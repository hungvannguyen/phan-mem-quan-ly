<?php

namespace App\Enums;

enum DegreeStatus: string
{
    case NOT_ISSUED = 'NotIssued';
    case ISSUED = 'Issued';
    case RECALLED = 'Recalled';
    case CANCELLED = 'Cancelled';

    /**
     * Lấy danh sách tất cả status với label tiếng Việt
     */
    public static function getStatusList(): array
    {
        return [
            self::NOT_ISSUED->value => 'Chưa cấp',
            self::ISSUED->value => 'Đã cấp',
            self::RECALLED->value => 'Thu hồi',
            self::CANCELLED->value => 'Hủy',
        ];
    }

    /**
     * Lấy label tiếng Việt của status
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::NOT_ISSUED => 'Chưa cấp',
            self::ISSUED => 'Đã cấp',
            self::RECALLED => 'Thu hồi',
            self::CANCELLED => 'Hủy',
        };
    }

    /**
     * Lấy CSS class cho status badge
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::NOT_ISSUED => 'status-pending',
            self::ISSUED => 'status-completed',
            self::RECALLED => 'status-processing',
            self::CANCELLED => 'status-cancelled',
        };
    }

    /**
     * Lấy icon cho status
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::NOT_ISSUED => 'clock',
            self::ISSUED => 'check-circle',
            self::RECALLED => 'x-circle',
            self::CANCELLED => 'slash-circle',
        };
    }

    /**
     * Kiểm tra có thể cấp văn bằng không
     */
    public function canIssue(): bool
    {
        return $this === self::NOT_ISSUED;
    }

    /**
     * Kiểm tra có thể thu hồi không
     */
    public function canRecall(): bool
    {
        return $this === self::ISSUED;
    }

    /**
     * Kiểm tra đã được cấp chưa
     */
    public function isIssued(): bool
    {
        return $this === self::ISSUED;
    }

    /**
     * Kiểm tra đã bị thu hồi chưa
     */
    public function isRecalled(): bool
    {
        return $this === self::RECALLED;
    }

    /**
     * Kiểm tra đã bị hủy chưa
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}
