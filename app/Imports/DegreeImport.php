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
 * Import cho thông tin bằng cử nhân, thạc sĩ, tiến sĩ
 * Cấu trúc file Excel theo template [Mau TT01]
 */
class DegreeImport implements ToCollection, WithStartRow
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

    // Column mapping constants
    private const STUDENT_CODE = 1;               // B - Mã học viên
    private const COL_DEGREE_TYPE = 2;            // C - Loại văn bằng
    private const COL_FULL_NAME = 3;              // D - Họ và tên
    private const COL_DATE_OF_BIRTH = 4;        // E - Ngày sinh
    private const COL_PLACE_OF_BIRTH = 5;       // F - Nơi sinh
    private const COL_HOMETOWN = 6;         // G - Quê quán
    private const COL_PLACE_OF_ORIGIN = 7;      // H - Nguyên quán
    private const COL_GENDER = 8;           // I - Giới tính
    private const COL_NATION = 9;       // J - Dân tộc
    private const COL_NATIONALITY = 10;     // K - Quốc tịch
    private const COL_COURSE = 11;         // L - Khóa học
    private const COL_CLASS_NAME = 12;       // M - Lớp học
    private const COL_ACADEMIC_YEAR = 13;   // N - Niên khóa
    private const COL_MAJOR_NAME = 14;     // O - Chuyên ngành
    private const COL_TRAINING_TYPE = 15;  // P - Hình thức đào tạo
    private const COL_COUNCIL_DECISION_NUMBER = 16;  // Q - Số QĐ thành lập hội đồng
    private const COL_COUNCIL_DECISION_DATE = 17; // R - Ngày QĐ thành lập hội đồng
    private const COL_DEFENSE_DATE = 18;     // S - Ngày bảo vệ
    private const COL_GRADUATION_DECISION_NUMBER = 19; // T - Số QĐ tốt nghiệp
    private const COL_GRADUATION_DECISION_DATE = 20; // U - Ngày QĐ tốt nghiệp
    private const COL_GRADUATION_YEAR = 21;   // V - Năm tốt nghiệp
    private const COL_RANKING = 22;         // W - Xếp loại
    private const COL_DIPLOMA_NUMBER = 23;   // X - Số hiệu văn bằng
    private const COL_NUMBER_IN_THE_BOOK = 24; // Y - Số trong sổ
    private const COL_GRANTING_DATE = 25;   // Z - Ngày cấp bằng
    private const COL_ADJUSTMENT_CONTENT = 26; // AA - Nội dung điều chỉnh
    private const COL_ADJUSTMENT_DECISION = 27; // AB - Số QĐ điều chỉnh
    private const COL_ADJUSTMENT_DATE = 28;  // AC - Ngày QĐ điều chỉnh
    private const COL_REISSUE_NUMBER = 29;   // AD - Số hiệu cấp lại
    private const COL_REISSUE_CONTENT = 30;  // AE - Nội dung cấp lại
    private const COL_REISSUE_DECISION = 31; // AF - Số QĐ cấp lại
    private const COL_REISSUE_DATE = 32;   // AG - Ngày QĐ cấp lại
    private const COL_NOTES = 33;          // AH - Ghi chú

    public function __construct(string $documentReference = null)
    {
        $this->documentReference = $documentReference ?? 'IMPORT_' . date('YmdHis');
    }

    /**
     * Start from row 3 (skip header at row 1 and description at row 2)
     * Excel template structure:
     * - Row 1: Column headers (STT, Loại văn bằng, Họ và tên, ...)
     * - Row 2: Sample data/description
     * - Row 3+: Actual data
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
            // Sử dụng type_id mặc định là Bachelor (BCN) - sẽ được update sau nếu cần
            $defaultTypeId = $this->getTypeIdForDegreeType('bachelor');

            $diplomaBlankImport = DiplomaBlankImport::create([
                'type_id' => $defaultTypeId,
                'document_reference' => $this->documentReference,
                'import_date' => now(),
                'issue_date' => now(), // Ngày phát hành mặc định
                'total_quantity' => $rows->count(),
                'from_number' => '000001', // Import từ Excel không có dãy số liên tục
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
                        'row' => $index + 2, // +2 vì start từ row 2
                        'error' => $e->getMessage(),
                        'data' => $row->toArray()
                    ];
                    Log::error('DegreeImport Error at row ' . ($index + 2), [
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
            Log::error('DegreeImport Fatal Error: ' . $e->getMessage());
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

        // Tìm hoặc tạo Major
        $major = $this->findOrCreateMajor($rowData['major_name']);

        // Kiểm tra degree đã tồn tại (early return để tránh xử lý thừa)
        $existingDegree = Degree::where('number_in_the_book', $rowData['number_in_the_book'])->first();
        if ($existingDegree) {
            Log::info('Degree already exists, skipping', [
                'number_in_the_book' => $rowData['number_in_the_book'],
                'existing_degree_id' => $existingDegree->degree_id
            ]);
            return;
        }

        $student = $existingDegree?->student;

        // Tìm hoặc tạo Student
        if (!$student) {
            $student = $this->findOrCreateStudent($rowData, $major);
        }

        // Tạo diploma blank và degree
        $diplomaBlank = $this->createDiplomaBlankIfNeeded($rowData['diploma_number'], $rowData['degree_type']);
        $degree = $this->createDegree($student, $diplomaBlank, $major, $rowData);

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
            'place_of_origin' => $this->cleanString($row[self::COL_PLACE_OF_ORIGIN] ?? ''),
            'gender' => $this->parseGender($row[self::COL_GENDER] ?? ''),
            'nation' => $this->cleanString($row[self::COL_NATION] ?? ''),
            'nationality' => $this->cleanString($row[self::COL_NATIONALITY] ?? ''),
            'course' => $this->cleanString($row[self::COL_COURSE] ?? ''),
            'class_name' => $this->cleanString($row[self::COL_CLASS_NAME] ?? ''),
            'academic_year' => $this->cleanString($row[self::COL_ACADEMIC_YEAR] ?? ''),
            'major_name' => $this->cleanString($row[self::COL_MAJOR_NAME] ?? ''),
            'training_type' => $this->normalizeTrainingType($this->cleanString($row[self::COL_TRAINING_TYPE] ?? '')),
            'council_decision_number' => $this->cleanString($row[self::COL_COUNCIL_DECISION_NUMBER] ?? ''),
            'council_decision_date' => $this->parseDate($row[self::COL_COUNCIL_DECISION_DATE] ?? ''),
            'defense_date' => $this->parseDate($row[self::COL_DEFENSE_DATE] ?? ''),
            'graduation_decision_number' => $this->cleanString($row[self::COL_GRADUATION_DECISION_NUMBER] ?? ''),
            'graduation_decision_date' => $this->parseDate($row[self::COL_GRADUATION_DECISION_DATE] ?? ''),
            'graduation_year' => $this->cleanString($row[self::COL_GRADUATION_YEAR] ?? ''),
            'ranking' => $this->cleanString($row[self::COL_RANKING] ?? ''),
            'diploma_number' => $this->cleanString($row[self::COL_DIPLOMA_NUMBER] ?? ''),
            'number_in_the_book' => $this->cleanString($row[self::COL_NUMBER_IN_THE_BOOK] ?? ''),
            'granting_date' => $this->parseDate($row[self::COL_GRANTING_DATE] ?? ''),
            'degree_type' => $this->parseDegreeType($row[self::COL_DEGREE_TYPE] ?? ''),
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
     * Find or create major with cache
     */
    protected function findOrCreateMajor(?string $majorName): ?Major
    {
        if (empty($majorName)) {
            return null;
        }

        // Check cache by name
        if (isset(self::$majorsByName[$majorName])) {
            return self::$majorsByName[$majorName];
        }

        // Find in database
        $major = Major::where('major_name', $majorName)->first();
        if ($major) {
            self::$majorsByName[$majorName] = $major;
            return $major;
        }

        // Generate code and check cache/database
        $majorCode = $this->generateMajorCode($majorName);
        if (isset(self::$majorsByCode[$majorCode])) {
            $major = self::$majorsByCode[$majorCode];
            self::$majorsByName[$majorName] = $major;
            return $major;
        }

        $major = Major::where('major_code', $majorCode)->first();
        if ($major) {
            self::$majorsByCode[$majorCode] = $major;
            self::$majorsByName[$majorName] = $major;
            return $major;
        }

        // Create new major
        $major = Major::create([
            'major_name' => $majorName,
            'major_code' => $majorCode
        ]);

        // Cache it
        self::$majorsByName[$majorName] = $major;
        self::$majorsByCode[$majorCode] = $major;

        Log::info('Created new major', [
            'major_name' => $majorName,
            'major_code' => $major->major_code,
            'major_id' => $major->major_id
        ]);

        return $major;
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
            'hometown'           => $rowData['hometown'],
            'place_of_origin'    => $rowData['place_of_origin'],
            'gender'             => $rowData['gender'],
            'nation'             => $rowData['nation'],
            'nationality'        => $rowData['nationality'],
            'course'             => $rowData['course'],
            'class_name'         => $rowData['class_name'],
            'academic_year'      => $rowData['academic_year'],
        ];

        // 2. Sử dụng updateOrCreate
        // Tham số 1: Điều kiện tìm kiếm (ở đây là student_code)
        // Tham số 2: Dữ liệu cần lưu (sẽ update nếu tìm thấy, hoặc create merge với tham số 1 nếu không thấy)

        $student = Student::updateOrCreate(
            ['student_code' => $rowData['student_code']], // Điều kiện duy nhất (unique key)
            $dataToSync                                    // Dữ liệu cần cập nhật/tạo mới
        );

        // Logic của Laravel:
        // - Nếu tìm thấy: Nó sẽ fill $dataToSync và save(). (Chỉ chạy query update nếu dữ liệu thực sự thay đổi - isDirty)
        // - Nếu không thấy: Nó sẽ tạo mới bản ghi với student_code + $dataToSync.

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

        $typeId = $this->getTypeIdForDegreeType($degreeType);
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
     * Create degree record
     */
    protected function createDegree(Student $student, ?DiplomaBlank $diplomaBlank, ?Major $major, array $rowData): Degree
    {

        return Degree::create([
            'student_id' => $student->student_id,
            'degree_type' => $rowData['degree_type'],
            'diploma_blank_id' => $diplomaBlank?->diploma_blank_id,
            'number_in_the_book' => $rowData['number_in_the_book'],
            'granting_date' => $rowData['granting_date'],
            'defense_date' => $rowData['defense_date'],
            'graduation_year' => $rowData['graduation_year'],
            'ranking' => $rowData['ranking'],
            'council_decision_number' => $rowData['council_decision_number'],
            'council_decision_date' => $rowData['council_decision_date'],
            'graduation_decision_number' => $rowData['graduation_decision_number'],
            'graduation_decision_date' => $rowData['graduation_decision_date'],
            'major_id' => $major?->major_id,
            'major_name' => $rowData['major_name'],
            'training_type' => $rowData['training_type'],
            'status' => DegreeStatus::ISSUED, // Mặc định là đã cấp khi import
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

        Log::info('Reissue data check', [
            'number_in_the_book' => $rowData['number_in_the_book'],
            'reissueNumber' => $rowData['reissue_number'],
            'diplomaBlankId' => $oldDiplomaBlank?->diploma_blank_id
        ]);

        $newDiplomaBlank = null;
        if (!empty($rowData['reissue_number'])) {
            $newDiplomaBlank = $this->createDiplomaBlankIfNeeded(
                $rowData['reissue_number'],
                $rowData['degree_type']
            );

            if ($newDiplomaBlank) {
                Log::info('Created new diploma blank for reissue', [
                    'serial_number' => $rowData['reissue_number'],
                    'diploma_blank_id' => $newDiplomaBlank->diploma_blank_id
                ]);
            }
        }

        $reissue = DegreeReissue::create([
            'degree_id' => $degree->degree_id,
            'old_diploma_blank_id' => $oldDiplomaBlank?->diploma_blank_id,
            'new_diploma_blank_id' => $newDiplomaBlank?->diploma_blank_id,
            'edit_content' => $rowData['reissue_content'] ?: 'Cấp lại văn bằng',
            'recall_decision' => $rowData['reissue_decision'],
            'decision_date' => $rowData['reissue_date'],
            'created_by' => Auth::id() ?? null,
        ]);

        Log::info('Created degree reissue', [
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
     * Parse degree type from Vietnamese text
     */
    protected function parseDegreeType(?string $type): string
    {
        if (empty($type)) {
            return 'bachelor';
        }

        $type = mb_strtolower($this->removeVietnameseTones($type));

        if (str_contains($type, 'tien si') || str_contains($type, 'doctor')) {
            return 'doctor';
        }
        if (str_contains($type, 'thac si') || str_contains($type, 'master')) {
            return 'master';
        }

        return 'bachelor';
    }

    /**
     * Normalize training type to match enum values
     * Enum: ['Chính quy', 'Liên thông', 'Từ xa', 'Vừa làm vừa học']
     */
    protected function normalizeTrainingType(?string $type): string
    {
        if (empty($type)) {
            return 'Chính quy';
        }

        $normalized = mb_strtolower($this->removeVietnameseTones(trim($type)));

        // Map variations to enum values
        $mapping = [
            'chinh quy' => 'Chính quy',
            'lien thong' => 'Liên thông',
            'lien ket' => 'Liên thông',  // Liên kết -> Liên thông
            'tu xa' => 'Từ xa',
            'vua lam vua hoc' => 'Vừa làm vừa học',
        ];

        foreach ($mapping as $key => $value) {
            if (str_contains($normalized, $key)) {
                return $value;
            }
        }

        return 'Chính quy'; // Default
    }

    /**
     * Generate major code from name
     */
    protected function generateMajorCode(string $majorName): string
    {
        $words = explode(' ', $this->removeVietnameseTones($majorName));
        $code = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }

        return substr($code, 0, 10);
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
     * Get type_id for degree type
     */
    protected function getTypeIdForDegreeType(string $degreeType): ?int
    {
        // Map degree_type to prefix
        $prefixMap = [
            'bachelor' => 'BCN',  // Bằng Cử nhân
            'master' => 'BTS',    // Bằng Thạc sĩ
            'doctor' => 'BTSI', // Bằng Tiến sĩ
        ];

        $prefix = $prefixMap[$degreeType] ?? 'BCN';

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
