<?php

namespace App\Jobs;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankImport;
use App\Enums\ImportStatus;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateDiplomaBlankImportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 600; // 10 minutes timeout
    public $tries = 3; // Retry 3 times on failure

    protected DiplomaBlankImport $import;
    protected array $updateData;

    /**
     * Create a new job instance.
     */
    public function __construct(DiplomaBlankImport $import, array $updateData)
    {
        $this->import = $import;
        $this->updateData = $updateData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Kiểm tra xem có thể update không (chỉ khi status là COMPLETED hoặc PROCESSING)
        if (!in_array($this->import->status, [ImportStatus::COMPLETED, ImportStatus::PROCESSING])) {
            Log::info("Import ID {$this->import->id} cannot be updated in current status: {$this->import->status->value}");
            return;
        }

        try {
            Log::info("Starting update import ID: {$this->import->id}");

            // Đánh dấu import đang được xử lý update
            $this->import->update(['status' => ImportStatus::PROCESSING]);

            // Bắt đầu transaction
            DB::beginTransaction();

            $oldFromNumber = intval($this->import->from_number);
            $oldToNumber = intval($this->import->to_number);
            $newFromNumber = intval($this->updateData['from_number']);
            $newToNumber = intval($this->updateData['to_number']);

            $oldPrefix = $this->import->prefix ?? '';
            $oldSuffix = $this->import->suffix ?? '';
            $newPrefix = $this->updateData['prefix'] ?? '';
            $newSuffix = $this->updateData['suffix'] ?? '';

            // Tính toán số lượng cũ và mới
            $oldQuantity = $oldToNumber - $oldFromNumber + 1;
            $newQuantity = $newToNumber - $newFromNumber + 1;

            // Xử lý thay đổi prefix/suffix
            if ($oldPrefix !== $newPrefix || $oldSuffix !== $newSuffix) {
                $this->updatePrefixSuffix($oldPrefix, $oldSuffix, $newPrefix, $newSuffix, $oldFromNumber, $oldToNumber);
            }

            // Xử lý thay đổi số lượng
            if ($newQuantity > $oldQuantity) {
                // Tăng số phôi - thêm phôi mới từ last_processed_serial + 1
                $this->addDiplomaBlanks($newFromNumber, $newToNumber, $newPrefix, $newSuffix, $oldQuantity);
            } elseif ($newQuantity < $oldQuantity) {
                // Giảm số phôi - xóa từ last_processed_serial đến phần được giảm
                $this->removeDiplomaBlanks($newFromNumber, $newToNumber, $newPrefix, $newSuffix, $oldQuantity - $newQuantity);
            }

            // Cập nhật thông tin import
            $this->import->update([
                'prefix' => $newPrefix,
                'suffix' => $newSuffix,
                'from_number' => $this->updateData['from_number'],
                'to_number' => $this->updateData['to_number'],
                'total_quantity' => $newQuantity,
                'status' => ImportStatus::COMPLETED
            ]);

            DB::commit();

            Log::info("Successfully updated import ID: {$this->import->id}");
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollback();

            // Đánh dấu lỗi
            $errorMessage = "Error updating import: " . $e->getMessage();
            $this->import->update([
                'status' => ImportStatus::FAILED,
                'error_message' => $errorMessage
            ]);

            Log::error("Failed to update import ID: {$this->import->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw exception để job có thể retry
            throw $e;
        }
    }

    /**
     * Cập nhật prefix/suffix cho các phôi hiện có
     */
    private function updatePrefixSuffix(string $oldPrefix, string $oldSuffix, string $newPrefix, string $newSuffix, int $fromNumber, int $toNumber): void
    {
        $batchSize = 100;
        $processed = 0;

        for ($num = $fromNumber; $num <= $toNumber; $num += $batchSize) {
            $endNum = min($num + $batchSize - 1, $toNumber);

            $updates = [];
            for ($i = $num; $i <= $endNum; $i++) {
                $oldSerial = $oldPrefix . $i . $oldSuffix;
                $newSerial = $newPrefix . $i . $newSuffix;

                $updates[] = [
                    'old_serial' => $oldSerial,
                    'new_serial' => $newSerial
                ];
            }

            // Batch update
            foreach ($updates as $update) {
                DiplomaBlank::where('serial_number', $update['old_serial'])
                    ->update(['serial_number' => $update['new_serial']]);
                $processed++;
            }

            Log::info("Updated prefix/suffix for {$processed} diploma blanks");
        }
    }

    /**
     * Thêm phôi mới khi tăng số lượng
     */
    private function addDiplomaBlanks(int $newFromNumber, int $newToNumber, string $prefix, string $suffix, int $oldQuantity): void
    {
        $diplomaBlanks = [];
        $startNum = $newFromNumber + $oldQuantity;
        $batchSize = 100;

        for ($num = $startNum; $num <= $newToNumber; $num++) {
            $serialNumber = $prefix . $num . $suffix;

            // Kiểm tra xem serial number đã tồn tại chưa
            if (!DiplomaBlank::where('serial_number', $serialNumber)->exists()) {
                $diplomaBlanks[] = [
                    'serial_number' => $serialNumber,
                    'type_id' => $this->import->type_id,
                    'status' => DiplomaBlankStatus::IN_STOCK->value,
                    'import_date' => $this->import->import_date,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Batch insert mỗi 100 records
                if (count($diplomaBlanks) >= $batchSize) {
                    DiplomaBlank::insert($diplomaBlanks);
                    Log::info("Added " . count($diplomaBlanks) . " new diploma blanks");
                    $diplomaBlanks = [];
                }
            }
        }

        // Insert các records còn lại
        if (!empty($diplomaBlanks)) {
            DiplomaBlank::insert($diplomaBlanks);
            Log::info("Added " . count($diplomaBlanks) . " final diploma blanks");
        }

        // Cập nhật last_processed_serial
        $lastSerial = $prefix . $newToNumber . $suffix;
        $addedCount = $newToNumber - $startNum + 1;
        $this->import->update([
            'processed_count' => $this->import->processed_count + $addedCount,
            'last_processed_serial' => $lastSerial
        ]);
    }

    /**
     * Xóa phôi khi giảm số lượng
     */
    private function removeDiplomaBlanks(int $newFromNumber, int $newToNumber, string $prefix, string $suffix, int $removeCount): void
    {
        $serialsToRemove = [];
        $startNum = $newToNumber + 1;
        $endNum = $newToNumber + $removeCount;
        $batchSize = 100;

        for ($num = $startNum; $num <= $endNum; $num += $batchSize) {
            $batchEnd = min($num + $batchSize - 1, $endNum);
            $batchSerials = [];

            for ($i = $num; $i <= $batchEnd; $i++) {
                $serialNumber = $prefix . $i . $suffix;
                $batchSerials[] = $serialNumber;
            }

            // Chỉ xóa các phôi có status IN_STOCK (chưa được sử dụng)
            $deletedCount = DiplomaBlank::whereIn('serial_number', $batchSerials)
                ->where('status', DiplomaBlankStatus::IN_STOCK->value)
                ->delete();

            Log::info("Removed {$deletedCount} unused diploma blanks from batch");
        }

        // Cập nhật last_processed_serial
        $lastSerial = $prefix . $newToNumber . $suffix;
        $this->import->update([
            'processed_count' => max(0, $this->import->processed_count - $removeCount),
            'last_processed_serial' => $lastSerial
        ]);
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error("UpdateDiplomaBlankImportJob failed for import ID: {$this->import->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Đảm bảo import được đánh dấu là failed và khôi phục status COMPLETED
        $this->import->update([
            'status' => ImportStatus::COMPLETED,  // Khôi phục về trạng thái ban đầu
            'error_message' => "Update job failed: " . $exception->getMessage()
        ]);
    }
}