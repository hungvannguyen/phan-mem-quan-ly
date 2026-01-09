<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model wrapper cho degree_adjustments view
 * View này lọc dữ liệu từ bảng change_logs cho entity_type = 'Degree'
 *
 * @deprecated Sử dụng ChangeLog::forEntity('Degree', $degreeId) để lấy logs của degree
 */
class DegreeAdjustment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Đây là một view, không phải bảng thực
     */
    protected $table = 'degree_adjustments';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'adjustment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'degree_id',
        'adjusted_field',
        'old_value',
        'new_value',
        'adjustment_content',
        'decision_number',
        'decision_date',
        'adjusted_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'adjustment_content' => 'string',
            'decision_number' => 'string',
            'decision_date' => 'date',
        ];
    }

    /**
     * Get the degree that this adjustment belongs to.
     */
    public function degree()
    {
        return $this->belongsTo(Degree::class, 'degree_id', 'degree_id');
    }

    /**
     * Get the user who made this adjustment.
     */
    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by', 'user_id');
    }

    /**
     * NOTE: Model này chỉ dùng để đọc dữ liệu (read-only)
     * Để tạo log mới cho degree, sử dụng:
     *
     * ChangeLog::logChange(
     *     entityType: 'Degree',
     *     entityId: $degreeId,
     *     changeDescription: 'Mô tả thay đổi',
     *     changedField: 'field_name',
     *     oldValue: 'giá trị cũ',
     *     newValue: 'giá trị mới',
     *     decisionNumber: 'số quyết định',
     *     decisionDate: 'ngày quyết định'
     * );
     */
}
