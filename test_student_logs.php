#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\ChangeLog;

// Lấy student đầu tiên
$student = Student::first();

if (!$student) {
    echo "Không tìm thấy student\n";
    exit(1);
}

echo "Student: {$student->full_name} (ID: {$student->student_id})\n";
echo "Primary Key: {$student->getKeyName()} = {$student->getKey()}\n";
echo "Class basename: " . class_basename($student) . "\n\n";

// Kiểm tra logs trong DB
echo "=== Logs trong DB cho student này ===\n";
$logsInDb = ChangeLog::where('entity_type', 'Student')
    ->where('entity_id', $student->student_id)
    ->get();

echo "Số logs trong DB: {$logsInDb->count()}\n";
foreach ($logsInDb as $log) {
    echo "  - {$log->change_description} (field: {$log->changed_field})\n";
}

// Kiểm tra relationship
echo "\n=== Logs qua relationship ===\n";
$logsViaRelation = $student->changeLogs;
echo "Số logs qua relation: {$logsViaRelation->count()}\n";
foreach ($logsViaRelation as $log) {
    echo "  - {$log->change_description} (field: {$log->changed_field})\n";
}

// Test tạo log mới
echo "\n=== Test tạo log mới ===\n";
ChangeLog::create([
    'entity_type' => 'Student',
    'entity_id' => $student->student_id,
    'changed_field' => 'test_field',
    'old_value' => 'old',
    'new_value' => 'new',
    'change_description' => 'Test log tạo bằng tay',
    'action_type' => 'update',
]);

echo "✓ Đã tạo test log\n";

// Kiểm tra lại
$logsViaRelation = $student->changeLogs()->get();
echo "Số logs sau khi tạo: {$logsViaRelation->count()}\n";

// Xóa test log
ChangeLog::where('entity_type', 'Student')
    ->where('entity_id', $student->student_id)
    ->where('changed_field', 'test_field')
    ->delete();

echo "✓ Đã xóa test log\n";
