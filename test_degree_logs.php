#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Degree;
use App\Models\ChangeLog;

echo "\n=== KIỂM TRA DEGREE LOGS ===\n\n";

// Kiểm tra tổng logs Degree trong DB
$totalDegreeLogs = ChangeLog::where('entity_type', 'Degree')->count();
echo "Tổng số Degree logs trong DB: {$totalDegreeLogs}\n\n";

if ($totalDegreeLogs == 0) {
    echo "❌ KHÔNG CÓ DEGREE LOGS!\n";
    echo "Seeder có thể chưa tạo hoặc bị lỗi.\n\n";
} else {
    echo "✓ Có {$totalDegreeLogs} degree logs\n";

    // Xem mẫu logs
    echo "\n=== MẪU DEGREE LOGS ===\n";
    $sampleLogs = ChangeLog::where('entity_type', 'Degree')
        ->with('changedBy')
        ->latest()
        ->take(5)
        ->get();

    foreach ($sampleLogs as $log) {
        echo "- Entity ID: {$log->entity_id}\n";
        echo "  Description: {$log->change_description}\n";
        echo "  Field: {$log->changed_field}\n";
        echo "  Decision: {$log->decision_number}\n";
        echo "  Date: {$log->created_at->format('Y-m-d H:i')}\n\n";
    }
}

// Test với degree cụ thể
$degree = Degree::first();
if (!$degree) {
    echo "❌ Không tìm thấy degree\n";
    exit(1);
}

echo "=== TEST VỚI DEGREE ===\n";
echo "Degree ID: {$degree->degree_id}\n";
echo "Registration: {$degree->registration_number}\n";
echo "Primary Key Name: {$degree->getKeyName()}\n";
echo "Primary Key Value: {$degree->getKey()}\n\n";

// Kiểm tra logs trong DB cho degree này
echo "Logs trong DB cho degree này:\n";
$logsInDb = ChangeLog::where('entity_type', 'Degree')
    ->where('entity_id', $degree->degree_id)
    ->get();
echo "Số lượng: {$logsInDb->count()}\n";

foreach ($logsInDb as $log) {
    echo "  - {$log->change_description}\n";
}

// Kiểm tra qua relationship
echo "\nLogs qua relationship:\n";
$logsViaRelation = $degree->adjustments;
echo "Số lượng: {$logsViaRelation->count()}\n";

foreach ($logsViaRelation as $log) {
    echo "  - {$log->change_description}\n";
}

if ($logsInDb->count() > 0 && $logsViaRelation->count() == 0) {
    echo "\n❌ LỖI: Có logs trong DB nhưng relationship không lấy được!\n";
    echo "   Kiểm tra lại relationship trong Degree model.\n";
} elseif ($logsViaRelation->count() > 0) {
    echo "\n✅ THÀNH CÔNG: Relationship hoạt động đúng!\n";
}

echo "\n=== KẾT THÚC ===\n\n";
