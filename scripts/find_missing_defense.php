<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Degree;

$rows = Degree::whereNotNull('council_decision_date')->whereNull('defense_date')->get();
echo "count: " . $rows->count() . "\n";
foreach ($rows as $r) {
    echo $r->degree_id . ' ' . ($r->registration_number ?? '') . "\n";
}
