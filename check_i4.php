<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$student = DB::table('students')->where('full_name', 'LIKE', '%I4%')->first();
if (!$student) {
    echo "Student not found\n";
    exit;
}

echo "Student: {$student->full_name}\n";
$degrees = DB::table('degrees')->where('student_id', $student->student_id)->get();

foreach ($degrees as $deg) {
    echo "Degree ID: {$deg->degree_id}\n";
    echo "Registration: {$deg->registration_number}\n\n";

    // Check Reissues
    $reissues = DB::table('degree_reissues')->where('degree_id', $deg->degree_id)->get();
    echo "  Reissues: " . count($reissues) . "\n";
    foreach ($reissues as $reissue) {
        echo "    * Edit Content: {$reissue->edit_content}\n";
        echo "      Recall Decision: " . ($reissue->recall_decision ?? 'N/A') . "\n";
        echo "      Decision Date: " . ($reissue->decision_date ?? 'N/A') . "\n";

        if ($reissue->old_diploma_blank_id) {
            $oldBlank = DB::table('diploma_blanks')->where('diploma_blank_id', $reissue->old_diploma_blank_id)->first();
            echo "      Old Blank Serial: " . ($oldBlank->serial_number ?? 'NULL') . "\n";
        } else {
            echo "      Old Blank Serial: NULL\n";
        }

        if ($reissue->new_diploma_blank_id) {
            $newBlank = DB::table('diploma_blanks')->where('diploma_blank_id', $reissue->new_diploma_blank_id)->first();
            echo "      New Blank Serial: " . ($newBlank->serial_number ?? 'NULL') . "\n";
        } else {
            echo "      New Blank Serial: NULL\n";
        }
    }
}
