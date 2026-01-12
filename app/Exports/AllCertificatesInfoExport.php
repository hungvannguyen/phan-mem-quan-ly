<?php

namespace App\Exports;

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AllCertificatesInfoExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Generate all certificates info report from template
     * Logic: Load file -> Xác định dòng 5 -> Duyệt sinh viên -> Ghi từng ô -> Đẩy footer xuống
     */
    public function generate()
    {
        // Increase execution time for large exports
        set_time_limit(300); // 5 minutes

        // Query students with certificate degrees (all types)
        $query = Student::with([
                'major',
                'degrees.major',
                'degrees.diplomaBlank.type',
                'degrees.changeLogs',
                'degrees.reissues.newDiplomaBlank'
            ])
            ->whereHas('degrees', function ($q) {
                $q->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate');
            });

        // Apply filters
        if (!empty($this->filters['graduation_year'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereYear('granting_date', $this->filters['graduation_year'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate');
            });
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '>=', $this->filters['start_date'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate');
            });
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '<=', $this->filters['end_date'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate');
            });
        }

        if (!empty($this->filters['major_id'])) {
            $query->where('major_id', $this->filters['major_id']);
        }

        if (!empty($this->filters['ranking'])) {
            $query->whereHas('degrees', function ($q) {
                $q->where('ranking', $this->filters['ranking'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate');
            });
        }

        if (!empty($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        // Execute query
        $students = $query->get();

        // Filter out students without certificate degrees
        $students = $students->filter(function ($student) {
            foreach ($student->degrees as $degree) {
                if (
                    $degree->degree_type === 'certificate'
                    && $degree->registration_number !== null
                ) {
                    return true;
                }
            }
            return false;
        });

        \Log::info('AllCertificatesInfoExport: Query returned ' . $students->count() . ' students with certificates');

        // Check if there's any data to export
        if ($students->count() === 0) {
            \Log::warning('AllCertificatesInfoExport: No data found - throwing exception');
            throw new \Exception('Không có dữ liệu chứng chỉ để xuất');
        }

        // Step 1: Load file mẫu
        $templatePath = resource_path('templates/[Mau TT06] Thong tin cap chung chi.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $templatePath);
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Step 2: Xác định dòng bắt đầu (hardcoded = 5)
        $startRow = 5;
        $totalStudents = $students->count();

        // Step 3: Insert rows và copy style (tối ưu tốc độ)
        // Disable automatic calculation for better performance
        $spreadsheet->getActiveSheet()->setSelectedCell('A1');

        // Insert rows at once (for students 2, 3, 4, ...)
        if ($totalStudents > 1) {
            $sheet->insertNewRowBefore($startRow + 1, $totalStudents - 1);
        }

        // Get row height from template
        $rowHeight = $sheet->getRowDimension($startRow)->getRowHeight();

        // Step 4: Duyệt danh sách sinh viên và ghi vào từng ô
        $currentRow = $startRow;
        $stt = 1;

        foreach ($students as $student) {
            // Get first certificate degree
            $degree = null;
            foreach ($student->degrees as $d) {
                if (
                    $d->degree_type === 'certificate'
                    && $d->registration_number !== null
                ) {
                    $degree = $d;
                    break;
                }
            }

            if (!$degree) {
                continue;
            }

            // Set row height
            if ($rowHeight) {
                $sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);
            }

            // Get training program name from diploma blank type or major
            $trainingProgram = '';
            if ($degree->diplomaBlank && $degree->diplomaBlank->type) {
                $trainingProgram = $degree->diplomaBlank->type->type_name;
            } elseif ($degree->major) {
                $trainingProgram = $degree->major->major_name;
            }

            // Ghi data vào các cột A-P theo mapping
            $sheet->setCellValue('A' . $currentRow, $stt); // TT (STT)
            $sheet->setCellValue('B' . $currentRow, $student->full_name ?? ''); // Họ và tên
            $sheet->setCellValue('C' . $currentRow, $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : ''); // Ngày sinh
            $sheet->setCellValue('D' . $currentRow, $student->place_of_birth ?? ''); // Nơi sinh
            $sheet->setCellValue('E' . $currentRow, $this->getGenderLabel($student->gender)); // Giới tính
            $sheet->setCellValue('F' . $currentRow, $student->nation ?? ''); // Dân tộc
            $sheet->setCellValue('G' . $currentRow, $trainingProgram); // Chương trình bồi dưỡng
            $sheet->setCellValue('H' . $currentRow, $degree->ranking ?? ''); // Xếp loại
            $sheet->setCellValue('I' . $currentRow, $degree->registration_number ?? ''); // Số hiệu chứng chỉ
            $sheet->setCellValue('J' . $currentRow, $student->number_in_the_book ?? ''); // Số vào sổ gốc cấp văn bằng
            $sheet->setCellValue('K' . $currentRow, $degree->training_start_date ? $degree->training_start_date->format('d/m/Y') : ''); // Thời gian đào tạo từ ngày
            $sheet->setCellValue('L' . $currentRow, $degree->training_end_date ? $degree->training_end_date->format('d/m/Y') : ''); // Thời gian đào tạo đến ngày
            $sheet->setCellValue('M' . $currentRow, $degree->decision_number ?? ''); // Số quyết định công nhận tốt nghiệp
            $sheet->setCellValue('N' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày tháng công nhận tốt nghiệp
            $sheet->setCellValue('O' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày cấp
            $sheet->setCellValue('P' . $currentRow, 'Đã cấp'); // Tình trạng

            $stt++;
            $currentRow++;
        }

        \Log::info('AllCertificatesInfoExport: Processed ' . ($stt - 1) . ' students');

        // Step 5: Footer được tự động đẩy xuống khi insert rows

        // Set fixed column widths instead of AutoSize (much faster)
        $columnWidths = [
            'A' => 6,   // TT
            'B' => 30,  // Họ và tên
            'C' => 13,  // Ngày sinh
            'D' => 25,  // Nơi sinh
            'E' => 11,  // Giới tính
            'F' => 13,  // Dân tộc
            'G' => 35,  // Chương trình bồi dưỡng
            'H' => 16,  // Xếp loại
            'I' => 20,  // Số hiệu chứng chỉ
            'J' => 13,  // Số vào sổ
            'K' => 13,  // Từ ngày
            'L' => 13,  // Đến ngày
            'M' => 20,  // Số quyết định
            'N' => 13,  // Ngày công nhận
            'O' => 13,  // Ngày cấp
            'P' => 13,  // Tình trạng
        ];

        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Generate filename and save
        $filename = $this->generateFilename();
        $outputPath = storage_path('app/temp/' . $filename);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Save the document
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \Exception('Không thể tạo file xuất');
        }

        \Log::info('AllCertificatesInfoExport: File saved to ' . $outputPath);

        return $outputPath;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename()
    {
        return 'Thong_tin_cap_chung_chi_' . date('Y-m-d_His') . '.xlsx';
    }

    /**
     * Download the generated file
     */
    public function download()
    {
        $filePath = $this->generate();

        return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
    }

    /**
     * Get gender label from enum
     */
    protected function getGenderLabel($gender)
    {
        if (is_null($gender)) {
            return '';
        }

        // If it's StudentGender enum, use the label() method
        if (is_object($gender) && method_exists($gender, 'label')) {
            return $gender->label();
        }

        // Handle other enum objects
        if (is_object($gender)) {
            if (property_exists($gender, 'value')) {
                $gender = $gender->value;
            } elseif (property_exists($gender, 'name')) {
                $gender = $gender->name;
            } else {
                return '';
            }
        }

        // Ensure we have a string
        if (!is_string($gender)) {
            return '';
        }

        // Convert to lowercase for comparison
        $genderLower = strtolower(trim($gender));

        return match ($genderLower) {
            'male', 'nam', 'm', '0' => 'Nam',
            'female', 'nữ', 'nu', 'f', '1' => 'Nữ',
            default => ucfirst($gender)
        };
    }
}
