<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule để xử lý các DiplomaBlankImport đang pending
Schedule::command('imports:process-pending --limit=5')
    ->everyMinute()
    ->withoutOverlapping() // Không cho phép chạy đồng thời
    ->runInBackground() // Chạy trong background
    ->appendOutputTo(storage_path('logs/pending-imports.log'));
