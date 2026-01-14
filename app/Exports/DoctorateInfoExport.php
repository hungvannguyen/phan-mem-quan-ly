<?php

namespace App\Exports;

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoctorateInfoExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Generate doctorate info report from template
     * Logic: Load file -> Xác định dòng 5 -> Duyệt sinh viên -> Ghi từng ô -> Đẩy footer xuống
     */
    public function generate()
    {
        // Increase execution time for large exports
        set_time_limit(300); // 5 minutes

        // Query students with doctorate degrees
        $query = Student::with([
                'major',
                'degrees.major',
                'degrees.diplomaBlank.type',
                'degrees.changeLogs',
                'degrees.reissues.newDiplomaBlank'
            ])
            ->whereHas('degrees', function ($q) {
                $q->whereNotNull('registration_number')
                    ->where('degree_type', 'doctor'); // Filter only doctorate degrees
            });

        // Apply filters
        if (!empty($this->filters['graduation_year'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereYear('granting_date', $this->filters['graduation_year'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'doctor');
            });
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '>=', $this->filters['start_date'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'doctor');
            });
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereHas('degrees', function ($q) {
                $q->whereDate('granting_date', '<=', $this->filters['end_date'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'doctor');
            });
        }

        if (!empty($this->filters['major_id'])) {
            $query->where('major_id', $this->filters['major_id']);
        }

        if (!empty($this->filters['ranking'])) {
            $query->whereHas('degrees', function ($q) {
                $q->where('ranking', $this->filters['ranking'])
                    ->whereNotNull('registration_number')
                    ->where('degree_type', 'doctor');
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

        // Filter out students without doctorate degrees
        $students = $students->filter(function ($student) {
            $doctorateDegree = $student->degrees->where('degree_type', 'doctor')->first();
            return $doctorateDegree && $doctorateDegree->registration_number !== null;
        });

        \Log::info('DoctorateInfoExport: Query returned ' . $students->count() . ' students with doctorate degrees');

        // Check if there's any data to export BEFORE loading template
        if ($students->count() === 0) {
            \Log::warning('DoctorateInfoExport: No data found - throwing exception');
            throw new \Exception('Không có dữ liệu bằng tiến sĩ để xuất');
        }

        // Step 1: Load file mẫu (only after confirming there's data)
        $templatePath = resource_path('templates/[Mau TT03] Thong tin cap bang tien si.xlsx');

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
            // Get doctorate degree specifically
            $degree = $student->degrees->where('degree_type', 'doctor')->first();

            if (!$degree) {
                continue;
            }

            // Set row height
            if ($rowHeight) {
                $sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);
            }

            // Get latest change log for this degree (from eager loaded collection)
            // Chỉ lấy logs có action_type là update, không lấy create
            $latestChangeLog = $degree->changeLogs
                ->where('action_type', 'update')
                ->sortByDesc('created_at')
                ->first();

            // Get latest reissue for this degree (from eager loaded collection)
            $latestReissue = $degree->reissues->sortByDesc('decision_date')->first();

            // Ghi data vào các cột A-S theo mapping
            $sheet->setCellValue('A' . $currentRow, $stt); // STT
            $sheet->setCellValue('B' . $currentRow, $student->full_name ?? ''); // Họ và tên
            $sheet->setCellValue('C' . $currentRow, $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : ''); // Ngày sinh
            $sheet->setCellValue('D' . $currentRow, $student->place_of_birth ?? ''); // Nơi sinh
            $sheet->setCellValue('E' . $currentRow, $this->getGenderLabel($student->gender)); // Giới tính
            $sheet->setCellValue('F' . $currentRow, $student->nation ?? ''); // Dân tộc
            $sheet->setCellValue('G' . $currentRow, $student->nationality ?? ''); // Quốc tịch
            $sheet->setCellValue('H' . $currentRow, $degree->major->major_name ?? $student->major->major_name ?? ''); // Ngành đào tạo
            $sheet->setCellValue('I' . $currentRow, $degree->council_decision_number ?? ''); // Số quyết định đánh giá luận án
            $sheet->setCellValue('J' . $currentRow, $degree->council_decision_date ? $degree->council_decision_date->format('d/m/Y') : ''); // Ngày Tháng quyết định
            $sheet->setCellValue('K' . $currentRow, $degree->defense_date ? $degree->defense_date->format('d/m/Y') : ''); // Ngày bảo vệ
            $sheet->setCellValue('L' . $currentRow, $degree->registration_number ?? ''); // Số hiệu văn bằng
            $sheet->setCellValue('M' . $currentRow, $student->number_in_the_book ?? ''); // Số vào sổ gốc cấp văn bằng
            $sheet->setCellValue('N' . $currentRow, $student->course ?? ''); // Khoá
            $sheet->setCellValue('O' . $currentRow, $student->training_type ?? 'Chính quy'); // Hình thức đào tạo
            $sheet->setCellValue('P' . $currentRow, $degree->decision_number ?? ''); // Số quyết định
            $sheet->setCellValue('Q' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày tháng (quyết định)
            $sheet->setCellValue('R' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày cấp
            $sheet->setCellValue('S' . $currentRow, 'Đã cấp'); // Tình trạng

            // Điều chỉnh thông tin (Change Logs) - chỉ ghi nếu có dữ liệu và có changed_field
            if ($latestChangeLog && $latestChangeLog->changed_field) {
                $sheet->setCellValue('T' . $currentRow, $latestChangeLog->change_description ?? ''); // Nội dung điều chỉnh
                $sheet->setCellValue('U' . $currentRow, $latestChangeLog->decision_number ?? ''); // QĐ điều chỉnh thông tin
                $sheet->setCellValue('V' . $currentRow, $latestChangeLog->decision_date ? $latestChangeLog->decision_date->format('d/m/Y') : ''); // Ngày QĐ
            }

            // Cấp lại văn bằng (Reissues) - chỉ ghi nếu có dữ liệu
            if ($latestReissue) {
                $sheet->setCellValue('W' . $currentRow, $latestReissue->newDiplomaBlank?->serial_number ?? ''); // Số hiệu văn bằng mới
                $sheet->setCellValue('X' . $currentRow, $latestReissue->edit_content ?? ''); // Nội dung chỉnh sửa
                $sheet->setCellValue('Y' . $currentRow, $latestReissue->recall_decision ?? ''); // QĐ thu hồi, hủy bỏ và cấp lại
                $sheet->setCellValue('Z' . $currentRow, $latestReissue->decision_date ? $latestReissue->decision_date->format('d/m/Y') : ''); // Ngày QĐ
                $sheet->setCellValue('AA' . $currentRow, $latestReissue->notes ?? ''); // Ghi chú
            }

            $stt++;
            $currentRow++;
        }

        \Log::info('DoctorateInfoExport: Processed ' . ($stt - 1) . ' students');

        // Step 5: Footer được tự động đẩy xuống khi insert rows

        // Set fixed column widths instead of AutoSize (much faster)
        $columnWidths = [
            'A' => 6,   // STT
            'B' => 30,  // Họ và tên
            'C' => 13,  // Ngày sinh
            'D' => 25,  // Nơi sinh
            'E' => 11,  // Giới tính
            'F' => 13,  // Dân tộc
            'G' => 13,  // Quốc tịch
            'H' => 28,  // Ngành đào tạo
            'I' => 26,  // Số QĐ (QĐ thành lập hội đồng đánh giá luận án)
            'J' => 13,  // Ngày QĐ (QĐ thành lập hội đồng đánh giá luận án)
            'K' => 13,  // Ngày bảo vệ
            'L' => 18,  // Số hiệu văn bằng
            'M' => 13,  // Số vào sổ
            'N' => 9,   // Khoá
            'O' => 20,  // Hình thức
            'P' => 26,  // Số quyết định (QĐ công nhận học vị)
            'Q' => 13,  // Ngày tháng (QĐ công nhận học vị)
            'R' => 13,  // Ngày cấp
            'S' => 13,  // Tình trạng
            'T' => 30,  // Nội dung điều chỉnh (Điều chỉnh thông tin)
            'U' => 18,  // QĐ điều chỉnh thông tin (Điều chỉnh thông tin)
            'V' => 13,  // Ngày QĐ (Điều chỉnh thông tin)
            'W' => 25,  // Số hiệu văn bằng (Cấp lại văn bằng)
            'X' => 30,  // Nội dung chỉnh sửa (Cấp lại văn bằng)
            'Y' => 26,  // QĐ thu hồi, hủy bỏ và cấp lại (Cấp lại văn bằng)
            'Z' => 13,  // Ngày QĐ (Cấp lại văn bằng)
            'AA' => 30,  // Ghi chú
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

        \Log::info('DoctorateInfoExport: File saved to ' . $outputPath);

        return $outputPath;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename()
    {
        return 'Thong_tin_cap_bang_tien_si_' . date('Y-m-d_His') . '.xlsx';
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
