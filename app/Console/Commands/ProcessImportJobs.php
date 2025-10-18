<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDiplomaBlankImportJob;
use App\Models\DiplomaBlankImport;
use App\Enums\ImportStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessImportJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imports:process-jobs {--limit=5 : Maximum number of imports to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process PROCESSING DiplomaBlankImport records by dispatching appropriate import jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        $this->info('Scanning for PROCESSING DiplomaBlankImport records...');

        // Lấy các import đang processing
        $processingImports = DiplomaBlankImport::where('status', ImportStatus::PROCESSING)
            ->orderBy('created_at', 'asc') // FIFO - First In, First Out
            ->limit($limit)
            ->get();

        if ($processingImports->isEmpty()) {
            $this->info('No processing imports found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$processingImports->count()} processing import(s). Dispatching appropriate jobs...");

        $dispatchedCount = 0;

        foreach ($processingImports as $import) {
            try {
                // Kiểm tra xem có phải là update import hay tạo mới
                $isUpdate = $this->isUpdateImport($import);

                if ($isUpdate) {
                    // Dispatch UpdateDiplomaBlankImportJob với dữ liệu từ import
                    $updateData = [
                        'prefix' => $import->prefix,
                        'suffix' => $import->suffix,
                        'from_number' => $import->from_number,
                        'to_number' => $import->to_number,
                    ];

                    \App\Jobs\UpdateDiplomaBlankImportJob::dispatch($import, $updateData);

                    $this->line("✓ Dispatched UpdateDiplomaBlankImportJob for Import ID: {$import->id}");
                    Log::info("Dispatched UpdateDiplomaBlankImportJob for import ID: {$import->id}");
                } else {
                    // Dispatch job bất đồng bộ vào queue cho tạo mới
                    ProcessDiplomaBlankImportJob::dispatch($import);

                    $this->line("✓ Dispatched ProcessDiplomaBlankImportJob for Import ID: {$import->id}");
                    Log::info("Dispatched ProcessDiplomaBlankImportJob for import ID: {$import->id}");
                }

                $dispatchedCount++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to dispatch job for Import ID: {$import->id} - Error: {$e->getMessage()}");

                Log::error("Failed to dispatch job for import ID: {$import->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Successfully dispatched {$dispatchedCount} job(s) to queue.");

        // Log thống kê
        $this->displayStatistics();

        return Command::SUCCESS;
    }

    /**
     * Kiểm tra xem import có phải là update hay tạo mới
     * Import update sẽ có diploma blanks đã tồn tại
     */
    private function isUpdateImport(DiplomaBlankImport $import): bool
    {
        // Nếu import đã có diploma blanks thì đây là update
        return $import->diplomaBlanks()->exists();
    }

    /**
     * Hiển thị thống kê về trạng thái imports
     */
    private function displayStatistics(): void
    {
        $stats = [
            'Pending' => DiplomaBlankImport::where('status', ImportStatus::PENDING)->count(),
            'Processing' => DiplomaBlankImport::where('status', ImportStatus::PROCESSING)->count(),
            'Completed' => DiplomaBlankImport::where('status', ImportStatus::COMPLETED)->count(),
            'Failed' => DiplomaBlankImport::where('status', ImportStatus::FAILED)->count(),
        ];

        $this->newLine();
        $this->info('Import Statistics:');

        foreach ($stats as $status => $count) {
            $this->line("  {$status}: {$count}");
        }
    }
}
