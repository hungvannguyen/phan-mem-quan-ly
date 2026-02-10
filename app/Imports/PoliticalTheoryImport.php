<?php

namespace App\Imports;

use App\Enums\DegreeStatus;
use App\Enums\DiplomaBlankStatus;
use App\Enums\ImportStatus;
use App\Enums\StudentStatus;
use App\Models\ChangeLog;
use App\Models\Degree;
use App\Models\DegreeReissue;
use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankImport;
use App\Models\DiplomaBlankType;
use App\Models\Major;
use App\Models\Student;
use App\Traits\ImportHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Import cho thông tin cấp bằng Lý luận chính trị
 * Cấu trúc file Excel: Có thể khác với DegreeImport
 */
class PoliticalTheoryImport implements ToCollection, WithStartRow
{
    use ImportHelper;

    protected $importedCount = 0;

    protected $errorCount = 0;

    protected $errors = [];

    protected $documentReference;

    protected $diplomaBlankImportId;

    protected static $diplomaBlankTypes = null;

    protected static $majorsByName = [];

    protected static $majorsByCode = [];

    // Column mapping constants (26 columns A-Z)
    // A - Số TT (index 0)
    // Mã sinh viên sẽ được tạo tự động, không lấy từ Excel
    private const COL_DEGREE_TYPE = 1;              // A - Loại Văn bằng

    private const COL_FULL_NAME = 2;                // B - Họ và tên

    private const COL_DATE_OF_BIRTH = 3;            // C - Ngày Sinh

    private const COL_PLACE_OF_BIRTH = 4;           // D - Nơi Sinh

    private const COL_HOMETOWN = 5;                 // E - Quê quán

    private const COL_GENDER = 6;                   // F - Giới tính

    private const COL_NATION = 7;                   // G - Dân tộc

    private const COL_TRAINING_TYPE = 8;            // H - Loại hình đào tạo

    private const COL_COURSE = 9;                   // I - Khoá

    private const COL_RANKING = 10;                 // J - Xếp loại tốt nghiệp

    private const COL_DIPLOMA_NUMBER = 11;          // K - Số hiệu văn bằng

    private const COL_NUMBER_IN_THE_BOOK = 12;     // L - Số vào sổ gốc cấp văn bằng

    private const COL_ACADEMIC_YEAR = 13;           // M - Khoá học

    private const COL_GRADUATION_DECISION_NUMBER = 14; // N - Số QĐ (QĐ công nhận tốt nghiệp)

    private const COL_GRADUATION_DECISION_DATE = 15;   // O - Ngày Tháng (QĐ công nhận tốt nghiệp)

    private const COL_GRANTING_DATE = 16;           // P - Ngày cấp

    private const COL_STATUS_TEXT = 17;             // Q - Tình trạng

    private const COL_ADJUSTMENT_CONTENT = 18;      // R - Nội dung điều chỉnh

    private const COL_ADJUSTMENT_DECISION = 19;     // S - QĐ điều chỉnh thông tin

    private const COL_ADJUSTMENT_DATE = 20;         // T - Ngày QĐ (Điều chỉnh thông tin)

    private const COL_REISSUE_NUMBER = 21;          // U - Số hiệu văn bằng (Cấp lại)

    private const COL_REISSUE_CONTENT = 22;         // V - Nội dung chỉnh sửa (Cấp lại)

    private const COL_REISSUE_DECISION = 23;        // W - QĐ thu hồi, huỷ bỏ và cấp lại

    private const COL_REISSUE_DATE = 24;            // X - Ngày QĐ (Cấp lại)

    private const COL_NOTES = 25;                   // Y - Ghi chú

    public function __construct(?string $documentReference = null)
    {
        $this->documentReference = $documentReference ?? 'CERT_IMPORT_'.date('YmdHis');
    }

