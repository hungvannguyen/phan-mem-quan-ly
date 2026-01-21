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
    private const STUDENT_CODE = 1;               // A - Mã học viên
    private const COL_DEGREE_TYPE = 2;              // B - Loại Văn bằng
    private const COL_FULL_NAME = 3;                // C - Họ và tên
    private const COL_DATE_OF_BIRTH = 4;            // D - Ngày Sinh
    private const COL_PLACE_OF_BIRTH = 5;           // E - Nơi Sinh
    private const COL_HOMETOWN = 6;                 // F - Quê quán
    private const COL_GENDER = 7;                   // G - Giới tính
    private const COL_NATION = 8;                   // H - Dân tộc
    private const COL_TRAINING_TYPE = 9;            // I - Loại hình đào tạo
    private const COL_COURSE = 10;                   // J - Khoá
    private const COL_RANKING = 11;                 // K - Xếp loại tốt nghiệp
    private const COL_DIPLOMA_NUMBER = 12;          // L - Số hiệu văn bằng
    private const COL_NUMBER_IN_THE_BOOK = 13;     // M - Số vào sổ gốc cấp văn bằng
    private const COL_ACADEMIC_YEAR = 14;           // N - Khoá học
    private const COL_GRADUATION_DECISION_NUMBER = 15; // O - Số QĐ (QĐ công nhận tốt nghiệp)
    private const COL_GRADUATION_DECISION_DATE = 16;   // P - Ngày Tháng (QĐ công nhận tốt nghiệp)
    private const COL_GRANTING_DATE = 17;           // Q - Ngày cấp
    private const COL_STATUS_TEXT = 18;             // R - Tình trạng
    private const COL_ADJUSTMENT_CONTENT = 19;      // S - Nội dung điều chỉnh
    private const COL_ADJUSTMENT_DECISION = 20;     // T - QĐ điều chỉnh thông tin
    private const COL_ADJUSTMENT_DATE = 21;         // U - Ngày QĐ (Điều chỉnh thông tin)
    private const COL_REISSUE_NUMBER = 22;          // V - Số hiệu văn bằng (Cấp lại)
    private const COL_REISSUE_CONTENT = 23;         // W - Nội dung chỉnh sửa (Cấp lại)
    private const COL_REISSUE_DECISION = 24;        // X - QĐ thu hồi, huỷ bỏ và cấp lại
    private const COL_REISSUE_DATE = 25;            // Y - Ngày QĐ (Cấp lại)
    private const COL_NOTES = 26;                   // Z - Ghi chú

    public function __construct(string $documentReference = null)
    {
        $this->documentReference = $documentReference ?? 'CERT_IMPORT_' . date('YmdHis');
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
                        'data' => $row->toArray()
                    ];
                    Log::error('PoliticalTheoryImport Error at row ' . ($index + 5), [
                        'error' => $e->getMessage(),
                        'row' => $row->toArray()
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
            Log::error('PoliticalTheoryImport Fatal Error: ' . $e->getMessage());
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
                'existing_degree_id' => $existingDegree->degree_id
            ]);
            return;
        }

        $student = $existingDegree?->student;

        // Tìm hoặc tạo Student
        if (!$student) {
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
            'student_code' => $this->cleanString($row[self::STUDENT_CODE] ?? ''),
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
            'degree_type' => $this->parseCertificateType($row[self::COL_DEGREE_TYPE] ?? ''),
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
        // 1. Chuẩn bị dữ liệu để map vào database
        // Đây là những dữ liệu sẽ được dùng để tạo mới HOẶC cập nhật
        $dataToSync = [
            'full_name'          => $rowData['full_name'],
            'date_of_birth'      => $rowData['date_of_birth'],
            'place_of_birth'     => $rowData['place_of_birth'],
            'gender'             => $rowData['gender'],
            'nation'             => $rowData['nation'],
        ];

        // 2. Sử dụng updateOrCreate
        // Tham số 1: Điều kiện tìm kiếm (ở đây là student_code)
        // Tham số 2: Dữ liệu cần lưu (sẽ update nếu tìm thấy, hoặc create merge với tham số 1 nếu không thấy)

        Log::info('PoliticalTheoryImport: Processing student', [
            'student_code' => $rowData['student_code']
        ]);

        $student = Student::updateOrCreate(
            ['student_code' => $rowData['student_code']], // Điều kiện duy nhất (unique key)
            $dataToSync                                    // Dữ liệu cần cập nhật/tạo mới
        );

        // Logic của Laravel:
        // - Nếu tìm thấy: Nó sẽ fill $dataToSync và save(). (Chỉ chạy query update nếu dữ liệu thực sự thay đổi - isDirty)
        // - Nếu không thấy: Nó sẽ tạo mới bản ghi với student_code + $dataToSync.

        // Log kết quả để kiểm tra (có thể bỏ qua nếu muốn code gọn hơn)
        if ($student->wasRecentlyCreated) {
            Log::info('PoliticalTheoryImport: Created new student', ['id' => $student->student_id]);
        } elseif ($student->wasChanged()) {
            Log::info('PoliticalTheoryImport: Updated existing student', ['id' => $student->student_id]);
        } else {
            Log::info('PoliticalTheoryImport: Student existed and no changes detected', ['id' => $student->student_id]);
        }

        return $student;
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

        Log::info('Certificate Reissue data check', [
            'number_in_the_book' => $rowData['number_in_the_book'],
            'reissueNumber' => $rowData['reissue_number'],
            'diplomaBlankId' => $oldDiplomaBlank?->diploma_blank_id
        ]);

        $newDiplomaBlank = null;
        if (!empty($rowData['reissue_number'])) {
            $newDiplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['reissue_number'],$rowData['degree_type']);

            if ($newDiplomaBlank) {
                Log::info('Created new diploma blank for certificate reissue', [
                    'serial_number' => $rowData['reissue_number'],
                    'diploma_blank_id' => $newDiplomaBlank->diploma_blank_id
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
            'new_blank_id' => $reissue->new_diploma_blank_id
        ]);

        if ($newDiplomaBlank) {
            $degree->update(['diploma_blank_id' => $newDiplomaBlank->diploma_blank_id]);
            Log::info('Updated degree diploma_blank_id to new blank', [
                'degree_id' => $degree->degree_id,
                'new_diploma_blank_id' => $newDiplomaBlank->diploma_blank_id
            ]);
        }
    }

    /**
     * Parse certificate type from Vietnamese text
     */
    protected function parseCertificateType(?string $type): string
    {
        if (empty($type)) {
            return 'cao_cap'; // Mặc định hoặc giá trị an toàn
        }

        $type = mb_strtolower($this->removeVietnameseTones($type));

        if (str_contains($type, 'cao cap')) {
            return 'cao_cap';
        }
        if (str_contains($type, 'trung cap')) {
            return 'trung_cap';
        }

        return 'cao_cap';
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
            'normalized' => $normalized
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
        // Map degree_type to prefix
        $prefixMap = [
           'cao_cap'   => 'CC-LLCT',
            'trung_cap' => 'TC-LLCT',
        ];

        // Default to the first mapping value (CC-LLCT) when no specific degree type is provided.
        $prefix = reset($prefixMap) ?? 'CC-LLCT';

        // Get from cache
        if (isset(self::$diplomaBlankTypes[$prefix])) {
            return self::$diplomaBlankTypes[$prefix]->type_id;
        }

        // Fallback: query database
        $type = DiplomaBlankType::where('prefix', $prefix)->first();
        return $type?->type_id;
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
