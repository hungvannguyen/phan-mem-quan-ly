<?php

namespace App\Exports;

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdvancedPoliticalTheoryInfoExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Generate advanced political theory certificate info report from template
     * Logic: Load file -> Xác định dòng 5 -> Duyệt sinh viên -> Ghi từng ô -> Đẩy footer xuống
     */
    public function generate()
    {
        // Increase execution time for large exports
        set_time_limit(300); // 5 minutes

        // Step 1: Load file mẫu
        $templatePath = resource_path('templates/[Mau TT04] Thong tin cap bang cao cap LLCT.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $templatePath);
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Query students with certificate degrees (Cao cấp lý luận chính trị)
        $query = Student::with(['major', 'degrees.major', 'degrees.diplomaBlank.type'])
            ->whereHas('degrees', function ($q) {
                $q->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate')
                    ->whereHas('diplomaBlank.type', function ($typeQuery) {
                        $typeQuery->where('type_name', 'LIKE', '%Cao cấp lý luận chính trị%');
                    });
            });

        // Apply filters
        if (!empty($this->filters['graduation_year'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereYear('granting_date', $this->filters['graduation_year'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate')
                    ->whereHas('diplomaBlank.type', function ($typeQuery) {
                        $typeQuery->where('type_name', 'LIKE', '%Cao cấp lý luận chính trị%');
                    });
            });
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '>=', $this->filters['start_date'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate')
                    ->whereHas('diplomaBlank.type', function ($typeQuery) {
                        $typeQuery->where('type_name', 'LIKE', '%Cao cấp lý luận chính trị%');
                    });
            });
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '<=', $this->filters['end_date'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate')
                    ->whereHas('diplomaBlank.type', function ($typeQuery) {
                        $typeQuery->where('type_name', 'LIKE', '%Cao cấp lý luận chính trị%');
                    });
            });
        }

        if (!empty($this->filters['major_id'])) {
            $query->where('major_id', $this->filters['major_id']);
        }

        if (!empty($this->filters['ranking'])) {
            $query->whereHas('degrees', function ($q) {
                $q->where('ranking', $this->filters['ranking'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'certificate')
                    ->whereHas('diplomaBlank.type', function ($typeQuery) {
                        $typeQuery->where('type_name', 'LIKE', '%Cao cấp lý luận chính trị%');
                    });
            });
        }

        if (!empty($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        // Execute query
        $students = $query->get();

        // Filter out students without certificate degrees
        $students = $students->filter(function ($student) {
            $certificateDegree = $student->degrees->where('degree_type', 'certificate')->first();
            return $certificateDegree && $certificateDegree->registration_number !== null;
        });

        \Log::info('AdvancedPoliticalTheoryInfoExport: Found ' . $students->count() . ' students with certificates');

        // Step 2: Xác định dòng bắt đầu (hardcoded = 5)
        $startRow = 5;
        $totalStudents = $students->count();

        if ($totalStudents === 0) {
            throw new \Exception('Không có dữ liệu chứng chỉ cao cấp lý luận chính trị để xuất');
        }

        // Step 3: Insert rows và copy style (tối ưu tốc độ)
        // Disable automatic calculation for better performance
        $spreadsheet->getActiveSheet()->setSelectedCell('A1');

        // Save template row style before inserting new rows
        $templateRowStyle = $sheet->getStyle($startRow . ':' . $startRow);

        // Insert rows at once (for students 2, 3, 4, ...)
        if ($totalStudents > 1) {
            $sheet->insertNewRowBefore($startRow + 1, $totalStudents - 1);

            // Copy style to all new rows at once
            $endRow = $startRow + $totalStudents - 1;
            $sheet->duplicateStyle($templateRowStyle, ($startRow + 1) . ':' . $endRow);
        }

        // Get row height from template
        $rowHeight = $sheet->getRowDimension($startRow)->getRowHeight();

        // Step 4: Duyệt danh sách sinh viên và ghi vào từng ô
        $currentRow = $startRow;
        $stt = 1;

        foreach ($students as $student) {
            // Get advanced political theory certificate degree specifically
            $degree = null;
            foreach ($student->degrees as $d) {
                if (
                    $d->degree_type === 'certificate'
                    && $d->diplomaBlank
                    && $d->diplomaBlank->type
                    && stripos($d->diplomaBlank->type->type_name, 'Cao cấp lý luận chính trị') !== false
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

            // Ghi data vào các cột A-P theo mapping
            $sheet->setCellValue('A' . $currentRow, $stt); // STT
            $sheet->setCellValue('B' . $currentRow, $student->full_name ?? ''); // Họ và tên
            $sheet->setCellValue('C' . $currentRow, $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : ''); // Ngày sinh
            $sheet->setCellValue('D' . $currentRow, $student->place_of_birth ?? ''); // Nơi sinh
            $sheet->setCellValue('E' . $currentRow, $this->getGenderLabel($student->gender)); // Giới tính
            $sheet->setCellValue('F' . $currentRow, $student->nation ?? ''); // Dân tộc
            $sheet->setCellValue('G' . $currentRow, $student->training_type ?? 'Chính quy'); // Loại hình đào tạo
            $sheet->setCellValue('H' . $currentRow, $student->course ?? ''); // Khóa
            $sheet->setCellValue('I' . $currentRow, $degree->ranking ?? ''); // Xếp loại tốt nghiệp
            $sheet->setCellValue('J' . $currentRow, $degree->registration_number ?? ''); // Số hiệu văn bằng
            $sheet->setCellValue('K' . $currentRow, $student->number_in_the_book ?? ''); // Số vào sổ gốc cấp văn bằng
            $sheet->setCellValue('L' . $currentRow, $student->academic_year ?? ''); // Khóa học
            $sheet->setCellValue('M' . $currentRow, $degree->decision_number ?? ''); // Số Quyết định
            $sheet->setCellValue('N' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày tháng
            $sheet->setCellValue('O' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày cấp
            $sheet->setCellValue('P' . $currentRow, 'Đã cấp'); // Tình trạng

            $stt++;
            $currentRow++;
        }

        \Log::info('AdvancedPoliticalTheoryInfoExport: Processed ' . ($stt - 1) . ' students');

        // Step 5: Footer được tự động đẩy xuống khi insert rows

        // Set fixed column widths instead of AutoSize (much faster)
        $columnWidths = [
            'A' => 5,   // STT
            'B' => 25,  // Họ và tên
            'C' => 12,  // Ngày sinh
            'D' => 20,  // Nơi sinh
            'E' => 10,  // Giới tính
            'F' => 12,  // Dân tộc
            'G' => 18,  // Loại hình đào tạo
            'H' => 8,   // Khóa
            'I' => 15,  // Xếp loại
            'J' => 15,  // Số hiệu văn bằng
            'K' => 12,  // Số vào sổ
            'L' => 12,  // Khóa học
            'M' => 15,  // Số quyết định
            'N' => 12,  // Ngày tháng
            'O' => 12,  // Ngày cấp
            'P' => 12,  // Tình trạng
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

        \Log::info('AdvancedPoliticalTheoryInfoExport: File saved to ' . $outputPath);

        return $outputPath;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename()
    {
        return 'Thong_tin_cap_bang_cao_cap_LLCT_' . date('Y-m-d_His') . '.xlsx';
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