    /**
     * Start from row 5 (skip header at row 1 and description at row 2)
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

            // Tạo DiplomaBlankImport record cho lần import này
            $defaultTypeId = $this->getTypeIdForCertificateType('cao_cap');

            $diplomaBlankImport = DiplomaBlankImport::create([
                'type_id' => $defaultTypeId,
                'document_reference' => $this->documentReference,
                'import_date' => now(),
                'issue_date' => now(),
                'total_quantity' => $rows->count(),
                'from_number' => '000001',
                'to_number' => str_pad($rows->count(), 6, '0', STR_PAD_LEFT),
                'status' => ImportStatus::PENDING,
            ]);

            $this->diplomaBlankImportId = $diplomaBlankImport->id;

            $successCount = 0;
            foreach ($rows as $index => $row) {
                try {
                    $this->processRow($row, $index);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'row' => $index + 5, // +5 vì start từ row 5
                        'error' => $e->getMessage(),
                        'data' => $row->toArray(),
                    ];
                    Log::error('PoliticalTheoryImport Error at row '.($index + 5), [
                        'error' => $e->getMessage(),
                        'row' => $row->toArray(),
                    ]);
                }
            }

            // Update DiplomaBlankImport status
            $diplomaBlankImport->update([
                'processed_count' => $successCount,
                'status' => $this->errorCount > 0 ? ImportStatus::COMPLETED : ImportStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PoliticalTheoryImport Fatal Error: '.$e->getMessage());
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

        // Kiểm tra degree đã tồn tại
        $existingDegree = Degree::where('number_in_the_book', $rowData['number_in_the_book'])->first();
        if ($existingDegree) {
            Log::info('Certificate already exists, skipping', [
                'number_in_the_book' => $rowData['number_in_the_book'],
                'existing_degree_id' => $existingDegree->degree_id,
            ]);

            return;
        }

        $student = $existingDegree?->student;

        // Tìm hoặc tạo Student
        if (! $student) {
            $student = $this->findOrCreateStudent($rowData, null);
        }

        // Tạo diploma blank và degree
        $diplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['diploma_number'], $rowData['degree_type']);
        $degree = $this->createDegree($student, $diplomaBlank, $rowData);

        // Xử lý adjustment và reissue
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
            'hometown' => $this->cleanString($row[self::COL_HOMETOWN] ?? ''),
            'gender' => $this->parseGender($row[self::COL_GENDER] ?? ''),
            'nation' => $this->cleanString($row[self::COL_NATION] ?? ''),
            'training_type' => $this->normalizeTrainingType($this->cleanString($row[self::COL_TRAINING_TYPE] ?? '')),
            'course' => $this->cleanString($row[self::COL_COURSE] ?? ''),
            'ranking' => $this->cleanString($row[self::COL_RANKING] ?? ''),
            'diploma_number' => $this->cleanString($row[self::COL_DIPLOMA_NUMBER] ?? ''),
            'number_in_the_book' => $this->cleanString($row[self::COL_NUMBER_IN_THE_BOOK] ?? ''),
            'academic_year' => $this->cleanString($row[self::COL_ACADEMIC_YEAR] ?? ''),
            'graduation_decision_number' => $this->cleanString($row[self::COL_GRADUATION_DECISION_NUMBER] ?? ''),
            'graduation_decision_date' => $this->parseDate($row[self::COL_GRADUATION_DECISION_DATE] ?? ''),
            'granting_date' => $this->parseDate($row[self::COL_GRANTING_DATE] ?? ''),
            'degree_type' => $this->cleanString($row[self::COL_DEGREE_TYPE] ?? ''),
            'status_text' => $this->cleanString($row[self::COL_STATUS_TEXT] ?? ''),
            'status' => $this->parseStatusFromText($this->cleanString($row[self::COL_STATUS_TEXT] ?? '')),
            'notes' => $this->cleanString($row[self::COL_NOTES] ?? ''),
            'adjustment_content' => $this->cleanString($row[self::COL_ADJUSTMENT_CONTENT] ?? ''),
            'adjustment_decision' => $this->cleanString($row[self::COL_ADJUSTMENT_DECISION] ?? ''),
            'adjustment_date' => $this->parseDate($row[self::COL_ADJUSTMENT_DATE] ?? ''),
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
                'status' => StudentStatus::Graduate,
            ]);

            Log::info('PoliticalTheoryImport: Found existing student', ['id' => $student->student_id]);

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
            'status' => StudentStatus::Graduate,
        ]);

        Log::info('PoliticalTheoryImport: Created new student', [
            'id' => $student->student_id,
            'student_code' => $studentCode,
        ]);

        return $student;
    }

    /**
     * Generate unique student code
     */
    protected function generateUniqueStudentCode(): string
    {
        $prefix = 'LLCT';
        $year = date('Y');

        do {
            // Tạo mã theo format: LLCT + Năm + 6 số ngẫu nhiên
            $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $studentCode = $prefix.$year.$randomNumber;

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

        // Lấy hoặc tạo mới DiplomaBlankType trước
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
        // Parse graduation year from granting_date
        $graduationYear = $rowData['granting_date'] ? date('Y', strtotime($rowData['granting_date'])) : null;

        return Degree::create([
            'student_id' => $student->student_id,
            'degree_type' => 'certificate', // Luôn là certificate
            'diploma_blank_id' => $diplomaBlank?->diploma_blank_id,
            'number_in_the_book' => $rowData['number_in_the_book'],
            'granting_date' => $rowData['granting_date'],
            'graduation_year' => $graduationYear,
            'ranking' => $rowData['ranking'],
            'graduation_decision_number' => $rowData['graduation_decision_number'],
            'graduation_decision_date' => $rowData['graduation_decision_date'],
            'major_id' => null, // Certificate không có major
            'major_name' => $rowData['degree_type'], // Sử dụng loại văn bằng làm tên
            'training_type' => $rowData['training_type'], // Lưu hình thức đào tạo vào degree
            'status' => $rowData['status'], // Parse từ cột R
            'notes' => $rowData['notes'],
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
            'changed_by' => Auth::id() ?? null,
            'additional_data' => [
                'source' => 'import',
                'document_reference' => $this->documentReference,
            ],
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

        Log::info('Certificate Reissue data check', [
            'number_in_the_book' => $rowData['number_in_the_book'],
            'reissueNumber' => $rowData['reissue_number'],
            'diplomaBlankId' => $oldDiplomaBlank?->diploma_blank_id,
        ]);

        $newDiplomaBlank = null;
        if (! empty($rowData['reissue_number'])) {
            $newDiplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['reissue_number'], $rowData['degree_type']);

            if ($newDiplomaBlank) {
                Log::info('Created new diploma blank for certificate reissue', [
                    'serial_number' => $rowData['reissue_number'],
                    'diploma_blank_id' => $newDiplomaBlank->diploma_blank_id,
                ]);
            }
        }

        $reissue = DegreeReissue::create([
            'degree_id' => $degree->degree_id,
            'old_diploma_blank_id' => $oldDiplomaBlank?->diploma_blank_id,
            'new_diploma_blank_id' => $newDiplomaBlank?->diploma_blank_id,
            'edit_content' => $rowData['reissue_content'] ?: 'Cấp lại Bằng',
            'recall_decision' => $rowData['reissue_decision'],
            'decision_date' => $rowData['reissue_date'],
            'created_by' => Auth::id() ?? null,
        ]);

        Log::info('Created certificate reissue', [
            'reissue_id' => $reissue->reissue_id,
            'old_blank_id' => $reissue->old_diploma_blank_id,
            'new_blank_id' => $reissue->new_diploma_blank_id,
        ]);

        if ($newDiplomaBlank) {
            $degree->update(['diploma_blank_id' => $newDiplomaBlank->diploma_blank_id]);
            Log::info('Updated degree diploma_blank_id to new blank', [
                'degree_id' => $degree->degree_id,
                'new_diploma_blank_id' => $newDiplomaBlank->diploma_blank_id,
            ]);
        }
    }

    /**
     * Parse status from Vietnamese text to DegreeStatus enum
     */
    protected function parseStatusFromText(?string $statusText): DegreeStatus
    {
        if (empty($statusText)) {
            return DegreeStatus::NOT_ISSUED;
        }

        $normalized = mb_strtolower($this->removeVietnameseTones(trim($statusText)));

        // Map Vietnamese status text to enum values
        $statusMapping = [
            'chua cap' => DegreeStatus::NOT_ISSUED,
            'da cap' => DegreeStatus::ISSUED,
            'thu hoi' => DegreeStatus::RECALLED,
        ];

        foreach ($statusMapping as $key => $enumValue) {
            if (str_contains($normalized, $key)) {
                return $enumValue;
            }
        }

        // Log warning nếu không map được
        Log::warning('PoliticalTheoryImport: Unknown status text, defaulting to NOT_ISSUED', [
            'status_text' => $statusText,
            'normalized' => $normalized,
        ]);

        return DegreeStatus::NOT_ISSUED;
    }

    /**
     * Normalize training type to match enum values
     */
    protected function normalizeTrainingType(?string $type): string
    {
        if (empty($type)) {
            return 'Chính quy';
        }

        $normalized = mb_strtolower($this->removeVietnameseTones(trim($type)));

        $mapping = [
            'chinh quy' => 'Chính quy',
            'lien thong' => 'Liên thông',
            'lien ket' => 'Liên thông',
            'tu xa' => 'Từ xa',
            'vua lam vua hoc' => 'Vừa làm vừa học',
        ];

        foreach ($mapping as $key => $value) {
            if (str_contains($normalized, $key)) {
                return $value;
            }
        }

        return 'Chính quy';
    }

    /**
     * Load all caches for better performance
     */
    protected function loadCaches(): void
    {
        // Load diploma blank types
        if (self::$diplomaBlankTypes === null) {
            self::$diplomaBlankTypes = DiplomaBlankType::all()->keyBy('prefix');
        }

        // Load existing majors into cache
        if (empty(self::$majorsByName)) {
            $majors = Major::all();
            foreach ($majors as $major) {
                self::$majorsByName[$major->major_name] = $major;
                self::$majorsByCode[$major->major_code] = $major;
            }
        }
    }

    /**
     * Get type_id for certificate type
     */
    protected function getTypeIdForCertificateType(string $degreeType): ?int
    {
        // Sử dụng trực tiếp giá trị degreeType từ hàng Excel để tìm/ tạo DiplomaBlankType
        $key = trim((string) $degreeType);
        if ($key === '') {
            $key = 'cao_cap';
        }

        // Chuẩn hóa prefix để lưu/so sánh (ví dụ: 'cao_cap' -> 'CAO_CAP')
        $searchPrefix = mb_strtoupper(str_replace(' ', '_', $key));

        // Kiểm tra cache trước
        if (isset(self::$diplomaBlankTypes[$searchPrefix])) {
            return self::$diplomaBlankTypes[$searchPrefix]->type_id;
        }

        // Tìm theo prefix hoặc theo tên (case-insensitive). Nếu không có thì tạo mới.
        $type = DiplomaBlankType::whereRaw('upper(prefix) = ?', [$searchPrefix])
            ->orWhereRaw('upper(type_name) = ?', [mb_strtoupper($key)])
            ->first();

        if (! $type) {
            $type = DiplomaBlankType::create([
                'prefix' => $searchPrefix,
                'type_name' => $key,
                'description' => 'Tự động tạo từ tiến trình Import Lý luận chính trị',
            ]);
        }

        // Cập nhật cache
        self::$diplomaBlankTypes[$type->prefix] = $type;

        return $type->type_id;
    }

    /**
     * Get import statistics
     */
    public function getStatistics(): array
    {
        return [
            'imported' => $this->importedCount,
            'errors' => $this->errorCount,
            'error_details' => $this->errors,
        ];
    }
}
