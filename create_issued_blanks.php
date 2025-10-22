<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DiplomaBlank;
use App\Models\Degree;
use App\Models\Student;
use App\Models\Major;
use App\Enums\DiplomaBlankStatus;

// Lấy một số phôi trong kho để chuyển thành đã cấp
$inStockBlanks = DiplomaBlank::where('status', DiplomaBlankStatus::IN_STOCK)
    ->take(5)
    ->get();

if ($inStockBlanks->isEmpty()) {
    echo "Không có phôi trong kho để chuyển thành đã cấp.\n";
    exit;
}

echo "Tạo " . $inStockBlanks->count() . " phôi đã cấp để test thu hồi...\n";

foreach ($inStockBlanks as $index => $blank) {
    // Cập nhật trạng thái thành đã cấp
    $blank->update([
        'status' => DiplomaBlankStatus::ISSUED,
        'issue_date' => now()->subDays(rand(1, 30)),
        'issue_reason' => 'Cấp cho sinh viên tốt nghiệp'
    ]);

    echo "✓ Cập nhật phôi {$blank->serial_number} thành đã cấp\n";
}

echo "\nHoàn thành! Các phôi đã cấp:\n";
$issuedBlanks = DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED)->get();
foreach ($issuedBlanks as $blank) {
    echo "- {$blank->serial_number} (cấp ngày: {$blank->issue_date->format('d/m/Y')})\n";
}

echo "\nTổng kết:\n";
echo "- Tổng phôi: " . DiplomaBlank::count() . "\n";
echo "- Phôi trong kho: " . DiplomaBlank::where('status', DiplomaBlankStatus::IN_STOCK)->count() . "\n";
echo "- Phôi đã cấp: " . DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED)->count() . "\n";
echo "- Phôi đã thu hồi: " . DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)->count() . "\n";
echo "- Phôi hư hỏng: " . DiplomaBlank::where('status', DiplomaBlankStatus::DAMAGED)->count() . "\n";
