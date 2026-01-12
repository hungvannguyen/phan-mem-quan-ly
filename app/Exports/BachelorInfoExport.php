<?php

namespace App\Exports;

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BachelorInfoExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Generate bachelor info report from template
     * Logic: Load file -> Xác định dòng 5 -> Duyệt sinh viên -> Ghi từng ô -> Đẩy footer xuống
     */
    public function generate()
    {
        // Increase execution time for large exports
        set_time_limit(300); // 5 minutes

        // Step 1: Load file mẫu
        $templatePath = resource_path('templates/[Mau TT01] Thong tin cap bang cu nhan.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $templatePath);
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Query students with bachelor degrees
        $query = Student::with([
                'major',
                'degrees.major',
                'degrees.diplomaBlank.type',
                'degrees.changeLogs',
                'degrees.reissues.newDiplomaBlank'
            ])
            ->whereHas('degrees', function ($q) {
                $q->whereNotNull('registration_number');
            });

        // Apply filters
        if (!empty($this->filters['graduation_year'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereYear('granting_date', $this->filters['graduation_year'])
                    ->whereNotNull('registration_number');
            });
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '>=', $this->filters['start_date'])
                    ->whereNotNull('registration_number');
            });
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '<=', $this->filters['end_date'])
                    ->whereNotNull('registration_number');
            });
        }

        if (!empty($this->filters['major_id'])) {
            $query->where('major_id', $this->filters['major_id']);
        }

        if (!empty($this->filters['ranking'])) {
            $query->whereHas('degrees', function ($q) {
                $q->where('ranking', $this->filters['ranking'])
                    ->whereNotNull('registration_number');
            });
        }

        if (!empty($this->filters['training_type'])) {
            $query->where('training_type', $this->filters['training_type']);
        }

        if (!empty($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        // Execute query
        $students = $query->get();

        \Log::info('BachelorInfoExport: Query returned ' . $students->count() . ' students with bachelor degrees');

        // Check if there's any data to export
        if ($students->count() === 0) {
            \Log::warning('BachelorInfoExport: No data found - throwing exception');
            throw new \Exception('Không có dữ liệu bằng cử nhân để xuất');
        }

        // Filter out students without degrees
        $students = $students->filter(function ($student) {
            return $student->degrees->isNotEmpty() &&
                $student->degrees->first()->registration_number !== null;
        });

        \Log::info('BachelorInfoExport: Found ' . $students->count() . ' students');

        // Step 2: Xác định dòng bắt đầu (hardcoded = 5)
        $startRow = 5;
        $totalStudents = $students->count();

        if ($totalStudents === 0) {
            throw new \Exception('Không có dữ liệu sinh viên để xuất');
        }

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
            $degree = $student->degrees->first();

            if (!$degree) {
                continue;
            }

            // Set row height
            if ($rowHeight) {
                $sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);
            }

            // Ghi data vào các cột A-T theo mapping
            $sheet->setCellValue('A' . $currentRow, $stt); // STT
            $sheet->setCellValue('B' . $currentRow, $student->full_name ?? ''); // Họ tên
            $sheet->setCellValue('C' . $currentRow, $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : ''); // Ngày sinh
            $sheet->setCellValue('D' . $currentRow, $student->place_of_birth ?? ''); // Nơi sinh
            $sheet->setCellValue('E' . $currentRow, $this->getGenderLabel($student->gender)); // Giới tính
            $sheet->setCellValue('F' . $currentRow, $student->nation ?? ''); // Dân tộc
            $sheet->setCellValue('G' . $currentRow, $student->nationality ?? ''); // Quốc tịch
            $sheet->setCellValue('H' . $currentRow, $degree->major->major_name ?? $student->major->major_name ?? ''); // Ngành đào tạo
            $sheet->setCellValue('I' . $currentRow, $degree->granting_date ? $degree->granting_date->format('Y') : ''); // Năm tốt nghiệp
            $sheet->setCellValue('J' . $currentRow, $degree->ranking ?? ''); // Xếp loại
            $sheet->setCellValue('K' . $currentRow, $degree->registration_number ?? ''); // Số hiệu bằng
            $sheet->setCellValue('L' . $currentRow, $student->number_in_the_book ?? ''); // Số vào sổ
            $sheet->setCellValue('M' . $currentRow, $student->course ?? ''); // Khoá
            $sheet->setCellValue('N' . $currentRow, $student->class_name ?? ''); // Lớp
            $sheet->setCellValue('O' . $currentRow, $student->academic_year ?? ''); // Niên khoá
            $sheet->setCellValue('P' . $currentRow, $student->training_type ?? 'Chính quy'); // Hình thức đào tạo
            $sheet->setCellValue('Q' . $currentRow, $degree->decision_number ?? ''); // Số quyết định
            $sheet->setCellValue('R' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày quyết định
            $sheet->setCellValue('S' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày cấp
            $sheet->setCellValue('T' . $currentRow, 'Đã cấp'); // Tình trạng

            $stt++;
            $currentRow++;
        }

        \Log::info('BachelorInfoExport: Processed ' . ($stt - 1) . ' students');

        // Step 5: Footer được tự động đẩy xuống khi insert rows

        // Set fixed column widths instead of AutoSize (much faster)
        $columnWidths = [
            'A' => 6,   // STT
            'B' => 30,  // Họ tên
            'C' => 13,  // Ngày sinh
            'D' => 25,  // Nơi sinh
            'E' => 11,  // Giới tính
            'F' => 13,  // Dân tộc
            'G' => 13,  // Quốc tịch
            'H' => 40,  // Ngành đào tạo
            'I' => 10,  // Năm TN
            'J' => 16,  // Xếp loại
            'K' => 18,  // Số hiệu bằng
            'L' => 13,  // Số vào sổ
            'M' => 9,   // Khoá
            'N' => 16,  // Lớp
            'O' => 13,  // Niên khoá
            'P' => 20,  // Hình thức
            'Q' => 18,  // Số QĐ
            'R' => 13,  // Ngày QĐ
            'S' => 13,  // Ngày cấp
            'T' => 13,  // Tình trạng
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

        \Log::info('BachelorInfoExport: File saved to ' . $outputPath);

        return $outputPath;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename()
    {
        return 'Thong_tin_cap_bang_cu_nhan_' . date('Y-m-d_His') . '.xlsx';
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
