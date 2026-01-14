<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Degree;
use App\Models\Student;
use App\Models\ChangeLog;

$beforeCount = ChangeLog::count();
echo "Before: $beforeCount logs\n";

$student = Student::first();
$degree = Degree::create([
    'student_id' => $student->student_id,
    'degree_type' => 'bachelor',
    'registration_number' => 'TEST_' . time(),
    'graduation_year' => 2024,
    'granting_date' => '2024-07-01',
]);

sleep(1);
$afterCount = ChangeLog::count();
echo "After: $afterCount logs\n";
echo "Difference: " . ($afterCount - $beforeCount) . "\n\n";

$createLog = ChangeLog::where('entity_id', $degree->degree_id)
    ->where('action_type', 'create')
    ->first();

if ($createLog) {
    echo "✓ Create log found!\n";
    echo "  Description: {$createLog->change_description}\n";
    echo "  Action: {$createLog->action_type}\n";
} else {
    echo "✗ NO create log found!\n";
}

// Cleanup
$degree->forceDelete();
ChangeLog::where('entity_id', $degree->degree_id)->delete();
echo "\nCleaned up test data.\n";
