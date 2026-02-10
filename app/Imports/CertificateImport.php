<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Degree;
use App\Models\Major;
use App\Models\DiplomaBlankImport;
use App\Models\DiplomaBlank;
use App\Models\ChangeLog;
use App\Models\DegreeReissue;
use App\Models\DiplomaBlankType;
use App\Enums\DegreeStatus;
use App\Enums\ImportStatus;
use App\Enums\DiplomaBlankStatus;
use App\Traits\ImportHelper;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Import cho thông tin chứng chỉ
 * Cấu trúc file Excel: 24 cột (A-X)
 */
class CertificateImport implements ToCollection, WithStartRow
{
    use ImportHelper;

    protected $importedCount = 0;
    protected $errorCount = 0;
    protected $errors = [];
    protected $documentReference;
    protected $diplomaBlankImportId;
    protected static $diplomaBlankTypes = null;

    // --- ĐỊNH NGHĨA CỘT THEO YÊU CẦU ---
    // Index bắt đầu từ 0 (Cột A)
    // Mã sinh viên sẽ được tạo tự động, không lấy từ Excel
    private const COL_FULL_NAME = 1;                // B - Họ và tên
    private const COL_DATE_OF_BIRTH = 2;            // C - Ngày sinh
    private const COL_PLACE_OF_BIRTH = 3;           // D - Nơi sinh
    private const COL_GENDER = 4;                   // E - Giới tính
    private const COL_NATION = 5;                   // F - Dân tộc
    private const COL_TRAINING_PROGRAM = 6;         // G - Chương trình bồi dưỡng (Map vào major_name hoặc degree_type)
    private const COL_RANKING = 7;                  // H - Xếp Loại
    private const COL_DIPLOMA_NUMBER = 8;           // I - Số hiệu chứng chỉ
    private const COL_NUMBER_IN_THE_BOOK = 9;       // J - Số vào sổ gốc cấp chứng chỉ
    private const COL_TRAINING_START = 10;          // K - Thời gian đào tạo từ ngày
    private const COL_TRAINING_END = 11;            // L - Thời gian đài tạo đến ngày
    private const COL_GRADUATION_DECISION_NUMBER = 12; // M - Số QĐ (QĐ công nhận tốt nghiệp)
    private const COL_GRADUATION_DECISION_DATE = 13;   // N - Ngày Tháng (QĐ công nhận tốt nghiệp)
    private const COL_GRANTING_DATE = 14;           // O - Ngày cấp
    private const COL_STATUS_TEXT = 15;             // P - Tình trạng
    private const COL_ADJUSTMENT_CONTENT = 16;      // Q - Nội dung điều chỉnh
    private const COL_ADJUSTMENT_DECISION = 17;     // R - QĐ điều chỉnh thông tin
    private const COL_ADJUSTMENT_DATE = 18;         // S - Ngày QĐ (Điều chỉnh)
    private const COL_REISSUE_NUMBER = 19;          // T - Số hiệu văn bằng (Cấp lại)
    private const COL_REISSUE_CONTENT = 20;         // U - Nội dung chỉnh sửa (Cấp lại)
    private const COL_REISSUE_DECISION = 21;        // V - QĐ thu hồi, huỷ bỏ và cấp lại
    private const COL_REISSUE_DATE = 22;            // W - Ngày QĐ (Cấp lại)
    private const COL_NOTES = 23;                   // X - Ghi chú

    public function __construct(string $documentReference = null)
    {
        $this->documentReference = $documentReference ?? 'CERT_IMPORT_' . date('YmdHis');
    }

    /**
     * Start from row 2 or 3 depending on header
     * Giả sử row 1 là header, data bắt đầu từ row 2
     */
    public function startRow(): int
    {
        return 5;
    }

    /**
     * Process collection of rows
     */
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            // Load caches
            $this->loadCaches();

            // Tạo DiplomaBlankImport record
            $defaultTypeId = $this->getTypeIdForCertificateType();

            $diplomaBlankImport = DiplomaBlankImport::create([
                'type_id' => $defaultTypeId,
                'document_reference' => $this->documentReference,
                'import_date' => now(),
                'issue_date' => now(),
                'total_quantity' => $rows->count(),
                'from_number' => '000001', // Logic tạm, có thể điều chỉnh
                'to_number' => str_pad($rows->count(), 6, '0', STR_PAD_LEFT),
                'status' => ImportStatus::PENDING,
            ]);

            $this->diplomaBlankImportId = $diplomaBlankImport->id;

