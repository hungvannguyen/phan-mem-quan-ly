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

class CertificateStatisticsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Degree::query()
            ->with(['student', 'major'])
            ->where('degree_type', 'certificate')
            ->whereNotNull('diploma_blank_id'); // Chỉ lấy chứng chỉ đã cấp

        // Apply filters
        if (!empty($this->filters['certificate_type'])) {
            $query->where('notes', 'like', '%' . $this->filters['certificate_type'] . '%');
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('granting_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('granting_date', '<=', $this->filters['end_date']);
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
            'Loại chứng chỉ',
            'Số hiệu chứng chỉ',
            'Số vào sổ',
            'Ngày cấp',
            'Số quyết định',
            'Ghi chú',
        ];
    }

    /**
     * @var Degree $certificate
     */
    public function map($certificate): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $certificate->student->student_code ?? '',
            $certificate->student->full_name ?? '',
            $certificate->student->date_of_birth ? $certificate->student->date_of_birth->format('d/m/Y') : '',
            $this->getGenderLabel($certificate->student->gender ?? ''),
            $this->getCertificateType($certificate->notes ?? ''),
            $certificate->diplomaBlank->serial_number ?? '',
            $certificate->registration_number ?? '',
            $certificate->granting_date ? $certificate->granting_date->format('d/m/Y') : '',
            $certificate->decision_number ?? '',
            $certificate->notes ?? '',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Thống kê chứng chỉ';
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47'],
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
        $sheet->getStyle('A1:K1')->getFont()->getColor()->setRGB('FFFFFF');

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Apply borders to all data
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle('A1:K' . $highestRow)->applyFromArray([
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
            $sheet->getStyle('I2:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
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

    /**
     * Get certificate type from notes
     */
    private function getCertificateType($notes)
    {
        $notes = strtolower($notes);
        if (str_contains($notes, 'ngoại ngữ') || str_contains($notes, 'tiếng')) {
            return 'Chứng chỉ ngoại ngữ';
        }
        if (str_contains($notes, 'tin học') || str_contains($notes, 'cntt')) {
            return 'Chứng chỉ tin học';
        }
        if (str_contains($notes, 'nghề')) {
            return 'Chứng chỉ nghề';
        }
        return 'Chứng chỉ khác';
    }
}
