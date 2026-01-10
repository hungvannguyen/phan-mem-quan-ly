<?php

namespace App\Enums;

enum DiplomaBlankStatus: string
{
    case IN_STOCK = 'InStock';
    case ISSUED = 'Issued';
    case RECALLED = 'Recalled';
    case DAMAGED = 'Damaged';
    case DESTROYED = 'Destroyed';

    /**
     * Lấy danh sách tất cả status với label tiếng Việt
     */
    public static function getStatusList(): array
    {
        return [
            self::IN_STOCK->value => 'Trong kho',
            self::ISSUED->value => 'Đã cấp',
            self::RECALLED->value => 'Đã thu hồi',
            self::DAMAGED->value => 'Hư hỏng',
            self::DESTROYED->value => 'Đã hủy'
        ];
    }

    /**
     * Lấy label tiếng Việt của status
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::IN_STOCK => 'Trong kho',
            self::ISSUED => 'Đã cấp',
            self::RECALLED => 'Đã thu hồi',
            self::DAMAGED => 'Hư hỏng',
            self::DESTROYED => 'Đã hủy'
        };
    }

    /**
     * Lấy CSS class cho status badge
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::IN_STOCK => 'status-pending',
            self::ISSUED => 'status-completed',
            self::RECALLED => 'status-processing',
            self::DAMAGED => 'status-failed',
            self::DESTROYED => 'status-canceled'
        };
    }

    /**
     * Kiểm tra xem status có thể cấp phôi không
     */
    public function canIssue(): bool
    {
        return $this === self::IN_STOCK;
    }

    /**
     * Kiểm tra xem status có thể thu hồi không
     */
    public function canRecall(): bool
    {
        return $this === self::ISSUED;
    }

    /**
     * Kiểm tra xem status có thể báo hư hỏng không
     */
    public function canMarkAsDamaged(): bool
    {
        return $this !== self::DAMAGED;
    }
}
