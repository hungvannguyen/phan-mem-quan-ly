<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Degree;

$d = Degree::orderBy('degree_id', 'desc')->first();
if (!$d) {
    echo "No degrees found\n";
    exit(0);
}

echo "degree_id: " . ($d->degree_id ?? 'none') . "\n";
echo "registration_number: " . ($d->registration_number ?? 'none') . "\n";
$def = $d->defense_date;
$cd = $d->council_decision_date;
if ($def instanceof \DateTimeInterface) {
    echo "defense_date: " . $def->format('Y-m-d H:i:s') . "\n";
} else {
    echo "defense_date: "; var_export($def); echo "\n";
}
if ($cd instanceof \DateTimeInterface) {
    echo "council_decision_date: " . $cd->format('Y-m-d H:i:s') . "\n";
} else {
    echo "council_decision_date: "; var_export($cd); echo "\n";
}
