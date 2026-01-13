<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$log = DB::table('import_logs')->latest()->first();

if (!$log) {
    echo "No import logs found\n";
    exit;
}

echo "Latest Import Log:\n";
echo "==================\n";
echo "ID: {$log->id}\n";
echo "Type: {$log->import_type}\n";
echo "File: {$log->file_name}\n";
echo "Status: {$log->status}\n";
echo "Total Rows: {$log->total_rows}\n";
echo "Success: {$log->success_rows}\n";
echo "Errors: {$log->error_rows}\n";

if ($log->error_details) {
    $errors = json_decode($log->error_details, true);
    if ($errors && count($errors) > 0) {
        echo "\nFirst 5 Errors:\n";
        foreach (array_slice($errors, 0, 5) as $error) {
            echo "  Row {$error['row']}: {$error['error']}\n";
        }
    }
}

echo "\nStudents count: " . DB::table('students')->where('student_code', 'LIKE', 'IMP%')->count() . "\n";
echo "Degrees count: " . DB::table('degrees')->count() . "\n";
