<?php

namespace App\Exports;

use App\Models\Degree;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DiplomaStatisticsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $filters;
    protected $statisticsType;

    public function __construct($filters, $statisticsType = 'all')
    {
        $this->filters = $filters;
        $this->statisticsType = $statisticsType;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Degree::query()
            ->with(['student', 'major', 'diplomaBlank.type'])
            ->whereNotNull('diploma_blank_id'); // Chỉ lấy bằng đã cấp

        // Apply filters
        if (!empty($this->filters['graduation_year'])) {
            $query->where('graduation_year', $this->filters['graduation_year']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('granting_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('granting_date', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['degree_type'])) {
            $query->where('degree_type', $this->filters['degree_type']);
        }

        if (!empty($this->filters['major_id'])) {
            $query->where('major_id', $this->filters['major_id']);
        }

        if (!empty($this->filters['gender'])) {
            $query->whereHas('student', function ($q) {
                $q->where('gender', $this->filters['gender']);
            });
        }

        if (!empty($this->filters['ranking'])) {
            $query->where('ranking', $this->filters['ranking']);
        }

        if (!empty($this->filters['training_type'])) {
            $query->whereHas('student', function ($q) {
                $q->where('training_type', $this->filters['training_type']);
            });
        }

        return $query->orderBy('granting_date', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã sinh viên',
            'Họ và tên',
            'Ngày sinh',
            'Giới tính',
            'Loại bằng',
            'Số hiệu bằng',
            'Số vào sổ',
            'Ngành học',
            'Mã ngành',
            'Khóa học',
            'Ngày cấp',
            'Xếp loại',
            'Hình thức đào tạo',
            'Số quyết định',
        ];
    }

    /**
     * @var Degree $degree
     */
    public function map($degree): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $degree->student->student_code ?? '',
            $degree->student->full_name ?? '',
            $degree->student->date_of_birth ? $degree->student->date_of_birth->format('d/m/Y') : '',
            $this->getGenderLabel($degree->student->gender ?? ''),
            $this->getDegreeTypeLabel($degree->degree_type),
            $degree->diplomaBlank->serial_number ?? '',
            $degree->registration_number ?? '',
            $degree->major->major_name ?? $degree->major_name ?? '',
            $degree->major->major_code ?? '',
            $degree->graduation_year ?? '',
            $degree->granting_date ? $degree->granting_date->format('d/m/Y') : '',
            $degree->ranking ?? '',
            $degree->student->training_type ?? '',
            $degree->decision_number ?? '',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Thống kê văn bằng';
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Set font color for header
        $sheet->getStyle('A1:O1')->getFont()->getColor()->setRGB('FFFFFF');

        // Auto-size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Apply borders to all data
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle('A1:O' . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            // Center align specific columns
            $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D2:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E2:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F2:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K2:K' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L2:L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }

    /**
     * Get Vietnamese label for degree type
     */
    private function getDegreeTypeLabel($type)
    {
        return match ($type) {
            'bachelor' => 'Cử nhân',
            'master' => 'Thạc sĩ',
            'doctor' => 'Tiến sĩ',
            'certificate' => 'Chứng chỉ',
            default => $type
        };
    }

    /**
     * Get Vietnamese label for gender
     */
    private function getGenderLabel($gender)
    {
        if (is_object($gender)) {
            return $gender->value === 'Male' ? 'Nam' : 'Nữ';
        }
        return match ($gender) {
            'Male', 'male', 'nam' => 'Nam',
            'Female', 'female', 'nữ' => 'Nữ',
            default => $gender
        };
    }
}
