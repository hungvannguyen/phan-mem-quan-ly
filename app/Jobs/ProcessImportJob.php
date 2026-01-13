<?php

namespace App\Jobs;

use App\Imports\DegreeImport;
use App\Imports\PoliticalTheoryImport;
use App\Imports\CertificateImport;
use App\Models\ImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ProcessImportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600; // 1 hour
    public $tries = 3;
    public $backoff = 300; // 5 minutes

    protected $importLogId;
    protected $filePath;
    protected $importType;

    /**
     * Create a new job instance.
     */
    public function __construct($importLogId, $filePath, $importType)
    {
        $this->importLogId = $importLogId;
        $this->filePath = $filePath;
        $this->importType = $importType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $importLog = ImportLog::find($this->importLogId);

        if (!$importLog) {
            Log::error('ImportLog not found', ['id' => $this->importLogId]);
            return;
        }

        try {
            // Get import instance
            $import = $this->getImportInstance();

            // Get file from storage
            $fileContent = Storage::disk('local')->get($this->filePath);

            // Create temp file with proper extension
            $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);
            $tempFile = tempnam(sys_get_temp_dir(), 'import_') . '.' . $extension;
            file_put_contents($tempFile, $fileContent);

            // Execute import
            Excel::import($import, $tempFile);

            // Get statistics
            $stats = $import->getStatistics();

            // Update import log
            $importLog->update([
                'status' => $stats['errors'] > 0 ? 'completed_with_errors' : 'completed',
                'total_rows' => $stats['imported'] + $stats['errors'],
                'success_rows' => $stats['imported'],
                'error_rows' => $stats['errors'],
                'error_details' => $stats['errors'] > 0 ? json_encode($stats['error_details']) : null,
                'completed_at' => now(),
            ]);

            // Clean up
            unlink($tempFile);
            Storage::disk('local')->delete($this->filePath);

            // TODO: Gửi notification cho user
            // $importLog->user->notify(new ImportCompletedNotification($importLog));

            Log::info('Import completed successfully', [
                'import_log_id' => $this->importLogId,
                'stats' => $stats,
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errorDetails = [];
            foreach ($failures as $failure) {
                $errorDetails[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }

            $importLog->update([
                'status' => 'failed',
                'error_rows' => count($failures),
                'error_details' => json_encode($errorDetails),
                'completed_at' => now(),
            ]);

            Log::error('Import validation failed', [
                'import_log_id' => $this->importLogId,
                'errors' => $errorDetails,
            ]);

            // Clean up
            Storage::disk('local')->delete($this->filePath);

        } catch (\Exception $e) {
            $importLog->update([
                'status' => 'failed',
                'error_details' => json_encode(['error' => $e->getMessage()]),
                'completed_at' => now(),
            ]);

            Log::error('Import failed', [
                'import_log_id' => $this->importLogId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up
            Storage::disk('local')->delete($this->filePath);

            throw $e;
        }
    }

    /**
     * Get import instance theo type
     */
    private function getImportInstance()
    {
        $documentReference = 'IMPORT_' . $this->importLogId . '_' . date('YmdHis');

        return match($this->importType) {
            'degree' => new DegreeImport($documentReference),
            'political_theory' => new PoliticalTheoryImport(),
            'certificate' => new CertificateImport(),
            default => throw new \InvalidArgumentException('Invalid import type'),
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $importLog = ImportLog::find($this->importLogId);

        if ($importLog) {
            $importLog->update([
                'status' => 'failed',
                'error_details' => json_encode([
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]),
                'completed_at' => now(),
            ]);
        }

        Log::error('Import job failed completely', [
            'import_log_id' => $this->importLogId,
            'error' => $exception->getMessage(),
        ]);
    }
}
