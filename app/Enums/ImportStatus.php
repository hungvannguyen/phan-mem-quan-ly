<?php

namespace App\Enums;

enum ImportStatus: int
{
    case PENDING = 0;
    case PROCESSING = 1;
    case COMPLETED = 2;
    case FAILED = 3;

    /**
     * Lấy danh sách tất cả status với label
     */
    public static function getStatusList(): array
    {
        return [
            self::PENDING->value => 'Chờ xử lý',
            self::PROCESSING->value => 'Đang xử lý',
            self::COMPLETED->value => 'Hoàn thành',
            self::FAILED->value => 'Lỗi'
        ];
    }

    /**
     * Lấy label của status
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ xử lý',
            self::PROCESSING => 'Đang xử lý',
            self::COMPLETED => 'Hoàn thành',
            self::FAILED => 'Lỗi'
        };
    }

    /**
     * Lấy CSS class cho status badge
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-warning',
            self::PROCESSING => 'badge-info',
            self::COMPLETED => 'badge-success',
            self::FAILED => 'badge-danger'
        };
    }

    /**
     * Lấy màu sắc cho status
     */
    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => '#ffc107',
            self::PROCESSING => '#17a2b8',
            self::COMPLETED => '#28a745',
            self::FAILED => '#dc3545'
        };
    }

    /**
     * Kiểm tra xem có phải là trạng thái hoàn thành không
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Kiểm tra xem có phải là trạng thái đang xử lý không
     */
    public function isProcessing(): bool
    {
        return $this === self::PROCESSING;
    }

    /**
     * Kiểm tra xem có phải là trạng thái chờ xử lý không
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Kiểm tra xem có phải là trạng thái lỗi không
     */
    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    /**
     * Lấy các trạng thái có thể chuyển đổi từ trạng thái hiện tại
     */
    public function getNextValidStatuses(): array
    {
        return match ($this) {
            self::PENDING => [self::PROCESSING, self::FAILED],
            self::PROCESSING => [self::COMPLETED, self::FAILED],
            self::COMPLETED => [], // Không thể chuyển từ completed
            self::FAILED => [self::PENDING] // Có thể retry
        };
    }

    /**
     * Kiểm tra xem có thể chuyển sang trạng thái khác không
     */
    public function canTransitionTo(ImportStatus $newStatus): bool
    {
        return in_array($newStatus, $this->getNextValidStatuses());
    }
}