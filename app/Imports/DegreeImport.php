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
            // Load diploma blank types vào cache
            $this->loadDiplomaBlankTypes();

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
        // Convert Collection to array for index access
        $row = $row->values()->all();

        // Map columns theo cấu trúc file Excel
        // A - STT (index 0)
        // B - Loại văn bằng (index 1)
        // C - Họ và tên (index 2)
        // D - Ngày sinh (index 3)
        // E - Nơi sinh (index 4)
        // F - Quê quán (index 5)
        // G - Nguyên quán (index 6)
        // H - Giới tính (index 7)
        // I - Dân tộc (index 8)
        // J - Quốc tịch (index 9)
        // K - Khoá (index 10)
        // L - Lớp (index 11)
        // M - Niên khoá (index 12)
        // N - Ngành đào tạo (index 13)
        // O - Hình thức đào tạo (index 14)
        // P - Số QĐ (index 15)
        // Q - Ngày tháng QĐ (index 16)
        // R - Ngày bảo vệ (index 17)
        // S - Số QĐ công nhận (index 18)
        // T - Ngày tháng QĐ công nhận (index 19)
        // U - Năm tốt nghiệp (index 20)
        // V - Xếp loại (index 21)
        // W - Số hiệu văn bằng (index 22)
        // X - Số vào sổ (index 23)
        // Y - Ngày cấp (index 24)
        // Z - Nội dung điều chỉnh (index 25)
        // AA - QĐ điều chỉnh (index 26)
        // AB - Ngày QĐ điều chỉnh (index 27)
        // AC - Số hiệu văn bằng cấp lại (index 28)
        // AD - Nội dung chỉnh sửa (index 29)
        // AE - QĐ thu hồi (index 30)
        // AF - Ngày QĐ cấp lại (index 31)
        // AG - Ghi chú (index 32)

        // Skip empty rows
        $fullName = $this->cleanString($row[2] ?? '');
        if (empty($fullName)) {
            return; // Skip this row
        }

        $dateOfBirth = $this->parseDate($row[3] ?? '');
        $placeOfBirth = $this->cleanString($row[4] ?? '');
        $hometown = $this->cleanString($row[5] ?? '');
        $placeOfOrigin = $this->cleanString($row[6] ?? '');
        $gender = $this->parseGender($row[7] ?? '');
        $nation = $this->cleanString($row[8] ?? '');
        $nationality = $this->cleanString($row[9] ?? 'Việt Nam');
        $course = $this->cleanString($row[10] ?? '');
        $className = $this->cleanString($row[11] ?? '');
        $academicYear = $this->cleanString($row[12] ?? '');
        $majorName = $this->cleanString($row[13] ?? '');
        $trainingType = $this->cleanString($row[14] ?? '');
        // Normalize training_type to match enum values
        $trainingType = $this->normalizeTrainingType($trainingType);
        $councilDecisionNumber = $this->cleanString($row[15] ?? '');
        $councilDecisionDate = $this->parseDate($row[16] ?? '');
        $defenseDate = $this->parseDate($row[17] ?? '');
        $graduationDecisionNumber = $this->cleanString($row[18] ?? '');
        $graduationDecisionDate = $this->parseDate($row[19] ?? '');
        $graduationYear = $this->cleanString($row[20] ?? '');
        $ranking = $this->cleanString($row[21] ?? '');
        $diplomaNumber = $this->cleanString($row[22] ?? ''); // W - Số hiệu văn bằng
        $registrationNumber = $this->cleanString($row[23] ?? '');
        $grantingDate = $this->parseDate($row[24] ?? '');
        $notes = $this->cleanString($row[32] ?? '');

        // Parse degree type from column B
        $degreeType = $this->parseDegreeType($row[1] ?? '');


        // Tìm hoặc tạo Major
        $major = null;
        if (!empty($majorName)) {
            // Tìm major theo tên trước
            $major = Major::where('major_name', $majorName)->first();

            // Nếu chưa có, tạo mới (nhưng cần kiểm tra major_code không trùng)
            if (!$major) {
                $majorCode = $this->generateMajorCode($majorName);

                // Kiểm tra major_code đã tồn tại chưa
                $existingByCode = Major::where('major_code', $majorCode)->first();
                if ($existingByCode) {
                    // Nếu có major với code này rồi thì dùng luôn
                    $major = $existingByCode;
                } else {
                    // Tạo mới
                    $major = Major::create([
                        'major_name' => $majorName,
                        'major_code' => $majorCode
                    ]);

                    Log::info('Created new major', [
                        'major_name' => $majorName,
                        'major_code' => $major->major_code,
                        'major_id' => $major->major_id
                    ]);
                }
            }
        }

        // Tìm Student thông qua registration_number (nếu degree đã tồn tại)
        // Đây là cách tốt nhất để tránh duplicate khi import lại cùng file
        $existingDegree = Degree::where('registration_number', $registrationNumber)->first();
        $student = $existingDegree?->student;

        // Nếu không tìm thấy qua degree, tìm theo full_name + date_of_birth + place_of_birth
        if (!$student) {
            $student = Student::where('full_name', $fullName)
                ->where('date_of_birth', $dateOfBirth)
                ->where('place_of_birth', $placeOfBirth)
                ->first();
        }

        if (!$student) {
            // Tạo student_code dựa trên registration_number để đảm bảo unique và consistent
            // Thay thế ký tự đặc biệt trong registration_number
            $cleanRegNumber = preg_replace('/[^A-Z0-9]/', '', $registrationNumber);
            $studentCode = 'IMP_' . $cleanRegNumber;

            $student = Student::create([
                'student_code' => $studentCode,
                'full_name' => $fullName,
                'date_of_birth' => $dateOfBirth,
                'place_of_birth' => $placeOfBirth,
                'hometown' => $hometown,
                'place_of_origin' => $placeOfOrigin,
                'gender' => $gender,
                'nation' => $nation,
                'nationality' => $nationality,
                'course' => $course,
                'class_name' => $className,
                'academic_year' => $academicYear,
                'major_id' => $major?->major_id,
                'training_type' => $trainingType,
                'number_in_the_book' => $registrationNumber, // Sử dụng số vào sổ
                'status' => 1, // Graduate
            ]);
        }

        // Tạo hoặc cập nhật DiplomaBlank nếu có số hiệu văn bằng
        $diplomaBlank = null;
        if (!empty($diplomaNumber)) {
            $typeId = $this->getTypeIdForDegreeType($degreeType);
            $diplomaBlank = DiplomaBlank::firstOrCreate(
                ['serial_number' => $diplomaNumber],
                [
                    'import_id' => $this->diplomaBlankImportId,
                    'type_id' => $typeId,
                    'status' => DiplomaBlankStatus::ISSUED,
                ]
            );
        }

        // Kiểm tra Degree đã tồn tại chưa (theo registration_number)
        $degree = Degree::where('registration_number', $registrationNumber)->first();

        if ($degree) {
            // Nếu degree đã tồn tại, skip row này
            Log::info('Degree already exists, skipping', [
                'registration_number' => $registrationNumber,
                'existing_degree_id' => $degree->degree_id
            ]);
            return;
        }

        // Tạo Degree mới
        $degree = Degree::create([
            'student_id' => $student->student_id,
            'degree_type' => $degreeType,
            'diploma_blank_id' => $diplomaBlank?->diploma_blank_id,
            'registration_number' => $registrationNumber,
            'granting_date' => $grantingDate,
            'graduation_year' => $graduationYear,
            'ranking' => $ranking,
            'council_decision_number' => $councilDecisionNumber,
            'council_decision_date' => $councilDecisionDate,
            'graduation_decision_number' => $graduationDecisionNumber,
            'graduation_decision_date' => $graduationDecisionDate,
            'major_id' => $major?->major_id,
            'major_name' => $majorName,
            'defense_date' => $defenseDate,
            'notes' => $notes,
        ]);

        // ChangeLog creation được xử lý tự động bởi DegreeObserver

        // Xử lý ChangeLog điều chỉnh (Z, AA, AB)
        // Đây là dữ liệu lịch sử từ file import, không phải thay đổi thực tế
        // nên cần tạo thủ công (Observer không detect được)
        $adjustmentContent = $this->cleanString($row[25] ?? '');
        $adjustmentDecision = $this->cleanString($row[26] ?? '');
        $adjustmentDate = $this->parseDate($row[27] ?? '');

        if (!empty($adjustmentContent) || !empty($adjustmentDecision)) {
            ChangeLog::create([
                'entity_type' => class_basename(Degree::class),
                'entity_id' => $degree->degree_id,
                'change_description' => $adjustmentContent ?: 'Điều chỉnh thông tin từ import',
                'decision_number' => $adjustmentDecision,
                'decision_date' => $adjustmentDate,
                'action_type' => 'update',
                'changed_by' => Auth::id(),
                'additional_data' => [
                    'source' => 'import',
                    'document_reference' => $this->documentReference,
                ]
            ]);
        }

        // Xử lý DegreeReissue (AC, AD, AE, AF)
        $reissueNumber = $this->cleanString($row[28] ?? ''); // AC - Số hiệu văn bằng cấp lại
        $reissueContent = $this->cleanString($row[29] ?? ''); // AD - Nội dung chỉnh sửa
        $reissueDecision = $this->cleanString($row[30] ?? ''); // AE - QĐ thu hồi
        $reissueDate = $this->parseDate($row[31] ?? ''); // AF - Ngày QĐ cấp lại

        // Log để debug
        Log::info('Reissue data check', [
            'registration_number' => $registrationNumber,
            'reissueNumber' => $reissueNumber,
            'reissueContent' => $reissueContent,
            'reissueDecision' => $reissueDecision,
            'reissueDate' => $reissueDate,
            'diplomaNumber' => $diplomaNumber,
            'diplomaBlankId' => $diplomaBlank?->diploma_blank_id
        ]);

        // Kiểm tra có data để tạo reissue hay không (chỉ cần có AC hoặc AD)
        if (!empty($reissueNumber) || !empty($reissueContent)) {
            // Tạo DiplomaBlank mới cho văn bằng cấp lại từ ô AC (nếu có)
            $newDiplomaBlank = null;
            if (!empty($reissueNumber)) {
                $typeId = $this->getTypeIdForDegreeType($degreeType);
                $newDiplomaBlank = DiplomaBlank::firstOrCreate(
                    ['serial_number' => $reissueNumber],
                    [
                        'import_id' => $this->diplomaBlankImportId,
                        'type_id' => $typeId,
                        'status' => DiplomaBlankStatus::ISSUED,
                    ]
                );

                Log::info('Created new diploma blank for reissue', [
                    'serial_number' => $reissueNumber,
                    'diploma_blank_id' => $newDiplomaBlank->diploma_blank_id
                ]);
            }

            // Tạo DegreeReissue với old = W (diploma_blank_id từ degree), new = AC
            $reissue = DegreeReissue::create([
                'degree_id' => $degree->degree_id,
                'old_diploma_blank_id' => $diplomaBlank?->diploma_blank_id, // Phôi cũ từ ô W
                'new_diploma_blank_id' => $newDiplomaBlank?->diploma_blank_id, // Phôi mới từ ô AC
                'edit_content' => $reissueContent ?: 'Cấp lại văn bằng',
                'recall_decision' => $reissueDecision,
                'decision_date' => $reissueDate,
                'created_by' => Auth::id(),
            ]);

            Log::info('Created degree reissue', [
                'reissue_id' => $reissue->reissue_id,
                'old_blank_id' => $reissue->old_diploma_blank_id,
                'new_blank_id' => $reissue->new_diploma_blank_id
            ]);

            // CẬP NHẬT degree->diploma_blank_id thành phôi mới (AC) nếu có
            if ($newDiplomaBlank) {
                $degree->update([
                    'diploma_blank_id' => $newDiplomaBlank->diploma_blank_id
                ]);

                Log::info('Updated degree diploma_blank_id to new blank', [
                    'degree_id' => $degree->degree_id,
                    'new_diploma_blank_id' => $newDiplomaBlank->diploma_blank_id
                ]);
            }
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
     * Load diploma blank types into cache
     */
    protected function loadDiplomaBlankTypes(): void
    {
        if (self::$diplomaBlankTypes === null) {
            self::$diplomaBlankTypes = DiplomaBlankType::all()->keyBy('prefix');
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
