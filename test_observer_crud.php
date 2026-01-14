<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Degree;
use App\Models\Student;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set user ID cho Auth (giả lập đăng nhập)
Auth::loginUsingId(1);

echo "=== TESTING OBSERVER CRUD ===\n\n";

// ============================================
// TEST 1: SETUP - GET EXISTING STUDENT
// ============================================
echo "TEST 1: SETUP - GET EXISTING STUDENT\n";
echo str_repeat("-", 50) . "\n";

$student = Student::first();

if (!$student) {
    die("ERROR: No student found in database. Please seed data first.\n");
}

echo "✓ Using Student ID: {$student->student_id}\n";
echo "  Name: {$student->full_name}\n";
echo "\n";

// ============================================
// TEST 2: UPDATE STUDENT
// ============================================
echo "TEST 2: UPDATE STUDENT\n";
echo str_repeat("-", 50) . "\n";

$oldName = $student->full_name;
$student->full_name = 'Trần Thị Test Observer Updated';
$student->place_of_birth = 'Hồ Chí Minh';
$student->save();

echo "✓ Updated Student: {$oldName} → {$student->full_name}\n";

// Kiểm tra ChangeLog
sleep(1);
$updateLogs = ChangeLog::where('entity_type', 'Student')
    ->where('entity_id', $student->student_id)
    ->where('action_type', 'update')
    ->get();

if ($updateLogs->count() > 0) {
    echo "✓ ChangeLog updates detected ({$updateLogs->count()} logs):\n";
    foreach ($updateLogs as $log) {
        echo "  - {$log->field_name}: {$log->old_value} → {$log->new_value}\n";
    }
} else {
    echo "✗ ERROR: No ChangeLog updates!\n";
}

echo "\n";

// ============================================
// TEST 3: CREATE DEGREE
// ============================================
echo "TEST 3: CREATE DEGREE\n";
echo str_repeat("-", 50) . "\n";

$degree = Degree::create([
    'student_id' => $student->student_id,
    'degree_type' => 'bachelor', // bachelor, master, doctor, certificate
    'registration_number' => 'TEST_' . time(),
    'graduation_year' => 2024,
    'granting_date' => '2024-07-01',
]);

echo "✓ Created Degree ID: {$degree->degree_id}\n";

// Kiểm tra ChangeLog
sleep(1);
$degreeCreateLog = ChangeLog::where('entity_type', 'Degree')
    ->where('entity_id', $degree->degree_id)
    ->where('action_type', 'create')
    ->first();

if ($degreeCreateLog) {
    echo "✓ ChangeLog created detected:\n";
    echo "  - Description: {$degreeCreateLog->change_description}\n";
    echo "  - Action: {$degreeCreateLog->action_type}\n";
} else {
    echo "✗ ERROR: No ChangeLog created!\n";
}

echo "\n";

// ============================================
// TEST 4: UPDATE DEGREE
// ============================================
echo "TEST 4: UPDATE DEGREE\n";
echo str_repeat("-", 50) . "\n";

$degree->graduation_year = 2025;
$degree->save();

echo "✓ Updated Degree graduation_year: 2024 → 2025\n";

// Kiểm tra ChangeLog
sleep(1);
$degreeUpdateLogs = ChangeLog::where('entity_type', 'Degree')
    ->where('entity_id', $degree->degree_id)
    ->where('action_type', 'update')
    ->get();

if ($degreeUpdateLogs->count() > 0) {
    echo "✓ ChangeLog updates detected ({$degreeUpdateLogs->count()} logs):\n";
    foreach ($degreeUpdateLogs as $log) {
        echo "  - {$log->field_name}: {$log->old_value} → {$log->new_value}\n";
    }
} else {
    echo "✗ ERROR: No ChangeLog updates!\n";
}

echo "\n";

// ============================================
// TEST 5: SOFT DELETE DEGREE
// ============================================
echo "TEST 5: SOFT DELETE DEGREE\n";
echo str_repeat("-", 50) . "\n";

$degree->delete();

echo "✓ Soft deleted Degree ID: {$degree->degree_id}\n";

// Kiểm tra ChangeLog
sleep(1);
$deleteLog = ChangeLog::where('entity_type', 'Degree')
    ->where('entity_id', $degree->degree_id)
    ->where('action_type', 'delete')
    ->first();

if ($deleteLog) {
    echo "✓ ChangeLog delete detected:\n";
    echo "  - Description: {$deleteLog->change_description}\n";
} else {
    echo "✗ ERROR: No ChangeLog delete!\n";
}

echo "\n";

// ============================================
// TEST 6: RESTORE DEGREE
// ============================================
echo "TEST 6: RESTORE DEGREE\n";
echo str_repeat("-", 50) . "\n";

$degree->restore();

echo "✓ Restored Degree ID: {$degree->degree_id}\n";

// Kiểm tra ChangeLog
sleep(1);
$restoreLog = ChangeLog::where('entity_type', 'Degree')
    ->where('entity_id', $degree->degree_id)
    ->where('action_type', 'restore')
    ->first();

if ($restoreLog) {
    echo "✓ ChangeLog restore detected:\n";
    echo "  - Description: {$restoreLog->change_description}\n";
} else {
    echo "✗ ERROR: No ChangeLog restore!\n";
}

echo "\n";

// ============================================
// SUMMARY
// ============================================
echo "=== SUMMARY ===\n";
echo str_repeat("=", 50) . "\n";

$allLogs = ChangeLog::where('entity_type', 'Degree')
    ->where('entity_id', $degree->degree_id)
    ->orderBy('created_at', 'asc')
    ->get();

echo "Total ChangeLogs created: {$allLogs->count()}\n\n";

echo "Detailed logs:\n";
foreach ($allLogs as $i => $log) {
    echo ($i + 1) . ". [{$log->entity_type}] {$log->action_type}: {$log->change_description}\n";
    if ($log->field_name) {
        echo "   Field: {$log->field_name} | {$log->old_value} → {$log->new_value}\n";
    }
}

echo "\n";

// ============================================
// CLEANUP
// ============================================
echo "=== CLEANUP ===\n";
echo str_repeat("-", 50) . "\n";

// Xóa logs test
$deletedLogs = ChangeLog::where('entity_type', 'Degree')
    ->where('entity_id', $degree->degree_id)
    ->delete();

// Force delete degree
$degree->forceDelete();

echo "✓ Cleaned up test data:\n";
echo "  - Deleted {$deletedLogs} ChangeLogs\n";
echo "  - Deleted Degree ID: {$degree->degree_id}\n";

echo "\n=== TEST COMPLETED ===\n";
