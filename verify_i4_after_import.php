<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking I4 after import...\n";
echo "=========================================\n\n";

$degree = DB::table('degrees')->where('degree_id', 149)->first();
echo "Degree ID: 149\n";
echo "Registration: {$degree->registration_number}\n";
echo "Current diploma_blank_id: " . ($degree->diploma_blank_id ?? 'NULL') . "\n";

if ($degree->diploma_blank_id) {
    $blank = DB::table('diploma_blanks')->where('diploma_blank_id', $degree->diploma_blank_id)->first();
    echo "Current Diploma Serial: " . ($blank->serial_number ?? 'NULL') . "\n";
    echo "  => Should be: TS.2024.004R (AC) after successful reissue\n";
}

echo "\n--- Reissues ---\n";
$reissues = DB::table('degree_reissues')->where('degree_id', 149)->get();
echo "Total: " . count($reissues) . "\n\n";

foreach ($reissues as $r) {
    echo "Reissue ID: {$r->reissue_id}\n";
    echo "  Edit Content: {$r->edit_content}\n";
    echo "  Recall Decision: " . ($r->recall_decision ?? 'NULL') . "\n";
    echo "  Decision Date: " . ($r->decision_date ?? 'NULL') . "\n";

    if ($r->old_diploma_blank_id) {
        $old = DB::table('diploma_blanks')->where('diploma_blank_id', $r->old_diploma_blank_id)->first();
        echo "  Old Blank: " . ($old->serial_number ?? 'NULL') . " (Should be: TS.2024.004 / W)\n";
    } else {
        echo "  Old Blank: NULL ❌\n";
    }

    if ($r->new_diploma_blank_id) {
        $new = DB::table('diploma_blanks')->where('diploma_blank_id', $r->new_diploma_blank_id)->first();
        echo "  New Blank: " . ($new->serial_number ?? 'NULL') . " (Should be: TS.2024.004R / AC)\n";
    } else {
        echo "  New Blank: NULL ❌\n";
    }
}

echo "\n--- Expected Result ---\n";
echo "✓ degree.diploma_blank_id should point to AC (TS.2024.004R)\n";
echo "✓ reissue.old_diploma_blank_id should point to W (TS.2024.004)\n";
echo "✓ reissue.new_diploma_blank_id should point to AC (TS.2024.004R)\n";
