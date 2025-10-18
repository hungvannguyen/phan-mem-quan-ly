<?php

namespace App\Models;

use App\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiplomaBlankImport extends Model
{
    use HasFactory;
    protected $table = 'diploma_blank_import';
    // Primary key sử dụng mặc định 'id' từ $table->id() trong migration

    protected $fillable = [
        'type_id',
        'document_reference',
        'issue_date',
        'import_date',
        'total_quantity',
        'prefix',
        'suffix',
        'from_number',
        'to_number',
        'status',
        'processed_count',
        'last_processed_serial',
        'error_message',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'import_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_quantity' => 'integer',
        'processed_count' => 'integer',
        'status' => ImportStatus::class
    ];

    /**
     * Relationship với DiplomaBlankType
     */
    public function diplomaBlankType(): BelongsTo
    {
        return $this->belongsTo(DiplomaBlankType::class, 'type_id', 'type_id');
    }

    /**
     * Scope để lấy các import đang chờ xử lý
     */
    public function scopePending($query)
    {
        return $query->where('status', ImportStatus::PENDING);
    }

    /**
     * Scope để lấy các import đang xử lý
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', ImportStatus::PROCESSING);
    }

    /**
     * Scope để lấy các import đã hoàn thành
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', ImportStatus::COMPLETED);
    }

    /**
     * Scope để lấy các import bị lỗi
     */
    public function scopeFailed($query)
    {
        return $query->where('status', ImportStatus::FAILED);
    }

    /**
     * Kiểm tra xem import đã hoàn thành chưa
     */
    public function isCompleted(): bool
    {
        return $this->status === ImportStatus::COMPLETED;
    }

    /**
     * Kiểm tra xem import có đang xử lý không
     */
    public function isProcessing(): bool
    {
        return $this->status === ImportStatus::PROCESSING;
    }

    /**
     * Kiểm tra xem import có bị lỗi không
     */
    public function isFailed(): bool
    {
        return $this->status === ImportStatus::FAILED;
    }

    /**
     * Tính phần trăm hoàn thành
     */
    public function getCompletionPercentage(): float
    {
        if ($this->total_quantity == 0) {
            return 0;
        }

        return round(($this->processed_count / $this->total_quantity) * 100, 2);
    }

    /**
     * Lấy số serial tiếp theo cần xử lý
     */
    public function getNextSerialNumber(): ?string
    {
        if ($this->processed_count >= $this->total_quantity) {
            return null;
        }

        $fromNum = intval($this->from_number);
        $nextNum = $fromNum + $this->processed_count;

        return ($this->prefix ?? '') . $nextNum . ($this->suffix ?? '');
    }

    /**
     * Đánh dấu bắt đầu xử lý
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => ImportStatus::PROCESSING,
            'started_at' => now()
        ]);
    }

    /**
     * Đánh dấu hoàn thành
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => ImportStatus::COMPLETED,
            'completed_at' => now()
        ]);
    }

    /**
     * Đánh dấu lỗi
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => ImportStatus::FAILED,
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Cập nhật tiến trình xử lý
     */
    public function updateProgress(int $processedCount, string $lastProcessedSerial): void
    {
        $this->update([
            'processed_count' => $processedCount,
            'last_processed_serial' => $lastProcessedSerial
        ]);

        // Tự động đánh dấu hoàn thành nếu đã xử lý hết
        if ($processedCount >= $this->total_quantity) {
            $this->markAsCompleted();
        }
    }

    /**
     * Lấy danh sách tất cả status
     */
    public static function getStatusList(): array
    {
        return ImportStatus::getStatusList();
    }

    /**
     * Lấy text của status hiện tại
     */
    public function getStatusText(): string
    {
        return $this->status?->getLabel() ?? 'Không xác định';
    }

    /**
     * Lấy CSS class cho status badge
     */
    public function getStatusBadgeClass(): string
    {
        return $this->status?->getBadgeClass() ?? 'badge-secondary';
    }

    /**
     * Kiểm tra xem import có thể được cập nhật không
     */
    public function canBeUpdated(): bool
    {
        return $this->status === ImportStatus::COMPLETED;
    }

    /**
     * Tính toán số lượng phôi sẽ thay đổi khi update
     */
    public function calculateQuantityChange(string $newFromNumber, string $newToNumber): int
    {
        $currentQuantity = intval($this->to_number) - intval($this->from_number) + 1;
        $newQuantity = intval($newToNumber) - intval($newFromNumber) + 1;

        return $newQuantity - $currentQuantity;
    }

    /**
     * Lấy danh sách serial numbers hiện tại của import này
     */
    public function getCurrentSerialNumbers(): array
    {
        $serialNumbers = [];
        $fromNum = intval($this->from_number);
        $toNum = intval($this->to_number);
        $prefix = $this->prefix ?? '';
        $suffix = $this->suffix ?? '';

        for ($num = $fromNum; $num <= $toNum; $num++) {
            $paddedNum = str_pad($num, strlen($this->from_number), '0', STR_PAD_LEFT);
            $serialNumbers[] = $prefix . $paddedNum . $suffix;
        }

        return $serialNumbers;
    }
}
