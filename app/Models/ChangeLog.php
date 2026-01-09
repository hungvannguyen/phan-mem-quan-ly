<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model để lưu lịch sử thay đổi cho tất cả các entity
 */
class ChangeLog extends Model
{
    protected $table = 'change_logs';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'changed_field',
        'old_value',
        'new_value',
        'change_description',
        'decision_number',
        'decision_date',
        'changed_by',
        'action_type',
        'ip_address',
        'user_agent',
        'additional_data'
    ];

    protected $casts = [
        'decision_date' => 'date',
        'additional_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Polymorphic relationship với entity được thay đổi
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Người thực hiện thay đổi
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by', 'user_id');
    }

    /**
     * Scope để lọc theo entity type
     */
    public function scopeForEntity($query, string $entityType, string|int $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    /**
     * Scope để lọc theo action type
     */
    public function scopeByAction($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope để lọc theo người thực hiện
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Helper method để tạo log mới
     */
    public static function logChange(
        string $entityType,
        string|int $entityId,
        string $changeDescription,
        ?string $changedField = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $decisionNumber = null,
        ?string $decisionDate = null,
        ?string $changedBy = null,
        string $actionType = 'update',
        ?array $additionalData = null
    ): self {
        return self::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changed_field' => $changedField,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'change_description' => $changeDescription,
            'decision_number' => $decisionNumber,
            'decision_date' => $decisionDate,
            'changed_by' => $changedBy ?? auth()->id(),
            'action_type' => $actionType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'additional_data' => $additionalData
        ]);
    }

    /**
     * Helper method để lấy tất cả logs của một entity
     */
    public static function getEntityLogs(string $entityType, string|int $entityId)
    {
        return self::forEntity($entityType, $entityId)
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
