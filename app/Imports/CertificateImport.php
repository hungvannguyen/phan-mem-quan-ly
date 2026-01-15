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
 * Cấu trúc file Excel theo template Certificate (26 cột A-Z)
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
    protected static $majorsByName = [];
    protected static $majorsByCode = [];

    // Column mapping constants (26 columns A-Z)
    // A - Số TT (index 0)
    private const COL_DEGREE_TYPE = 1;              // B - Loại Văn bằng
    private const COL_FULL_NAME = 2;                // C - Họ và tên
    private const COL_DATE_OF_BIRTH = 3;            // D - Ngày Sinh
    private const COL_PLACE_OF_BIRTH = 4;           // E - Nơi Sinh
    private const COL_HOMETOWN = 5;                 // F - Quê quán
    private const COL_GENDER = 6;                   // G - Giới tính
    private const COL_NATION = 7;                   // H - Dân tộc
    private const COL_TRAINING_TYPE = 8;            // I - Loại hình đào tạo
    private const COL_COURSE = 9;                   // J - Khoá
    private const COL_RANKING = 10;                 // K - Xếp loại tốt nghiệp
    private const COL_DIPLOMA_NUMBER = 11;          // L - Số hiệu văn bằng
    private const COL_REGISTRATION_NUMBER = 12;     // M - Số vào sổ gốc cấp văn bằng
    private const COL_ACADEMIC_YEAR = 13;           // N - Khoá học
    private const COL_GRADUATION_DECISION_NUMBER = 14; // O - Số QĐ (QĐ công nhận tốt nghiệp)
    private const COL_GRADUATION_DECISION_DATE = 15;   // P - Ngày Tháng (QĐ công nhận tốt nghiệp)
    private const COL_GRANTING_DATE = 16;           // Q - Ngày cấp
    private const COL_STATUS_TEXT = 17;             // R - Tình trạng
    private const COL_ADJUSTMENT_CONTENT = 18;      // S - Nội dung điều chỉnh
    private const COL_ADJUSTMENT_DECISION = 19;     // T - QĐ điều chỉnh thông tin
    private const COL_ADJUSTMENT_DATE = 20;         // U - Ngày QĐ (Điều chỉnh thông tin)
    private const COL_REISSUE_NUMBER = 21;          // V - Số hiệu văn bằng (Cấp lại)
    private const COL_REISSUE_CONTENT = 22;         // W - Nội dung chỉnh sửa (Cấp lại)
    private const COL_REISSUE_DECISION = 23;        // X - QĐ thu hồi, huỷ bỏ và cấp lại
    private const COL_REISSUE_DATE = 24;            // Y - Ngày QĐ (Cấp lại)
    private const COL_NOTES = 25;                   // Z - Ghi chú

    public function __construct(string $documentReference = null)
    {
        $this->documentReference = $documentReference ?? 'CERT_IMPORT_' . date('YmdHis');
    }

    /**
     * Start from row 3 (skip header at row 1 and description at row 2)
     */
    public function startRow(): int
    {
        return 3;
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
            $defaultTypeId = $this->getTypeIdForCertificateType();

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
                        'row' => $index + 3, // +3 vì start từ row 3
                        'error' => $e->getMessage(),
                        'data' => $row->toArray()
                    ];
                    Log::error('CertificateImport Error at row ' . ($index + 3), [
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

        // Kiểm tra degree đã tồn tại
        $existingDegree = Degree::where('registration_number', $rowData['registration_number'])->first();
        if ($existingDegree) {
            Log::info('Certificate already exists, skipping', [
                'registration_number' => $rowData['registration_number'],
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
        $diplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['diploma_number']);
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
            'registration_number' => $this->cleanString($row[self::COL_REGISTRATION_NUMBER] ?? ''),
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
    protected function findOrCreateStudent(array $rowData, ?Major $major): Student
    {
        $student = Student::where('full_name', $rowData['full_name'])
            ->where('date_of_birth', $rowData['date_of_birth'])
            ->where('place_of_birth', $rowData['place_of_birth'])
            ->first();

        if ($student) {
            // Không update gì, giữ nguyên thông tin student đã có
            return $student;
        }

        $cleanRegNumber = preg_replace('/[^A-Z0-9]/', '', $rowData['registration_number']);
        $studentCode = 'CERT_' . $cleanRegNumber;

        $dataToCreate = [
            'student_code' => $studentCode,
            'full_name' => $rowData['full_name'],
            'date_of_birth' => $rowData['date_of_birth'],
            'place_of_birth' => $rowData['place_of_birth'],
            'hometown' => $rowData['hometown'],
            'gender' => $rowData['gender'],
            'nation' => $rowData['nation'],
            'nationality' => 'Việt Nam',
            'course' => $rowData['course'],
            'academic_year' => $rowData['academic_year'],
            'major_id' => null,
            'number_in_the_book' => $rowData['registration_number'],
            'class_name' => null, // EXPLICITLY NULL - Certificate không có class_name
            'status' => 1,
        ];

        Log::info('CertificateImport: Data being passed to Student::create', $dataToCreate);

        $student = Student::create($dataToCreate);

        // Fresh từ database để chắc chắn
        $student->refresh();

        Log::info('CertificateImport: Student sau khi create và refresh', [
            'student_id' => $student->student_id,
            'student_code' => $student->student_code,
            'class_name' => $student->class_name,
            'course' => $student->course,
            'class_name_is_null' => is_null($student->class_name),
        ]);

        return $student;
    }

    /**
     * Create diploma blank if needed
     */
    protected function createDiplomaBlankIfNeeded(?string $diplomaNumber): ?DiplomaBlank
    {
        if (empty($diplomaNumber)) {
            return null;
        }

        $typeId = $this->getTypeIdForCertificateType();
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
            'registration_number' => $rowData['registration_number'],
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
            'changed_by' => Auth::id(),
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
            'registration_number' => $rowData['registration_number'],
            'reissueNumber' => $rowData['reissue_number'],
            'diplomaBlankId' => $oldDiplomaBlank?->diploma_blank_id
        ]);

        $newDiplomaBlank = null;
        if (!empty($rowData['reissue_number'])) {
            $newDiplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['reissue_number']);

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
            'edit_content' => $rowData['reissue_content'] ?: 'Cấp lại chứng chỉ',
            'recall_decision' => $rowData['reissue_decision'],
            'decision_date' => $rowData['reissue_date'],
            'created_by' => Auth::id(),
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
            return 'Chứng chỉ';
        }

        return $this->cleanString($type);
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
        Log::warning('CertificateImport: Unknown status text, defaulting to NOT_ISSUED', [
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
    protected function getTypeIdForCertificateType(): ?int
    {
        // Tìm loại phôi cho chứng chỉ (có thể là NV-6T hoặc loại khác)
        // Default sẽ lấy loại đầu tiên chứa 'chứng chỉ' hoặc prefix bắt đầu bằng 'CC'

        if (self::$diplomaBlankTypes !== null) {
            foreach (self::$diplomaBlankTypes as $type) {
                if (str_contains(mb_strtolower($type->type_name), 'chứng chỉ') ||
                    str_starts_with($type->prefix, 'CC')) {
                    return $type->type_id;
                }
            }
        }

        // Fallback: query database
        $type = DiplomaBlankType::where('type_name', 'LIKE', '%Chứng chỉ%')->first();
        if ($type) {
            return $type->type_id;
        }

        // Nếu không tìm thấy, tạo mới
        $newType = DiplomaBlankType::firstOrCreate(
            ['prefix' => 'CC'],
            [
                'type_name' => 'Chứng chỉ',
                'description' => 'Chứng chỉ các loại',
            ]
        );

        return $newType->type_id;
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
