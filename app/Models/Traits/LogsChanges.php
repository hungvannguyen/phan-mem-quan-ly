<?php

namespace App\Models\Traits;

use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

/**
 * Trait để tự động ghi log lịch sử thay đổi
 */
trait LogsChanges
{
    /**
     * Flag to temporarily disable logging
     */
    protected $loggingEnabled = true;

    /**
     * Boot the trait
     */
    protected static function bootLogsChanges()
    {
        // Log khi tạo mới
        static::created(function ($model) {
            $model->logCreation();
        });

        // Log khi update
        static::updating(function ($model) {
            $model->logUpdate();
        });

        // Log khi xóa
        static::deleted(function ($model) {
            $model->logDeletion();
        });

        // Log khi restore (nếu dùng SoftDeletes)
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logRestoration();
            });
        }
    }

    /**
     * Disable logging temporarily
     */
    public function disableLogging()
    {
        $this->loggingEnabled = false;
        return $this;
    }

    /**
     * Enable logging
     */
    public function enableLogging()
    {
        $this->loggingEnabled = true;
        return $this;
    }

    /**
     * Check if logging should happen
     */
    protected function shouldLogChanges(): bool
    {
        return $this->loggingEnabled;
    }

    /**
     * Log khi tạo mới entity
     */
    protected function logCreation()
    {
        if ($this->shouldLogChanges()) {
            ChangeLog::logChange(
                entityType: $this->getEntityType(),
                entityId: $this->getKey(),
                changeDescription: $this->getCreationDescription(),
                actionType: 'create',
                additionalData: $this->getLoggableAttributes()
            );
        }
    }

    /**
     * Log khi update entity
     */
    protected function logUpdate()
    {
        if ($this->shouldLogChanges() && $this->isDirty()) {
            $changes = $this->getDirty();
            $original = $this->getOriginal();

            foreach ($changes as $field => $newValue) {
                // Bỏ qua các trường không cần log
                if ($this->shouldSkipField($field)) {
                    continue;
                }

                $oldValue = $original[$field] ?? null;

                ChangeLog::logChange(
                    entityType: $this->getEntityType(),
                    entityId: $this->getKey(),
                    changeDescription: $this->getUpdateDescription($field, $oldValue, $newValue),
                    changedField: $field,
                    oldValue: $this->formatValueForLog($field, $oldValue),
                    newValue: $this->formatValueForLog($field, $newValue),
                    actionType: 'update'
                );
            }
        }
    }

    /**
     * Log khi xóa entity
     */
    protected function logDeletion()
    {
        if ($this->shouldLogChanges()) {
            ChangeLog::logChange(
                entityType: $this->getEntityType(),
                entityId: $this->getKey(),
                changeDescription: $this->getDeletionDescription(),
                actionType: 'delete'
            );
        }
    }

    /**
     * Log khi restore entity
     */
    protected function logRestoration()
    {
        if ($this->shouldLogChanges()) {
            ChangeLog::logChange(
                entityType: $this->getEntityType(),
                entityId: $this->getKey(),
                changeDescription: $this->getRestorationDescription(),
                actionType: 'restore'
            );
        }
    }

    /**
     * Lấy entity type
     */
    protected function getEntityType(): string
    {
        return class_basename($this);
    }

    /**
     * Kiểm tra có nên bỏ qua field này không
     */
    protected function shouldSkipField(string $field): bool
    {
        $skipFields = array_merge(
            ['updated_at', 'created_at', 'deleted_at', 'remember_token'],
            $this->dontLogFields ?? []
        );

        return in_array($field, $skipFields);
    }

    /**
     * Format giá trị để log
     */
    protected function formatValueForLog(string $field, $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        // Nếu là date field
        if ($this->isDateAttribute($field)) {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        }

        // Nếu là enum (phải check là object trước)
        if (is_object($value) && method_exists($value, 'label')) {
            return $value->label();
        }

        // Nếu là boolean
        if (is_bool($value)) {
            return $value ? 'Có' : 'Không';
        }

        // Map giá trị degree_type sang tiếng Việt
        if ($field === 'degree_type' && is_string($value)) {
            $degreeTypeMap = [
                'certificate' => 'Chứng chỉ',
                'bachelor' => 'Cử nhân',
                'engineer' => 'Kỹ sư',
                'master' => 'Thạc sĩ',
                'doctor' => 'Tiến sĩ',
            ];
            return $degreeTypeMap[strtolower($value)] ?? $value;
        }

        // Map giá trị ranking sang tiếng Việt
        if ($field === 'ranking' && is_string($value)) {
            $rankingMap = [
                'excellent' => 'Xuất sắc',
                'very_good' => 'Giỏi',
                'good' => 'Khá',
                'average' => 'Trung bình',
                'below_average' => 'Trung bình khá',
            ];
            return $rankingMap[strtolower($value)] ?? $value;
        }

        return (string) $value;
    }

    /**
     * Lấy các attributes có thể log
     */
    protected function getLoggableAttributes(): array
    {
        $attributes = $this->getAttributes();

        foreach ($this->dontLogFields ?? [] as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    /**
     * Relationship với change logs
     * Dùng hasMany thay vì morphMany vì entity_type lưu class basename
     */
    public function changeLogs()
    {
        return $this->hasMany(ChangeLog::class, 'entity_id', $this->getKeyName())
            ->where('entity_type', class_basename($this))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Lấy lịch sử thay đổi
     */
    public function getChangeHistory()
    {
        return $this->changeLogs()->with('changedBy')->get();
    }

    /**
     * Các method mô tả có thể override trong model
     */
    protected function getCreationDescription(): string
    {
        return "Tạo mới {$this->getEntityTypeLabel()}";
    }

    protected function getUpdateDescription(string $field, $oldValue, $newValue): string
    {
        $fieldLabel = $this->getFieldLabel($field);
        return "Cập nhật {$fieldLabel}";
    }

    protected function getDeletionDescription(): string
    {
        return "Xóa {$this->getEntityTypeLabel()}";
    }

    protected function getRestorationDescription(): string
    {
        return "Khôi phục {$this->getEntityTypeLabel()}";
    }

    protected function getEntityTypeLabel(): string
    {
        return strtolower($this->getEntityType());
    }

    protected function getFieldLabel(string $field): string
    {
        // Map field names to Vietnamese labels
        $labels = array_merge([
            'student_code' => 'mã sinh viên',
            'full_name' => 'họ tên',
            'date_of_birth' => 'ngày sinh',
            'class_name' => 'lớp học',
            'course' => 'khóa',
            'academic_year' => 'niên khóa',
            'major_id' => 'ngành đào tạo',
            'place_of_birth' => 'nơi sinh',
            'hometown' => 'quê quán',
            'place_of_origin' => 'nguyên quán',
            'gender' => 'giới tính',
            'nation' => 'dân tộc',
            'nationality' => 'quốc tịch',
            'number_in_the_book' => 'số sổ gốc',
            'status' => 'trạng thái',
        ], $this->fieldLabels ?? []);

        return $labels[$field] ?? $field;
    }
}
