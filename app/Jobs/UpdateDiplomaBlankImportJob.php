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

            // Lấy thông tin hiện tại của import (đã được cập nhật)
            $newFromNumber = intval($this->import->from_number);
            $newToNumber = intval($this->import->to_number);
            $newPrefix = $this->import->prefix ?? '';
            $newSuffix = $this->import->suffix ?? '';

            // Tính số lượng mới
            $newQuantity = $newToNumber - $newFromNumber + 1;

            // Đếm số diploma blanks hiện có
            $currentCount = $this->import->diplomaBlanks()->count();

            Log::info("UpdateDiplomaBlankImportJob details", [
                'import_id' => $this->import->id,
                'new_from' => $newFromNumber,
                'new_to' => $newToNumber,
                'new_quantity' => $newQuantity,
                'current_count' => $currentCount,
                'prefix' => $newPrefix,
                'suffix' => $newSuffix
            ]);

            // Đồng bộ diploma blanks với thông tin mới
            $this->syncDiplomaBlanks($newFromNumber, $newToNumber, $newPrefix, $newSuffix, $currentCount, $newQuantity);

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
     * Đồng bộ diploma blanks với thông tin import mới
     */
    private function syncDiplomaBlanks(int $fromNumber, int $toNumber, string $prefix, string $suffix, int $currentCount, int $newQuantity): void
    {
        // Tạo danh sách serial numbers cần có
        $expectedSerials = [];
        for ($i = $fromNumber; $i <= $toNumber; $i++) {
            $expectedSerials[] = $prefix . $i . $suffix;
        }

        // Lấy danh sách serial hiện có
        $existingSerials = $this->import->diplomaBlanks()->pluck('serial_number')->toArray();

        // Tìm serial cần thêm
        $serialsToAdd = array_diff($expectedSerials, $existingSerials);

        // Tìm serial cần xóa (không còn trong danh sách mới)
        $serialsToRemove = array_diff($existingSerials, $expectedSerials);

        Log::info("Sync diploma blanks", [
            'expected_count' => count($expectedSerials),
            'existing_count' => count($existingSerials),
            'to_add' => count($serialsToAdd),
            'to_remove' => count($serialsToRemove),
            'first_5_to_add' => array_slice($serialsToAdd, 0, 5)
        ]);

        // Thêm diploma blanks mới
        if (!empty($serialsToAdd)) {
            $this->addMissingDiplomaBlanks($serialsToAdd);
        }

        // Xóa diploma blanks không cần thiết
        if (!empty($serialsToRemove)) {
            $this->removeUnwantedDiplomaBlanks($serialsToRemove);
        }

        // Cập nhật processed_count và last_processed_serial
        $finalCount = $this->import->diplomaBlanks()->count();
        $lastSerial = !empty($expectedSerials) ? end($expectedSerials) : null;

        $this->import->update([
            'processed_count' => $finalCount,
            'last_processed_serial' => $lastSerial,
            'total_quantity' => $newQuantity
        ]);

        Log::info("Sync completed", [
            'final_count' => $finalCount,
            'last_serial' => $lastSerial
        ]);
    }

    /**
     * Thêm các diploma blanks bị thiếu
     */
    private function addMissingDiplomaBlanks(array $serialsToAdd): void
    {
        $diplomaBlanks = [];
        $batchSize = 100;

        foreach ($serialsToAdd as $serialNumber) {
            $diplomaBlanks[] = [
                'serial_number' => $serialNumber,
                'type_id' => $this->import->type_id,
                'import_id' => $this->import->id,
                'status' => DiplomaBlankStatus::IN_STOCK->value,
                'import_date' => $this->import->import_date,
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Batch insert mỗi 100 records
            if (count($diplomaBlanks) >= $batchSize) {
                DiplomaBlank::insert($diplomaBlanks);
                Log::info("Added " . count($diplomaBlanks) . " diploma blanks");
                $diplomaBlanks = [];
            }
        }

        // Insert các records còn lại
        if (!empty($diplomaBlanks)) {
            DiplomaBlank::insert($diplomaBlanks);
            Log::info("Added " . count($diplomaBlanks) . " final diploma blanks");
        }
    }

    /**
     * Xóa các diploma blanks không cần thiết
     */
    private function removeUnwantedDiplomaBlanks(array $serialsToRemove): void
    {
        // Kiểm tra xem có phôi nào đã được cấp (ISSUED) không
        $issuedBlanks = $this->import->diplomaBlanks()
            ->whereIn('serial_number', $serialsToRemove)
            ->where('status', DiplomaBlankStatus::ISSUED->value)
            ->pluck('serial_number')
            ->toArray();

        if (!empty($issuedBlanks)) {
            // Có phôi đã cấp, không thể xóa
            $errorMessage = "Không thể cập nhật Import vì có " . count($issuedBlanks) . " phôi đã được cấp văn bằng: " . implode(', ', array_slice($issuedBlanks, 0, 5));

            Log::error("Cannot remove issued diploma blanks", [
                'import_id' => $this->import->id,
                'issued_count' => count($issuedBlanks),
                'issued_serials' => $issuedBlanks
            ]);

            throw new Exception($errorMessage);
        }

        // Chỉ xóa những diploma blanks có status IN_STOCK (chưa cấp)
        $deletedCount = $this->import->diplomaBlanks()
            ->whereIn('serial_number', $serialsToRemove)
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)
            ->delete();

        Log::info("Removed {$deletedCount} unwanted diploma blanks (all IN_STOCK)");
    }

    /**
     * Cập nhật prefix/suffix cho các phôi hiện có (DEPRECATED)
     */
    private function updatePrefixSuffix(string $oldPrefix, string $oldSuffix, string $newPrefix, string $newSuffix, int $fromNumber, int $toNumber): void
    {
        $batchSize = 100;
        $processed = 0;

        // Sử dụng relationship thay vì match pattern - an toàn hơn
        $diplomaBlanks = $this->import->diplomaBlanks()->get();

        foreach ($diplomaBlanks->chunk($batchSize) as $chunk) {
            foreach ($chunk as $diplomaBlank) {
                // Extract số từ serial cũ
                $oldSerial = $diplomaBlank->serial_number;
                $numberPart = str_replace([$oldPrefix, $oldSuffix], '', $oldSerial);

                // Tạo serial mới với prefix/suffix mới
                $newSerial = $newPrefix . $numberPart . $newSuffix;

                // Cập nhật serial number
                $diplomaBlank->update(['serial_number' => $newSerial]);
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
                    'import_id' => $this->import->id, // Liên kết với import record
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
        // Sử dụng relationship để xóa - an toàn và chính xác hơn
        $batchSize = 100;

        // Lấy các diploma blanks thuộc import này, sắp xếp theo serial descending để xóa từ cuối
        $diplomaBlanksToRemove = $this->import->diplomaBlanks()
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)
            ->orderBy('serial_number', 'desc')
            ->take($removeCount)
            ->get();

        $deletedCount = 0;

        foreach ($diplomaBlanksToRemove->chunk($batchSize) as $chunk) {
            $idsToDelete = $chunk->pluck('diploma_blank_id')->toArray();

            // Xóa batch này
            $batchDeleted = DiplomaBlank::whereIn('diploma_blank_id', $idsToDelete)->delete();
            $deletedCount += $batchDeleted;

            Log::info("Removed {$batchDeleted} unused diploma blanks from batch");
        }

        // Cập nhật last_processed_serial
        $lastSerial = $prefix . $newToNumber . $suffix;
        $this->import->update([
            'processed_count' => max(0, $this->import->processed_count - $deletedCount),
            'last_processed_serial' => $lastSerial
        ]);

        Log::info("Total removed: {$deletedCount} diploma blanks from import #{$this->import->id}");
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