            $successCount = 0;
            foreach ($rows as $index => $row) {
                try {
                    $this->processRow($row, $index);
                    $successCount++;
                    $this->importedCount++;
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'row' => $index + $this->startRow(),
                        'error' => $e->getMessage(),
                        'data' => $row->toArray()
                    ];
                    Log::error('CertificateImport Error at row ' . ($index + $this->startRow()), [
                        'error' => $e->getMessage(),
                        'row' => $row->toArray()
                    ]);
                }
            }

            // Update status
            $diplomaBlankImport->update([
                'processed_count' => $successCount,
                'status' => ImportStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CertificateImport Fatal Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process single row
     */
    protected function processRow(Collection $row, int $index)
    {
        $row = $row->values()->all();
        $rowData = $this->parseRowData($row);

        // Skip empty rows
        if (empty($rowData['full_name'])) {
            return;
        }

        // Kiểm tra degree đã tồn tại dựa trên số vào sổ
        $existingDegree = Degree::where('number_in_the_book', $rowData['number_in_the_book'])->first();
        if ($existingDegree) {
            Log::info('Certificate already exists, skipping', [
                'number_in_the_book' => $rowData['number_in_the_book']
            ]);
            return;
        }

        $student = $existingDegree?->student;

        // Tìm hoặc tạo Student
        if (!$student) {
            $student = $this->findOrCreateStudent($rowData);
        }

        // Tạo diploma blank (Phôi) và degree (Văn bằng/Chứng chỉ)
        $diplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['diploma_number'], $rowData['degree_type']);
        $degree = $this->createDegree($student, $diplomaBlank, $rowData);

        // Xử lý điều chỉnh và cấp lại
        $this->processAdjustment($degree, $rowData);
        $this->processReissue($degree, $diplomaBlank, $rowData);
    }

    /**
     * Parse row data from Excel
     */
    protected function parseRowData(array $row): array
    {
        return [
            'full_name' => $this->cleanString($row[self::COL_FULL_NAME] ?? ''),
            'date_of_birth' => $this->parseDate($row[self::COL_DATE_OF_BIRTH] ?? ''),
            'place_of_birth' => $this->cleanString($row[self::COL_PLACE_OF_BIRTH] ?? ''),
            'gender' => $this->parseGender($row[self::COL_GENDER] ?? ''),
            'nation' => $this->cleanString($row[self::COL_NATION] ?? ''),
            'training_program' => $this->cleanString($row[self::COL_TRAINING_PROGRAM] ?? ''), // Chương trình bồi dưỡng
            'degree_type' => $this->cleanString($row[self::COL_TRAINING_PROGRAM] ?? ''),
            'ranking' => $this->cleanString($row[self::COL_RANKING] ?? ''),
            'diploma_number' => $this->cleanString($row[self::COL_DIPLOMA_NUMBER] ?? ''),
            'number_in_the_book' => $this->cleanString($row[self::COL_NUMBER_IN_THE_BOOK] ?? ''),
            'training_start' => $this->parseDate($row[self::COL_TRAINING_START] ?? ''),
            'training_end' => $this->parseDate($row[self::COL_TRAINING_END] ?? ''),
            'graduation_decision_number' => $this->cleanString($row[self::COL_GRADUATION_DECISION_NUMBER] ?? ''),
            'graduation_decision_date' => $this->parseDate($row[self::COL_GRADUATION_DECISION_DATE] ?? ''),
            'granting_date' => $this->parseDate($row[self::COL_GRANTING_DATE] ?? ''),
            'status' => $this->parseStatusFromText($this->cleanString($row[self::COL_STATUS_TEXT] ?? '')),
            'notes' => $this->cleanString($row[self::COL_NOTES] ?? ''),
            // Adjustment
            'adjustment_content' => $this->cleanString($row[self::COL_ADJUSTMENT_CONTENT] ?? ''),
            'adjustment_decision' => $this->cleanString($row[self::COL_ADJUSTMENT_DECISION] ?? ''),
            'adjustment_date' => $this->parseDate($row[self::COL_ADJUSTMENT_DATE] ?? ''),
            // Reissue
            'reissue_number' => $this->cleanString($row[self::COL_REISSUE_NUMBER] ?? ''),
            'reissue_content' => $this->cleanString($row[self::COL_REISSUE_CONTENT] ?? ''),
            'reissue_decision' => $this->cleanString($row[self::COL_REISSUE_DECISION] ?? ''),
            'reissue_date' => $this->parseDate($row[self::COL_REISSUE_DATE] ?? ''),
        ];
    }

    /**
     * Find or create student
     */
    protected function findOrCreateStudent(array $rowData): Student
    {
        // Tìm sinh viên theo họ tên + ngày sinh
        $student = Student::where('full_name', $rowData['full_name'])
            ->where('date_of_birth', $rowData['date_of_birth'])
            ->first();

        if ($student) {
            // Cập nhật thông tin nếu cần
            $student->update([
                'place_of_birth' => $rowData['place_of_birth'],
                'gender' => $rowData['gender'],
                'nation' => $rowData['nation'],
            ]);

            Log::info('CertificateImport: Found existing student', ['id' => $student->student_id]);
            return $student;
        }

        // Tạo mã sinh viên tự động không trùng
        $studentCode = $this->generateUniqueStudentCode();

        $student = Student::create([
            'student_code' => $studentCode,
            'full_name' => $rowData['full_name'],
            'date_of_birth' => $rowData['date_of_birth'],
            'place_of_birth' => $rowData['place_of_birth'],
            'gender' => $rowData['gender'],
            'nation' => $rowData['nation'],
        ]);

        Log::info('CertificateImport: Created new student', [
            'id' => $student->student_id,
            'student_code' => $studentCode
        ]);

        return $student;
    }

    /**
     * Generate unique student code
     */
    protected function generateUniqueStudentCode(): string
    {
        $prefix = 'CERT';
        $year = date('Y');

        do {
            // Tạo mã theo format: CERT + Năm + 6 số ngẫu nhiên
            $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $studentCode = $prefix . $year . $randomNumber;

            // Kiểm tra xem mã đã tồn tại chưa
            $exists = Student::where('student_code', $studentCode)->exists();
        } while ($exists);

        return $studentCode;
    }

    /**
     * Create diploma blank if needed
     */
    protected function createDiplomaBlankIfNeeded(?string $diplomaNumber, string $degreeType): ?DiplomaBlank
    {
        if (empty($diplomaNumber)) {
            return null;
        }

        $typeId = $this->getTypeIdForCertificateType($degreeType);
        return DiplomaBlank::firstOrCreate(
            ['serial_number' => $diplomaNumber],
            [
                'import_id' => $this->diplomaBlankImportId,
                'type_id' => $typeId,
                'status' => DiplomaBlankStatus::ISSUED,
            ]
        );
    }

    /**
     * Create degree record for certificate
     */
    protected function createDegree(Student $student, ?DiplomaBlank $diplomaBlank, array $rowData): Degree
    {

        // Ensure log entry is written even if default channel isn't producing output
        try {
            Log::channel('single')->info('Creating degree', ['diploma_blank' => $diplomaBlank]);
        } catch (\Throwable $e) {
            // Fallback: append to import-jobs.log so scheduled runs also capture it
            $msg = now()->toDateTimeString() . " Creating degree - diploma_blank: " . json_encode($diplomaBlank) . PHP_EOL;
            @file_put_contents(storage_path('logs/import-jobs.log'), $msg, FILE_APPEND | LOCK_EX);
        }

        // Xác định ngày cấp và năm tốt nghiệp, dùng fallback nếu dữ liệu thiếu
        $grantingDate = $rowData['granting_date'] ?? $rowData['graduation_decision_date'] ?? $rowData['training_end'] ?? now();
        $graduationYear = $grantingDate ? date('Y', strtotime($grantingDate)) : (int)date('Y');

        // Xử lý ghi chú: Nối thêm thông tin thời gian đào tạo vào ghi chú vì bảng Degree thường không có cột start/end date
        $trainingNote = '';
        if ($rowData['training_start'] || $rowData['training_end']) {
            $start = $rowData['training_start'] ? date('d/m/Y', strtotime($rowData['training_start'])) : '...';
            $end = $rowData['training_end'] ? date('d/m/Y', strtotime($rowData['training_end'])) : '...';
            $trainingNote = "Thời gian đào tạo: $start - $end. ";
        }

        return Degree::create([
            'student_id' => $student->student_id,
            'degree_type' => 'certificate',
            'diploma_blank_id' => $diplomaBlank?->diploma_blank_id,
            'number_in_the_book' => $rowData['number_in_the_book'],
            'granting_date' => $grantingDate,
            'training_start_date' => $rowData['training_start'],
            'training_end_date' => $rowData['training_end'],
            'graduation_decision_number' => $rowData['graduation_decision_number'],
            'graduation_decision_date' => $rowData['graduation_decision_date'],
            'graduation_year' => $graduationYear,
            'ranking' => $rowData['ranking'],
            'graduation_decision_number' => $rowData['graduation_decision_number'],
            'graduation_decision_date' => $rowData['graduation_decision_date'],
            'major_id' => null,
            'major_name' => $rowData['training_program'], // Lưu tên chương trình bồi dưỡng vào major_name
            'status' => $rowData['status'],
            'notes' => trim($trainingNote . $rowData['notes']),
        ]);
    }

    /**
     * Process adjustment change log
     */
    protected function processAdjustment(Degree $degree, array $rowData): void
    {
        if (empty($rowData['adjustment_content']) && empty($rowData['adjustment_decision'])) {
            return;
        }

        ChangeLog::create([
            'entity_type' => class_basename(Degree::class),
            'entity_id' => $degree->degree_id,
            'change_description' => $rowData['adjustment_content'] ?: 'Điều chỉnh thông tin từ import',
            'decision_number' => $rowData['adjustment_decision'],
            'decision_date' => $rowData['adjustment_date'],
            'action_type' => 'update',
            'changed_by' => Auth::id() ?? null, // Không dùng fallback cố định để tránh FK lỗi
            'additional_data' => [
                'source' => 'import',
                'document_reference' => $this->documentReference,
            ]
        ]);
    }

    /**
     * Process degree reissue
     */
    protected function processReissue(Degree $degree, ?DiplomaBlank $oldDiplomaBlank, array $rowData): void
    {
        if (empty($rowData['reissue_number']) && empty($rowData['reissue_content'])) {
            return;
        }

        $newDiplomaBlank = null;
        if (!empty($rowData['reissue_number'])) {
            $newDiplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['reissue_number'], $rowData['degree_type']);
        }

        DegreeReissue::create([
            'degree_id' => $degree->degree_id,
            'old_diploma_blank_id' => $oldDiplomaBlank?->diploma_blank_id,
            'new_diploma_blank_id' => $newDiplomaBlank?->diploma_blank_id,
            'edit_content' => $rowData['reissue_content'] ?: 'Cấp lại văn bằng',
            'recall_decision' => $rowData['reissue_decision'],
            'decision_date' => $rowData['reissue_date'],
            'created_by' => Auth::id() ?? null,
        ]);

        if ($newDiplomaBlank) {
            $degree->update(['diploma_blank_id' => $newDiplomaBlank->diploma_blank_id]);
        }
    }

    /**
     * Parse status from Vietnamese text
     */
    protected function parseStatusFromText(?string $statusText): DegreeStatus
    {
        if (empty($statusText)) {
            return DegreeStatus::NOT_ISSUED;
        }

        $normalized = mb_strtolower($this->removeVietnameseTones(trim($statusText)));

        if (str_contains($normalized, 'da cap')) return DegreeStatus::ISSUED;
        if (str_contains($normalized, 'thu hoi')) return DegreeStatus::RECALLED;
        if (str_contains($normalized, 'huy')) return DegreeStatus::CANCELLED;

        return DegreeStatus::NOT_ISSUED;
    }

    /**
     * Load caches for performance
     */
    protected function loadCaches(): void
    {
        if (self::$diplomaBlankTypes === null) {
            self::$diplomaBlankTypes = DiplomaBlankType::all()->keyBy('prefix');
        }
    }

    /**
     * Get type_id for Certificate
     */
    protected function getTypeIdForCertificateType(?string $degreeType = null): ?int
    {
        // Use raw degreeType string from Excel to find/create DiplomaBlankType
        $key = trim((string) $degreeType);
        if ($key === '') {
            $key = 'BD-KHAC';
        }

        // Normalize a search-friendly prefix (replace spaces/slashes/hyphens)
        $searchPrefix = mb_strtoupper(str_replace([' ', '/', '-'], '_', $key));

        // Check cache first
        if (isset(self::$diplomaBlankTypes[$searchPrefix])) {
            return self::$diplomaBlankTypes[$searchPrefix]->type_id;
        }

        // Try find by prefix or by type_name (case-insensitive)
        $type = DiplomaBlankType::whereRaw('upper(prefix) = ?', [$searchPrefix])
            ->orWhereRaw('upper(type_name) = ?', [mb_strtoupper($key)])
            ->first();

        if (!$type) {
            $type = DiplomaBlankType::create([
                'prefix' => $searchPrefix,
                'type_name' => $key,
                'description' => 'Tự động tạo từ tiến trình Import',
            ]);
        }

        // Update cache
        self::$diplomaBlankTypes[$type->prefix] = $type;

        return $type->type_id;
    }

    public function getStatistics(): array
    {
        return [
            'imported' => $this->importedCount,
            'errors' => $this->errorCount,
            'error_details' => $this->errors,
        ];
    }
}
