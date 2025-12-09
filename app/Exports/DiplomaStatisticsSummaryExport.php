<?php

namespace App\Exports;

use App\Models\Degree;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class DiplomaStatisticsSummaryExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $filters;
    protected $groupBy;

    public function __construct($filters, $groupBy = 'degree_type')
    {
        $this->filters = $filters;
        $this->groupBy = $groupBy;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Degree::query()
            ->whereNotNull('diploma_blank_id');

        // Apply filters
        $this->applyFilters($query);

        // Group by the specified field
        $data = collect();

        switch ($this->groupBy) {
            case 'graduation_year':
                $results = $query->select('graduation_year', DB::raw('count(*) as total'))
                    ->groupBy('graduation_year')
                    ->orderBy('graduation_year', 'desc')
                    ->get();

                foreach ($results as $result) {
                    $data->push([
                        'label' => 'Khóa ' . $result->graduation_year,
                        'total' => $result->total,
                    ]);
                }
                break;

            case 'degree_type':
                $results = $query->select('degree_type', DB::raw('count(*) as total'))
                    ->groupBy('degree_type')
                    ->get();

                foreach ($results as $result) {
                    $data->push([
                        'label' => $this->getDegreeTypeLabel($result->degree_type),
                        'total' => $result->total,
                    ]);
                }
                break;

            case 'major':
                $results = $query->join('majors', 'majors.major_id', '=', 'degrees.major_id')
                    ->select('majors.major_name', 'majors.major_code', DB::raw('count(*) as total'))
                    ->groupBy('majors.major_name', 'majors.major_code')
                    ->orderBy('total', 'desc')
                    ->get();

                foreach ($results as $result) {
                    $data->push([
                        'label' => $result->major_name . ' (' . $result->major_code . ')',
                        'total' => $result->total,
                    ]);
                }
                break;

            case 'gender':
                $results = $query->join('students', 'students.student_id', '=', 'degrees.student_id')
                    ->select('students.gender', DB::raw('count(*) as total'))
                    ->groupBy('students.gender')
                    ->get();

                foreach ($results as $result) {
                    $data->push([
                        'label' => $this->getGenderLabel($result->gender),
                        'total' => $result->total,
                    ]);
                }
                break;

            case 'ranking':
                $results = $query->select('ranking', DB::raw('count(*) as total'))
                    ->whereNotNull('ranking')
                    ->groupBy('ranking')
                    ->orderBy('total', 'desc')
                    ->get();

                foreach ($results as $result) {
                    $data->push([
                        'label' => $result->ranking,
                        'total' => $result->total,
                    ]);
                }
                break;

            case 'training_type':
                $results = $query->join('students', 'students.student_id', '=', 'degrees.student_id')
                    ->select('students.training_type', DB::raw('count(*) as total'))
                    ->whereNotNull('students.training_type')
                    ->groupBy('students.training_type')
                    ->orderBy('total', 'desc')
                    ->get();

                foreach ($results as $result) {
                    $data->push([
                        'label' => $result->training_type,
                        'total' => $result->total,
                    ]);
                }
                break;
        }

        // Add total row
        $grandTotal = $data->sum('total');
        $data->push([
            'label' => 'TỔNG CỘNG',
            'total' => $grandTotal,
        ]);

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $groupLabel = $this->getGroupLabel();
        return [$groupLabel, 'Số lượng'];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Tổng hợp thống kê';
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
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

        $sheet->getStyle('A1:B1')->getFont()->getColor()->setRGB('FFFFFF');

        // Auto-size columns
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(15);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    /**
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Apply borders to all data
                $sheet->getStyle('A1:B' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Center align count column
                $sheet->getStyle('B2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Style for total row
                if ($highestRow > 1) {
                    $sheet->getStyle('A' . $highestRow . ':B' . $highestRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFE699'],
                        ],
                    ]);
                }
            },
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query)
    {
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
    }

    /**
     * Get group label
     */
    private function getGroupLabel()
    {
        return match ($this->groupBy) {
            'graduation_year' => 'Khóa học',
            'degree_type' => 'Loại bằng',
            'major' => 'Ngành học',
            'gender' => 'Giới tính',
            'ranking' => 'Xếp loại',
            'training_type' => 'Hình thức đào tạo',
            default => 'Phân loại'
        };
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
            'Male', 'male' => 'Nam',
            'Female', 'female' => 'Nữ',
            default => $gender
        };
    }
}