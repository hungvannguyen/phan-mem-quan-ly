<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ChangeLog;
use App\Models\Student;
use App\Models\Degree;
use App\Models\User;

echo "\n=== KIỂM TRA CHANGE LOGS DATABASE ===\n\n";

// 1. Kiểm tra tổng số logs
$totalLogs = ChangeLog::count();
$degreeLogs = ChangeLog::where('entity_type', 'Degree')->count();
$studentLogs = ChangeLog::where('entity_type', 'Student')->count();

echo "✓ Tổng số change logs: {$totalLogs}\n";
echo "  - Degree logs: {$degreeLogs}\n";
echo "  - Student logs: {$studentLogs}\n\n";

if ($totalLogs == 0) {
    echo "❌ CẢNH BÁO: Không có logs nào trong database!\n";
    echo "   Hãy chạy: ./vendor/bin/sail artisan migrate:fresh --seed\n\n";
    exit(1);
}

// 2. Xem một vài logs mẫu
echo "=== MẪU LOGS DEGREE ===\n";
$sampleDegreeLogs = ChangeLog::where('entity_type', 'Degree')
    ->with('changedBy')
    ->latest()
    ->take(3)
    ->get();

foreach ($sampleDegreeLogs as $log) {
    echo "- [{$log->created_at->format('Y-m-d H:i')}] {$log->change_description}\n";
    echo "  Field: {$log->changed_field}, Action: {$log->action_type}\n";
    if ($log->changedBy) {
        echo "  By: {$log->changedBy->full_name}\n";
    }
    echo "\n";
}

echo "=== MẪU LOGS STUDENT ===\n";
$sampleStudentLogs = ChangeLog::where('entity_type', 'Student')
    ->with('changedBy')
    ->latest()
    ->take(3)
    ->get();

foreach ($sampleStudentLogs as $log) {
    echo "- [{$log->created_at->format('Y-m-d H:i')}] {$log->change_description}\n";
    if ($log->changed_field) {
        echo "  Field: {$log->changed_field}";
        if ($log->old_value && $log->new_value) {
            echo " ({$log->old_value} → {$log->new_value})";
        }
        echo "\n";
    }
    echo "  Action: {$log->action_type}\n";
    if ($log->changedBy) {
        echo "  By: {$log->changedBy->full_name}\n";
    }
    echo "\n";
}

echo "\n=== TEST UPDATE STUDENT ===\n";

$student = Student::first();
if ($student) {
    echo "Test student: {$student->full_name} (ID: {$student->student_id})\n";

    $logsBefore = ChangeLog::where('entity_type', 'Student')
        ->where('entity_id', $student->student_id)
        ->count();

    echo "Logs trước update: {$logsBefore}\n";

    // Update student
    $oldClass = $student->class_name;
    $student->class_name = $oldClass . ' [TEST]';
    $student->save();

    $logsAfter = ChangeLog::where('entity_type', 'Student')
        ->where('entity_id', $student->student_id)
        ->count();

    echo "Logs sau update: {$logsAfter}\n";

    if ($logsAfter > $logsBefore) {
        echo "✅ THÀNH CÔNG: Log đã được tạo khi update student!\n";

        $newLog = ChangeLog::where('entity_type', 'Student')
            ->where('entity_id', $student->student_id)
            ->latest()
            ->first();

        if ($newLog) {
            echo "   - Description: {$newLog->change_description}\n";
            echo "   - Field: {$newLog->changed_field}\n";
            echo "   - Old → New: {$newLog->old_value} → {$newLog->new_value}\n";
        }
    } else {
        echo "❌ LỖI: Log KHÔNG được tạo khi update student!\n";
        echo "   Trait LogsChanges có thể chưa hoạt động đúng.\n";
    }

    // Rollback
    $student->class_name = $oldClass;
    $student->save();
} else {
    echo "❌ Không tìm thấy student để test\n";
}

echo "\n=== TEST UPDATE DEGREE ===\n";

$degree = Degree::first();
if ($degree) {
    echo "Test degree: {$degree->registration_number} (ID: {$degree->degree_id})\n";

    $logsBefore = ChangeLog::where('entity_type', 'Degree')
        ->where('entity_id', $degree->degree_id)
        ->count();

    echo "Logs trước update: {$logsBefore}\n";

    // Update degree
    $oldRanking = $degree->ranking;
    $degree->ranking = 'Giỏi [TEST]';
    $degree->save();

    $logsAfter = ChangeLog::where('entity_type', 'Degree')
        ->where('entity_id', $degree->degree_id)
        ->count();

    echo "Logs sau update: {$logsAfter}\n";

    if ($logsAfter > $logsBefore) {
        echo "✅ THÀNH CÔNG: Log đã được tạo khi update degree!\n";

        $newLog = ChangeLog::where('entity_type', 'Degree')
            ->where('entity_id', $degree->degree_id)
            ->latest()
            ->first();

        if ($newLog) {
            echo "   - Description: {$newLog->change_description}\n";
            echo "   - Field: {$newLog->changed_field}\n";
            echo "   - Old → New: {$newLog->old_value} → {$newLog->new_value}\n";
        }
    } else {
        echo "❌ LỖI: Log KHÔNG được tạo khi update degree!\n";
        echo "   Trait LogsChanges có thể chưa hoạt động đúng.\n";
    }

    // Rollback
    $degree->ranking = $oldRanking;
    $degree->save();
} else {
    echo "❌ Không tìm thấy degree để test\n";
}

echo "\n=== KẾT THÚC TEST ===\n\n";
