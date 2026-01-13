<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$student = DB::table('students')->where('full_name', 'LIKE', '%Y7%')->first();

if (!$student) {
    echo "Student not found\n";
    exit;
}

echo "Student: {$student->full_name} ({$student->student_code})\n";
echo "=========================================\n\n";

$degrees = DB::table('degrees')->where('student_id', $student->student_id)->get();
echo "Degrees: " . count($degrees) . "\n\n";

foreach ($degrees as $deg) {
    echo "- Registration Number: {$deg->registration_number}\n";
    echo "  Degree Type: {$deg->degree_type}\n";
    echo "  Graduation Decision: " . ($deg->graduation_decision_number ?? 'NULL') . "\n";

    if ($deg->diploma_blank_id) {
        $blank = DB::table('diploma_blanks')->where('diploma_blank_id', $deg->diploma_blank_id)->first();
        echo "  Diploma Serial Number: " . ($blank->serial_number ?? 'NULL') . "\n";
    } else {
        echo "  Diploma Blank ID: NULL\n";
    }

    $logs = DB::table('change_logs')
        ->where('entity_type', 'App\\Models\\Degree')
        ->where('entity_id', $deg->degree_id)
        ->orderBy('log_id')
        ->get();

    echo "  Change Logs: " . count($logs) . "\n";
    foreach ($logs as $log) {
        echo "    * [{$log->action_type}] {$log->change_description}\n";
        echo "      Decision: " . ($log->decision_number ?? 'N/A') . "\n";
        if ($log->decision_date) {
            echo "      Date: {$log->decision_date}\n";
        }
        if ($log->additional_data) {
            $data = json_decode($log->additional_data, true);
            if ($data) {
                echo "      Additional: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
    }

    // Check degree reissues
    $reissues = DB::table('degree_reissues')
        ->where('degree_id', $deg->degree_id)
        ->get();

    if (count($reissues) > 0) {
        echo "  \n  Degree Reissues: " . count($reissues) . "\n";
        foreach ($reissues as $reissue) {
            echo "    * Old: {$reissue->old_registration_number} → New: {$reissue->new_registration_number}\n";
            echo "      Content: {$reissue->edit_content}\n";
            echo "      Decision: {$reissue->recall_decision}\n";
            echo "      Date: {$reissue->decision_date}\n";
        }
    }

    echo "\n";
}
