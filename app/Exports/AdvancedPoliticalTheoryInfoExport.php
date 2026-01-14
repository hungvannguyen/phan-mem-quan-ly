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

        // Query students with certificate degrees (Cao cấp lý luận chính trị)
        $query = Student::with([
                'major',
                'degrees.major',
                'degrees.diplomaBlank.type',
                'degrees.changeLogs',
                'degrees.reissues.newDiplomaBlank'
            ])
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

        \Log::info('AdvancedPoliticalTheoryInfoExport: Query returned ' . $students->count() . ' students with advanced political theory certificates');

        // Check if there's any data to export
        if ($students->count() === 0) {
            \Log::warning('AdvancedPoliticalTheoryInfoExport: No data found - throwing exception');
            throw new \Exception('Không có dữ liệu chứng chỉ cao cấp lý luận chính trị để xuất');
        }

        // Step 1: Load file mẫu
        $templatePath = resource_path('templates/[Mau TT04] Thong tin cap bang cao cap LLCT.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $templatePath);
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Step 2: Xác định dòng bắt đầu (hardcoded = 5)
        $startRow = 5;
        $totalStudents = $students->count();

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

            // Get latest change log for this degree (from eager loaded collection)
            // Chỉ lấy logs có action_type là update/updated, không lấy create/created
            $latestChangeLog = $degree->changeLogs
                ->whereIn('action_type', ['update', 'updated'])
                ->sortByDesc('created_at')
                ->first();

            // Get latest reissue for this degree (from eager loaded collection)
            $latestReissue = $degree->reissues->sortByDesc('decision_date')->first();

            // Ghi data vào các cột A-X theo mapping
            $sheet->setCellValue('A' . $currentRow, $stt); // STT
            $sheet->setCellValue('B' . $currentRow, $student->full_name ?? ''); // Họ và tên
            $sheet->setCellValue('C' . $currentRow, $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : ''); // Ngày sinh
            $sheet->setCellValue('D' . $currentRow, $student->place_of_birth ?? ''); // Nơi sinh
            $sheet->setCellValue('E' . $currentRow, $this->getGenderLabel($student->gender)); // Giới tính
            $sheet->setCellValue('F' . $currentRow, $student->nation ?? ''); // Dân tộc
            $sheet->setCellValue('G' . $currentRow, $student->training_type ?? 'Chính quy'); // Loại hình đào tạo
            $sheet->setCellValue('H' . $currentRow, $student->course ?? ''); // Khóa
            $sheet->setCellValue('I' . $currentRow, $degree->ranking ?? ''); // Xếp loại tốt nghiệp
            $sheet->setCellValue('J' . $currentRow, $degree->diplomaBlank?->serial_number ?? ''); // Số hiệu văn bằng (serial number)
            $sheet->setCellValue('K' . $currentRow, $student->number_in_the_book ?? ''); // Số vào sổ gốc cấp văn bằng
            $sheet->setCellValue('L' . $currentRow, $student->academic_year ?? ''); // Khóa học
            $sheet->setCellValue('M' . $currentRow, $degree->decision_number ?? ''); // Số Quyết định
            $sheet->setCellValue('N' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày tháng
            $sheet->setCellValue('O' . $currentRow, $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''); // Ngày cấp
            $sheet->setCellValue('P' . $currentRow, 'Đã cấp'); // Tình trạng

            // Điều chỉnh thông tin (Change Logs) - chỉ ghi nếu có dữ liệu và có changed_field
            if ($latestChangeLog && $latestChangeLog->changed_field) {
                $sheet->setCellValue('Q' . $currentRow, $latestChangeLog->change_description ?? ''); // Nội dung điều chỉnh
                $sheet->setCellValue('R' . $currentRow, $latestChangeLog->decision_number ?? ''); // QĐ điều chỉnh thông tin
                $sheet->setCellValue('S' . $currentRow, $latestChangeLog->decision_date ? $latestChangeLog->decision_date->format('d/m/Y') : ''); // Ngày QĐ
            }

            // Cấp lại văn bằng (Reissues) - chỉ ghi nếu có dữ liệu
            if ($latestReissue) {
                $sheet->setCellValue('T' . $currentRow, $latestReissue->newDiplomaBlank?->serial_number ?? ''); // Số hiệu văn bằng mới
                $sheet->setCellValue('U' . $currentRow, $latestReissue->edit_content ?? ''); // Nội dung chỉnh sửa
                $sheet->setCellValue('V' . $currentRow, $latestReissue->recall_decision ?? ''); // QĐ thu hồi, hủy bỏ và cấp lại
                $sheet->setCellValue('W' . $currentRow, $latestReissue->decision_date ? $latestReissue->decision_date->format('d/m/Y') : ''); // Ngày QĐ
                $sheet->setCellValue('X' . $currentRow, $latestReissue->notes ?? ''); // Ghi chú
            }

            $stt++;
            $currentRow++;
        }

        \Log::info('AdvancedPoliticalTheoryInfoExport: Processed ' . ($stt - 1) . ' students');

        // Step 5: Footer được tự động đẩy xuống khi insert rows

        // Set fixed column widths instead of AutoSize (much faster)
        $columnWidths = [
            'A' => 6,   // STT
            'B' => 30,  // Họ và tên
            'C' => 13,  // Ngày sinh
            'D' => 25,  // Nơi sinh
            'E' => 11,  // Giới tính
            'F' => 13,  // Dân tộc
            'G' => 20,  // Loại hình đào tạo
            'H' => 9,   // Khóa
            'I' => 16,  // Xếp loại
            'J' => 18,  // Số hiệu văn bằng
            'K' => 13,  // Số vào sổ
            'L' => 13,  // Khóa học
            'M' => 26,  // Số quyết định (QĐ công nhận tốt nghiệp)
            'N' => 13,  // Ngày tháng (QĐ công nhận tốt nghiệp)
            'O' => 13,  // Ngày cấp
            'P' => 13,  // Tình trạng
            'Q' => 30,  // Nội dung điều chỉnh (Điều chỉnh thông tin)
            'R' => 26,  // QĐ điều chỉnh thông tin (Điều chỉnh thông tin)
            'S' => 13,  // Ngày QĐ (Điều chỉnh thông tin)
            'T' => 25,  // Số hiệu văn bằng (Cấp lại văn bằng)
            'U' => 30,  // Nội dung chỉnh sửa (Cấp lại văn bằng)
            'V' => 26,  // QĐ thu hồi, hủy bỏ và cấp lại (Cấp lại văn bằng)
            'W' => 13,  // Ngày QĐ (Cấp lại văn bằng)
            'X' => 30,  // Ghi chú
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
