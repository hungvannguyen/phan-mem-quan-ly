<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// 1. Template cho Bằng Cử nhân, Thạc sĩ, Tiến sĩ
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Degree Import');

// Header
$headers = [
    'A1' => 'ho_ten',
    'B1' => 'ma_sinh_vien',
    'C1' => 'ngay_sinh',
    'D1' => 'noi_sinh',
    'E1' => 'gioi_tinh',
    'F1' => 'nganh',
    'G1' => 'loai_bang',
    'H1' => 'so_bang',
    'I1' => 'so_vao_so',
    'J1' => 'ngay_cap',
    'K1' => 'xep_loai',
    'L1' => 'hinh_thuc_dao_tao',
    'M1' => 'ghi_chu',
];

foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
    $sheet->getStyle($cell)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF4472C4');
    $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Row mô tả
$descriptions = [
    'A2' => 'Nguyễn Văn A',
    'B2' => 'SV001',
    'C2' => '01/01/2000',
    'D2' => 'Hà Nội',
    'E2' => 'Nam',
    'F2' => 'Công nghệ thông tin',
    'G2' => 'bachelor',
    'H2' => 'B001',
    'I2' => 'VS001',
    'J2' => '20/06/2024',
    'K2' => 'Giỏi',
    'L2' => 'Chính quy',
    'M2' => '',
];

foreach ($descriptions as $cell => $value) {
    $sheet->setCellValue($cell, $value);
    $sheet->getStyle($cell)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE7E6E6');
}

// Auto width
foreach (range('A', 'M') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$writer->save(__DIR__ . '/storage/app/templates/degree_import_template.xlsx');
echo "✓ Created: degree_import_template.xlsx\n";

// 2. Template cho Lý luận chính trị
$spreadsheet2 = new Spreadsheet();
$sheet2 = $spreadsheet2->getActiveSheet();
$sheet2->setTitle('Political Theory Import');

$headers2 = [
    'A1' => 'ho_ten',
    'B1' => 'ma_sinh_vien',
    'C1' => 'ngay_sinh',
    'D1' => 'chung_chi_so',
    'E1' => 'loai_chung_chi',
    'F1' => 'ngay_cap',
    'G1' => 'noi_cap',
    'H1' => 'ghi_chu',
];

foreach ($headers2 as $cell => $value) {
    $sheet2->setCellValue($cell, $value);
    $sheet2->getStyle($cell)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF70AD47');
    $sheet2->getStyle($cell)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet2->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

$descriptions2 = [
    'A2' => 'Trần Thị B',
    'B2' => 'SV002',
    'C2' => '15/05/1999',
    'D2' => 'LLCT001',
    'E2' => 'Cao cấp',
    'F2' => '10/07/2024',
    'G2' => 'Trường ABC',
    'H2' => '',
];

foreach ($descriptions2 as $cell => $value) {
    $sheet2->setCellValue($cell, $value);
    $sheet2->getStyle($cell)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE7E6E6');
}

foreach (range('A', 'H') as $col) {
    $sheet2->getColumnDimension($col)->setAutoSize(true);
}

$writer2 = new Xlsx($spreadsheet2);
$writer2->save(__DIR__ . '/storage/app/templates/political_theory_import_template.xlsx');
echo "✓ Created: political_theory_import_template.xlsx\n";

// 3. Template cho Chứng chỉ
$spreadsheet3 = new Spreadsheet();
$sheet3 = $spreadsheet3->getActiveSheet();
$sheet3->setTitle('Certificate Import');

$headers3 = [
    'A1' => 'ho_ten',
    'B1' => 'ma_sinh_vien',
    'C1' => 'ngay_sinh',
    'D1' => 'chung_chi_so',
    'E1' => 'ten_chung_chi',
    'F1' => 'ngay_cap',
    'G1' => 'noi_cap',
    'H1' => 'co_quan_cap',
    'I1' => 'ghi_chu',
];

foreach ($headers3 as $cell => $value) {
    $sheet3->setCellValue($cell, $value);
    $sheet3->getStyle($cell)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFC000');
    $sheet3->getStyle($cell)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet3->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

$descriptions3 = [
    'A2' => 'Lê Văn C',
    'B2' => 'SV003',
    'C2' => '20/08/1998',
    'D2' => 'CC001',
    'E2' => 'Chứng chỉ Tin học',
    'F2' => '15/09/2024',
    'G2' => 'Trường XYZ',
    'H2' => 'Bộ GD&ĐT',
    'I2' => '',
];

foreach ($descriptions3 as $cell => $value) {
    $sheet3->setCellValue($cell, $value);
    $sheet3->getStyle($cell)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE7E6E6');
}

foreach (range('A', 'I') as $col) {
    $sheet3->getColumnDimension($col)->setAutoSize(true);
}

$writer3 = new Xlsx($spreadsheet3);
$writer3->save(__DIR__ . '/storage/app/templates/certificate_import_template.xlsx');
echo "✓ Created: certificate_import_template.xlsx\n";

echo "\n✓ All template files created successfully!\n";
