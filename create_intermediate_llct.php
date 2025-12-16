<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DiplomaBlankType;
use App\Models\DiplomaBlank;
use App\Models\Student;
use App\Models\Degree;
use App\Enums\DiplomaBlankStatus;

// Tìm loại bằng Trung cấp LLCT
$type = DiplomaBlankType::where('type_name', 'LIKE', '%Trung cấp%')
    ->where('type_name', 'LIKE', '%lý luận chính trị%')
    ->first();

if (!$type) {
    echo 'Tạo loại bằng Trung cấp LLCT mới...' . PHP_EOL;
    $type = DiplomaBlankType::create([
        'type_name' => 'Bằng Trung cấp lý luận chính trị',
        'description' => 'Bằng trung cấp lý luận chính trị',
        'degree_type' => 'certificate'
    ]);
}

echo 'Loại bằng: ' . $type->type_name . ' (ID: ' . $type->type_id . ')' . PHP_EOL;

// Tạo 5 phôi bằng Trung cấp LLCT
$blanks = [];
for ($i = 1; $i <= 5; $i++) {
    $blankNumber = 'TC' . date('Y') . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
    $blank = DiplomaBlank::create([
        'serial_number' => $blankNumber,
        'type_id' => $type->type_id,
        'import_date' => now(),
        'status' => $i <= 3 ? DiplomaBlankStatus::ISSUED : DiplomaBlankStatus::IN_STOCK,
    ]);
    $blanks[] = $blank;
    echo 'Tạo phôi: ' . $blank->blank_number . PHP_EOL;
}

// Lấy 3 sinh viên ngẫu nhiên để gán bằng
$students = Student::inRandomOrder()->limit(3)->get();

foreach ($students as $index => $student) {
    $regNumber = 'TCLLCT' . date('Y') . str_pad($index + 1, 7, '0', STR_PAD_LEFT);

    $grantingDate = now()->subDays(rand(30, 365));
    $degree = Degree::create([
        'student_id' => $student->student_id,
        'major_id' => $student->major_id,
        'registration_number' => $regNumber,
        'diploma_blank_id' => $blanks[$index]->diploma_blank_id,
        'granting_date' => $grantingDate,
        'graduation_year' => $grantingDate->format('Y'),
        'decision_number' => 'QĐ-TCLLCT-' . rand(100, 999) . '/' . date('Y'),
        'ranking' => ['Xuất sắc', 'Giỏi', 'Khá'][array_rand(['Xuất sắc', 'Giỏi', 'Khá'])],
        'degree_type' => 'certificate',
    ]);

    echo 'Tạo bằng ' . $regNumber . ' cho sinh viên: ' . $student->full_name . PHP_EOL;
}

echo 'Hoàn thành! Đã tạo 3 bằng Trung cấp LLCT.' . PHP_EOL;
