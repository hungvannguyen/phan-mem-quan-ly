<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Major;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$major = Major::first();
if ($major) {
    echo $major->major_id;
} else {
    echo "NO_MAJOR";
}
