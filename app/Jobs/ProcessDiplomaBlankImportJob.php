<?php

namespace App\Jobs;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankImport;
use App\Enums\ImportStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessDiplomaBlankImportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3; // Retry 3 times on failure

    protected DiplomaBlankImport $import;

    /**
     * Create a new job instance.
     */
    public function __construct(DiplomaBlankImport $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Kiểm tra trạng thái import trước khi xử lý
        if ($this->import->status !== ImportStatus::PENDING) {
            Log::info("Import ID {$this->import->id} is not in PENDING status, skipping...");
            return;
        }

        try {
            Log::info("Starting processing import ID: {$this->import->id}");

            // Đánh dấu import đang được xử lý
            $this->import->markAsProcessing();

            // Bắt đầu transaction
            DB::beginTransaction();

            // Tạo các DiplomaBlank records
            $this->createDiplomaBlanks();

            // Commit transaction
            DB::commit();

            // Đánh dấu hoàn thành
            $this->import->markAsCompleted();

            Log::info("Successfully processed import ID: {$this->import->id}");
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollback();

            // Đánh dấu lỗi
            $errorMessage = "Error processing import: " . $e->getMessage();
            $this->import->markAsFailed($errorMessage);

            Log::error("Failed to process import ID: {$this->import->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw exception để job có thể retry
            throw $e;
        }
    }

    /**
     * Tạo các DiplomaBlank records từ import
     */
    private function createDiplomaBlanks(): void
    {
        $fromNum = intval($this->import->from_number);
        $toNum = intval($this->import->to_number);
        $prefix = $this->import->prefix ?? '';
        $suffix = $this->import->suffix ?? '';
        $typeId = $this->import->type_id;
        $importDate = $this->import->import_date;

        // Chuẩn bị batch insert data
        $diplomaBlanks = [];
        $processedCount = 0;

        for ($num = $fromNum; $num <= $toNum; $num++) {
            // Format số với leading zeros giống với from_number
            $paddedNum = str_pad($num, strlen($this->import->from_number), '0', STR_PAD_LEFT);

            // Tạo serial number
            $serialNumber = $prefix . $paddedNum . $suffix;

            // Kiểm tra xem serial number đã tồn tại chưa
            $existingBlank = DiplomaBlank::where('serial_number', $serialNumber)->first();
            if ($existingBlank) {
                Log::warning("Serial number {$serialNumber} already exists, skipping...");
                continue;
            }

            $diplomaBlanks[] = [
                'serial_number' => $serialNumber,
                'type_id' => $typeId,
                'status' => 'available', // Trạng thái mặc định là có sẵn
                'import_date' => $importDate,
                'issue_date' => null,
                'recall_date' => null,
                'issue_reason' => null,
                'recall_reason' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $processedCount++;

            // Batch insert mỗi 100 records để tránh timeout
            if (count($diplomaBlanks) >= 100) {
                DiplomaBlank::insert($diplomaBlanks);

                // Cập nhật tiến trình
                $this->import->updateProgress($processedCount, $serialNumber);

                // Reset array
                $diplomaBlanks = [];

                Log::info("Processed {$processedCount} diploma blanks for import ID: {$this->import->id}");
            }
        }

        // Insert các records còn lại
        if (count($diplomaBlanks) > 0) {
            DiplomaBlank::insert($diplomaBlanks);
        }

        // Cập nhật tiến trình cuối cùng
        $lastSerial = $prefix . str_pad($toNum, strlen($this->import->from_number), '0', STR_PAD_LEFT) . $suffix;
        $this->import->updateProgress($processedCount, $lastSerial);

        Log::info("Created {$processedCount} diploma blanks for import ID: {$this->import->id}");
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error("ProcessDiplomaBlankImportJob failed for import ID: {$this->import->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Đảm bảo import được đánh dấu là failed
        if ($this->import->status !== ImportStatus::FAILED) {
            $this->import->markAsFailed("Job failed: " . $exception->getMessage());
        }
    }
}
