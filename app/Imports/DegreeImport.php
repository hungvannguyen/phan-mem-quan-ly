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
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Import cho thông tin bằng cử nhân, thạc sĩ, tiến sĩ
 * Cấu trúc file Excel theo template [Mau TT01]
 */
class DegreeImport implements ToCollection, WithChunkReading, WithStartRow
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
    // Mã sinh viên sẽ được tạo tự động, không lấy từ Excel
    private const COL_DEGREE_TYPE = 1;            // B - Loại văn bằng

    private const COL_FULL_NAME = 2;              // C - Họ và tên

    private const COL_DATE_OF_BIRTH = 3;        // D - Ngày sinh

    private const COL_PLACE_OF_BIRTH = 4;       // E - Nơi sinh

    private const COL_HOMETOWN = 5;         // F - Quê quán

    private const COL_PLACE_OF_ORIGIN = 6;      // G - Nguyên quán

    private const COL_GENDER = 7;           // H - Giới tính

    private const COL_NATION = 8;       // I - Dân tộc

    private const COL_NATIONALITY = 9;     // J - Quốc tịch

    private const COL_COURSE = 10;         // K - Khóa học

    private const COL_CLASS_NAME = 11;       // L - Lớp học

    private const COL_ACADEMIC_YEAR = 12;   // M - Niên khóa

    private const COL_MAJOR_NAME = 13;     // N - Chuyên ngành

    private const COL_TRAINING_TYPE = 14;  // O - Hình thức đào tạo

    private const COL_COUNCIL_DECISION_NUMBER = 15;  // P - Số QĐ thành lập hội đồng

    private const COL_COUNCIL_DECISION_DATE = 16; // Q - Ngày QĐ thành lập hội đồng

    private const COL_DEFENSE_DATE = 17;     // R - Ngày bảo vệ

    private const COL_GRADUATION_DECISION_NUMBER = 18; // S - Số QĐ tốt nghiệp

    private const COL_GRADUATION_DECISION_DATE = 19; // T - Ngày QĐ tốt nghiệp

    private const COL_GRADUATION_YEAR = 20;   // U - Năm tốt nghiệp

    private const COL_RANKING = 21;         // V - Xếp loại

    private const COL_DIPLOMA_NUMBER = 22;   // W - Số hiệu văn bằng

    private const COL_NUMBER_IN_THE_BOOK = 23; // X - Số trong sổ

    private const COL_GRANTING_DATE = 24;   // Y - Ngày cấp bằng

    private const COL_ADJUSTMENT_CONTENT = 25; // Z - Nội dung điều chỉnh

    private const COL_ADJUSTMENT_DECISION = 26; // AA - Số QĐ điều chỉnh

    private const COL_ADJUSTMENT_DATE = 27;  // AB - Ngày QĐ điều chỉnh

    private const COL_REISSUE_NUMBER = 28;   // AC - Số hiệu cấp lại

    private const COL_REISSUE_CONTENT = 29;  // AD - Nội dung cấp lại

    private const COL_REISSUE_DECISION = 30; // AE - Số QĐ cấp lại

    private const COL_REISSUE_DATE = 31;   // AF - Ngày QĐ cấp lại

    private const COL_NOTES = 32;          // AG - Ghi chú

    public function __construct(?string $documentReference = null)
    {
        $this->documentReference = $documentReference ?? 'IMPORT_'.date('YmdHis');
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
     * Chunk size for processing large files
     */
    public function chunkSize(): int
    {
        return 500; // Process 500 rows at a time
    }

    /**
     * Process collection of rows
     */
    public function collection(Collection $rows)
    {
        // Load caches
        $this->loadCaches();

        // Tạo hoặc lấy DiplomaBlankImport record cho lần import này
        if (! $this->diplomaBlankImportId) {
            $defaultTypeId = $this->getTypeIdForDegreeType('bachelor');

            $diplomaBlankImport = DiplomaBlankImport::firstOrCreate(
                ['document_reference' => $this->documentReference],
                [
                    'type_id' => $defaultTypeId,
                    'import_date' => now(),
                    'issue_date' => now(),
                    'total_quantity' => 0,
                    'from_number' => '000001',
                    'to_number' => '000000',
                    'status' => ImportStatus::PENDING,
                    'processed_count' => 0,
                ]
            );

            $this->diplomaBlankImportId = $diplomaBlankImport->id;
        }

        // Process rows in chunks
        $successCount = 0;
        foreach ($rows as $index => $row) {
            try {
                $this->processRow($row, $index);
                $successCount++;
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $index + 3, // +3 vì start từ row 3
                    'error' => $e->getMessage(),
                    'data' => $row->toArray(),
                ];
                Log::error('DegreeImport Error at row '.($index + 3), [
                    'error' => $e->getMessage(),
                    'row' => $row->toArray(),
                ]);
            }
        }

        // Update DiplomaBlankImport progress after each chunk
        $diplomaBlankImport = DiplomaBlankImport::find($this->diplomaBlankImportId);
        if ($diplomaBlankImport) {
            $diplomaBlankImport->increment('processed_count', $successCount);
            $diplomaBlankImport->increment('total_quantity', $rows->count());
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
                'existing_degree_id' => $existingDegree->degree_id,
            ]);

            return;
        }

        // Tìm hoặc tạo Student
        $student = $this->findOrCreateStudent($rowData, $major);

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
            'degree_type' => $this->cleanString($row[self::COL_DEGREE_TYPE] ?? ''),
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
            'major_code' => $majorCode,
        ]);

        // Cache it
        self::$majorsByName[$majorName] = $major;
        self::$majorsByCode[$majorCode] = $major;

        Log::info('Created new major', [
            'major_name' => $majorName,
            'major_code' => $major->major_code,
            'major_id' => $major->major_id,
        ]);

        return $major;
    }

    /**
     * Find or create student
     */
    protected function findOrCreateStudent(array $rowData, ?Major $major): Student
    {
        // Tìm sinh viên theo họ tên + ngày sinh
        $student = Student::where('full_name', $rowData['full_name'])
            ->where('date_of_birth', $rowData['date_of_birth'])
            ->first();

        if ($student) {
            // Cập nhật thông tin nếu cần
            $student->update([
                'major_id' => $major?->major_id,
                'place_of_birth' => $rowData['place_of_birth'],
                'hometown' => $rowData['hometown'],
                'place_of_origin' => $rowData['place_of_origin'],
                'gender' => $rowData['gender'],
                'nation' => $rowData['nation'],
                'nationality' => $rowData['nationality'],
                'course' => $rowData['course'],
                'class_name' => $rowData['class_name'],
                'academic_year' => $rowData['academic_year'],
                'status' => StudentStatus::Graduate,
            ]);

            Log::info('DegreeImport: Found existing student', ['id' => $student->student_id]);

            return $student;
        }

        // Tạo mã sinh viên tự động không trùng
        $studentCode = $this->generateUniqueStudentCode();

        $student = Student::create([
            'student_code' => $studentCode,
            'major_id' => $major?->major_id,
            'full_name' => $rowData['full_name'],
            'date_of_birth' => $rowData['date_of_birth'],
            'place_of_birth' => $rowData['place_of_birth'],
            'hometown' => $rowData['hometown'],
            'place_of_origin' => $rowData['place_of_origin'],
            'gender' => $rowData['gender'],
            'nation' => $rowData['nation'],
            'nationality' => $rowData['nationality'],
            'course' => $rowData['course'],
            'class_name' => $rowData['class_name'],
            'academic_year' => $rowData['academic_year'],
            'status' => StudentStatus::Graduate,
        ]);

        Log::info('DegreeImport: Created new student', [
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
        $prefix = 'DEG';
        $year = date('Y');

        do {
            // Tạo mã theo format: DEG + Năm + 6 số ngẫu nhiên
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
            'degree_type' => $this->mapDegreeTypeForDb($rowData['degree_type']),
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
     * Map raw degree type (from Excel) to DB-safe values (enum keys)
     */
    protected function mapDegreeTypeForDb(?string $type): string
    {
        if (empty($type)) {
            return 'bachelor';
        }

        $normalized = mb_strtolower($this->removeVietnameseTones(trim($type)));

        if (str_contains($normalized, 'tien si') || str_contains($normalized, 'doctor')) {
            return 'doctor';
        }

        if (str_contains($normalized, 'thac si') || str_contains($normalized, 'master')) {
            return 'master';
        }

        // Vietnamese for 'cử nhân' -> 'cu nhan'
        if (str_contains($normalized, 'cu nhan') || str_contains($normalized, 'cu\u00a0nhan') || str_contains($normalized, 'bachelor')) {
            return 'bachelor';
        }

        // Default fallback
        return 'bachelor';
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

        Log::info('Reissue data check', [
            'number_in_the_book' => $rowData['number_in_the_book'],
            'reissueNumber' => $rowData['reissue_number'],
            'diplomaBlankId' => $oldDiplomaBlank?->diploma_blank_id,
        ]);

        $newDiplomaBlank = null;
        if (! empty($rowData['reissue_number'])) {
            $newDiplomaBlank = $this->createDiplomaBlankIfNeeded(
                $rowData['reissue_number'],
                $rowData['degree_type']
            );

            if ($newDiplomaBlank) {
                Log::info('Created new diploma blank for reissue', [
                    'serial_number' => $rowData['reissue_number'],
                    'diploma_blank_id' => $newDiplomaBlank->diploma_blank_id,
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
            if (! empty($word)) {
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
        // Sử dụng trực tiếp giá trị degreeType từ hàng Excel để tìm/ tạo DiplomaBlankType
        $key = trim((string) $degreeType);
        if ($key === '') {
            $key = 'bachelor';
        }

        // Chuẩn hóa prefix để lưu/so sánh (ví dụ: 'bachelor' -> 'BACHELOR')
        $searchPrefix = mb_strtoupper(str_replace(' ', '_', $key));

        // Kiểm tra cache trước
        if (isset(self::$diplomaBlankTypes[$searchPrefix])) {
            return self::$diplomaBlankTypes[$searchPrefix]->type_id;
        }

        // Tìm theo prefix hoặc theo tên (case-insensitive)
        $type = DiplomaBlankType::whereRaw('upper(prefix) = ?', [$searchPrefix])
            ->orWhereRaw('upper(type_name) = ?', [mb_strtoupper($key)])
            ->first();

        // If not found, create new with dedicated transaction
        if (! $type) {
            // Use DB transaction to ensure type is created immediately
            $type = DB::transaction(function () use ($searchPrefix, $key) {
                return DiplomaBlankType::create([
                    'prefix' => $searchPrefix,
                    'type_name' => $key,
                    'description' => 'Tự động tạo từ tiến trình Import',
                ]);
            });

            // Reload to ensure we have type_id
            $type = DiplomaBlankType::find($type->type_id);
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

    /**
     * Get diploma blank import ID
     */
    public function getDiplomaBlankImportId(): ?int
    {
        return $this->diplomaBlankImportId;
    }
}
