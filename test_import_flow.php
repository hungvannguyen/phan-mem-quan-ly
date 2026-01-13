<?php

/**
 * Script test import flow:
 * 1. User upload file
 * 2. File được đưa vào queue
 * 3. Queue worker xử lý import
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\DataImportController;
use App\Models\ImportLog;
use App\Models\Student;
use App\Models\Degree;
use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankImport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST IMPORT FLOW ===\n\n";

// Step 0: Authenticate as first user
$user = User::first();
if (!$user) {
    echo "❌ No users found in database. Please seed users first.\n";
    exit(1);
}

Auth::login($user);
echo "✓ Authenticated as: {$user->name} (ID: {$user->user_id})\n\n";

// Step 1: Check test file exists
$testFile = storage_path('app/test_degree_import.xls');
if (!file_exists($testFile)) {
    echo "❌ Test file not found: {$testFile}\n";
    echo "Please run: php test_import_degree.php first\n";
    exit(1);
}

echo "✓ Test file found: {$testFile}\n";
echo "File size: " . number_format(filesize($testFile)) . " bytes\n\n";

// Step 2: Count records before import
$studentsBefore = Student::count();
$degreesBefore = Degree::count();
$diplomaBlanksBefore = DiplomaBlank::count();
$diplomaBlankImportsBefore = DiplomaBlankImport::count();

echo "📊 Records BEFORE import:\n";
echo "  - Students: {$studentsBefore}\n";
echo "  - Degrees: {$degreesBefore}\n";
echo "  - DiplomaBlank: {$diplomaBlanksBefore}\n";
echo "  - DiplomaBlankImport: {$diplomaBlankImportsBefore}\n\n";

// Step 3: Create fake uploaded file
echo "🚀 Starting import test...\n";

$uploadedFile = new UploadedFile(
    $testFile,
    'test_degree_import.xls',
    'application/vnd.ms-excel',
    null,
    true
);

// Step 4: Create request manually
$request = Illuminate\Http\Request::create('/import/handle', 'POST', [
    'import_type' => 'degree',
    'use_queue' => '1',
]);

$request->files->set('excel_file', $uploadedFile);

// Step 5: Execute import
try {
    $controller = new DataImportController();
    $response = $controller->handleImport($request);

    echo "✓ Import request processed\n";

    // Check import log
    $latestLog = ImportLog::latest()->first();
    if ($latestLog) {
        echo "\n📝 Import Log created:\n";
        echo "  - ID: {$latestLog->id}\n";
        echo "  - Type: {$latestLog->import_type}\n";
        echo "  - Status: {$latestLog->status}\n";
        echo "  - File: {$latestLog->file_name}\n";
        echo "  - Created: {$latestLog->created_at}\n";
    }

    // Step 6: Check if job is queued
    $jobsInQueue = DB::table('jobs')->count();
    echo "\n📦 Jobs in queue: {$jobsInQueue}\n";

    if ($jobsInQueue > 0) {
        echo "\n⏳ Processing queue job...\n";
        echo "Running: php artisan queue:work --once\n\n";

        // Process one job
        $exitCode = null;
        $output = [];
        exec('cd ' . base_path() . ' && php artisan queue:work --once 2>&1', $output, $exitCode);

        foreach ($output as $line) {
            echo "  {$line}\n";
        }

        if ($exitCode === 0) {
            echo "\n✓ Queue job processed successfully\n";
        } else {
            echo "\n❌ Queue job failed with exit code: {$exitCode}\n";
        }

        // Refresh import log
        $latestLog->refresh();
        echo "\n📝 Import Log updated:\n";
        echo "  - Status: {$latestLog->status}\n";
        echo "  - Total rows: {$latestLog->total_rows}\n";
        echo "  - Success rows: {$latestLog->success_rows}\n";
        echo "  - Error rows: {$latestLog->error_rows}\n";
        echo "  - Completed: {$latestLog->completed_at}\n";

        if ($latestLog->error_details) {
            echo "  - Errors: {$latestLog->error_details}\n";
        }
    } else {
        echo "ℹ️  No jobs in queue (processed synchronously)\n";
    }

    // Step 7: Count records after import
    $studentsAfter = Student::count();
    $degreesAfter = Degree::count();
    $diplomaBlanksAfter = DiplomaBlank::count();
    $diplomaBlankImportsAfter = DiplomaBlankImport::count();

    echo "\n📊 Records AFTER import:\n";
    echo "  - Students: {$studentsAfter} (+" . ($studentsAfter - $studentsBefore) . ")\n";
    echo "  - Degrees: {$degreesAfter} (+" . ($degreesAfter - $degreesBefore) . ")\n";
    echo "  - DiplomaBlank: {$diplomaBlanksAfter} (+" . ($diplomaBlanksAfter - $diplomaBlanksBefore) . ")\n";
    echo "  - DiplomaBlankImport: {$diplomaBlankImportsAfter} (+" . ($diplomaBlankImportsAfter - $diplomaBlankImportsBefore) . ")\n";

    // Step 8: Verify imported data
    echo "\n🔍 Verifying imported data:\n";

    $testStudent = Student::where('full_name', 'Nguyễn Văn Test')->first();
    if ($testStudent) {
        echo "  ✓ Found student: {$testStudent->full_name}\n";
        echo "    - Date of birth: {$testStudent->date_of_birth}\n";
        echo "    - Gender: {$testStudent->gender}\n";
        echo "    - Course: {$testStudent->course}\n";

        $degree = $testStudent->degrees()->first();
        if ($degree) {
            echo "  ✓ Found degree:\n";
            echo "    - Type: {$degree->degree_type}\n";
            echo "    - Registration: {$degree->registration_number}\n";
            echo "    - Granting date: {$degree->granting_date}\n";
            echo "    - Ranking: {$degree->ranking}\n";

            if ($degree->diplomaBlank) {
                echo "  ✓ Found diploma blank:\n";
                echo "    - Serial: {$degree->diplomaBlank->serial_number}\n";
                echo "    - Type ID: {$degree->diplomaBlank->type_id}\n";
                echo "    - Status: {$degree->diplomaBlank->status}\n";
            }
        }
    }

    echo "\n✅ TEST COMPLETED!\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
