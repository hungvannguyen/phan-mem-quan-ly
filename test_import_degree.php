<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Tạo file Excel test với 2 rows dữ liệu
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Headers (Row 1)
$headers = [
    'STT', 'Loại Văn bằng', 'Họ và tên', 'Ngày Sinh', 'Nơi Sinh',
    'Quê quán', 'Nguyên quán', 'Giới tính', 'Dân tộc', 'Quốc tịch',
    'Khoá', 'Lớp', 'Niên Khoá', 'Ngành đào tạo', 'Hình thức đào tạo',
    'Số QĐ (Hội đồng)', 'Ngày Tháng (Hội đồng)', 'Ngày Bảo vệ',
    'Số QĐ (Công nhận)', 'Ngày Tháng (Công nhận)', 'Năm tốt nghiệp',
    'Xếp Loại tốt nghiệp', 'Số hiệu văn bằng', 'SỐ vào sổ gốc',
    'Ngày cấp', 'Nội Dung điều chỉnh', 'QĐ điều chỉnh', 'Ngày QĐ điều chỉnh',
    'Số hiệu văn bằng (Cấp lại)', 'Nội dung chỉnh sửa (Cấp lại)',
    'QĐ thu hồi (Cấp lại)', 'Ngày QĐ (Cấp lại)', 'Ghi chú'
];

$sheet->fromArray($headers, null, 'A1');

// Row 2 - Test data 1 (Cử nhân)
$row2 = [
    1, // STT
    'Cử nhân', // Loại văn bằng
    'Nguyễn Văn Test', // Họ tên
    '01/01/2000', // Ngày sinh
    'Hà Nội', // Nơi sinh
    'Hà Nội', // Quê quán
    'Hà Nội', // Nguyên quán
    'Nam', // Giới tính
    'Kinh', // Dân tộc
    'Việt Nam', // Quốc tịch
    'K44', // Khoá
    'CNTT-01', // Lớp
    '2018-2022', // Niên khoá
    'Công nghệ thông tin', // Ngành
    'Chính quy', // Hình thức
    'QĐ001', // Số QĐ hội đồng
    '15/05/2022', // Ngày QĐ hội đồng
    '20/05/2022', // Ngày bảo vệ
    'QĐ002', // Số QĐ công nhận
    '25/05/2022', // Ngày QĐ công nhận
    '2022', // Năm tốt nghiệp
    'Khá', // Xếp loại
    'TEST001', // Số hiệu văn bằng
    'VS001', // Số vào sổ
    '01/06/2022', // Ngày cấp
    '', // Nội dung điều chỉnh
    '', // QĐ điều chỉnh
    '', // Ngày QĐ điều chỉnh
    '', // Số hiệu cấp lại
    '', // Nội dung cấp lại
    '', // QĐ cấp lại
    '', // Ngày QĐ cấp lại
    'Test import' // Ghi chú
];

$sheet->fromArray($row2, null, 'A2');

// Row 3 - Test data 2 (Thạc sĩ)
$row3 = [
    2, // STT
    'Thạc sĩ', // Loại văn bằng
    'Trần Thị Test', // Họ tên
    '15/03/1998', // Ngày sinh
    'Đà Nẵng', // Nơi sinh
    'Đà Nẵng', // Quê quán
    'Quảng Nam', // Nguyên quán
    'Nữ', // Giới tính
    'Kinh', // Dân tộc
    'Việt Nam', // Quốc tịch
    'K42', // Khoá
    'CNTT-CK-01', // Lớp
    '2020-2022', // Niên khoá
    'Khoa học máy tính', // Ngành
    'Chính quy', // Hình thức
    'QĐ003', // Số QĐ hội đồng
    '10/07/2022', // Ngày QĐ hội đồng
    '15/07/2022', // Ngày bảo vệ
    'QĐ004', // Số QĐ công nhận
    '20/07/2022', // Ngày QĐ công nhận
    '2022', // Năm tốt nghiệp
    'Giỏi', // Xếp loại
    'TEST002', // Số hiệu văn bằng
    'VS002', // Số vào sổ
    '01/08/2022', // Ngày cấp
    '', // Nội dung điều chỉnh
    '', // QĐ điều chỉnh
    '', // Ngày QĐ điều chỉnh
    '', // Số hiệu cấp lại
    '', // Nội dung cấp lại
    '', // QĐ cấp lại
    '', // Ngày QĐ cấp lại
    'Test import master' // Ghi chú
];

$sheet->fromArray($row3, null, 'A3');

// Auto-size columns
foreach (range('A', 'AG') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$filePath = __DIR__ . '/storage/app/test_degree_import.xls';
$writer->save($filePath);

echo "✓ Created test file: {$filePath}\n";
echo "File contains 2 test records:\n";
echo "  1. Nguyễn Văn Test - Cử nhân CNTT\n";
echo "  2. Trần Thị Test - Thạc sĩ KHMT\n";
