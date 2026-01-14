<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Resetting I4 reissue data...\n";
echo "=========================================\n\n";

// Xóa reissue của I4
$deleted = DB::table('degree_reissues')->where('degree_id', 149)->delete();
echo "Deleted reissues for degree 149: $deleted\n\n";

// Kiểm tra diploma blank
$degree = DB::table('degrees')->where('degree_id', 149)->first();
echo "Current diploma_blank_id: " . ($degree->diploma_blank_id ?? 'NULL') . "\n";

if ($degree->diploma_blank_id) {
    $blank = DB::table('diploma_blanks')->where('diploma_blank_id', $degree->diploma_blank_id)->first();
    echo "Current serial: " . ($blank->serial_number ?? 'NULL') . "\n";
}

echo "\n";

// Check diploma blanks
$w = DB::table('diploma_blanks')->where('serial_number', 'TS.2024.004')->first();
echo "W blank (TS.2024.004) exists: " . ($w ? "YES (ID: {$w->diploma_blank_id})" : 'NO') . "\n";

$ac = DB::table('diploma_blanks')->where('serial_number', 'TS.2024.004R')->first();
echo "AC blank (TS.2024.004R) exists: " . ($ac ? "YES (ID: {$ac->diploma_blank_id})" : 'NO') . "\n";

echo "\nNow re-import the Excel file to test.\n";
