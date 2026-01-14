<?php

namespace App\Observers;

use App\Models\Degree;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

class DegreeObserver
{
    /**
     * Handle the Degree "created" event.
     */
    public function created(Degree $degree): void
    {
        ChangeLog::create([
            'entity_type' => class_basename(Degree::class),
            'entity_id' => $degree->degree_id,
            'change_description' => 'Tạo mới văn bằng',
            'action_type' => 'create',
            'changed_by' => Auth::id(),
        ]);
    }

    /**
     * Handle the Degree "updated" event.
     */
    public function updated(Degree $degree): void
    {
        // Chỉ log khi có thay đổi thực sự
        if ($degree->isDirty()) {
            $changes = $degree->getDirty();
            $original = $degree->getOriginal();

            foreach ($changes as $field => $newValue) {
                // Bỏ qua các trường không cần log
                if ($this->shouldSkipField($field)) {
                    continue;
                }

                $oldValue = $original[$field] ?? null;

                // Chỉ log nếu giá trị thực sự thay đổi
                if ($oldValue != $newValue) {
                    ChangeLog::create([
                        'entity_type' => class_basename(Degree::class),
                        'entity_id' => $degree->degree_id,
                        'changed_field' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'change_description' => $this->getUpdateDescription($field, $oldValue, $newValue),
                        'action_type' => 'update',
                        'changed_by' => Auth::id(),
                        'additional_data' => null,
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Degree "deleted" event.
     */
    public function deleted(Degree $degree): void
    {
        ChangeLog::create([
            'entity_type' => class_basename(Degree::class),
            'entity_id' => $degree->degree_id,
            'change_description' => 'Xóa văn bằng',
            'action_type' => 'delete',
            'changed_by' => Auth::id(),
        ]);
    }

    /**
     * Handle the Degree "restored" event.
     */
    public function restored(Degree $degree): void
    {
        ChangeLog::create([
            'entity_type' => class_basename(Degree::class),
            'entity_id' => $degree->degree_id,
            'change_description' => 'Khôi phục văn bằng',
            'action_type' => 'restore',
            'changed_by' => Auth::id(),
        ]);
    }

    /**
     * Kiểm tra xem field có nên skip không
     */
    protected function shouldSkipField(string $field): bool
    {
        $skipFields = [
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        return in_array($field, $skipFields);
    }

    /**
     * Tạo mô tả cho update
     */
    protected function getUpdateDescription(string $field, $oldValue, $newValue): string
    {
        $fieldLabels = [
            'registration_number' => 'số đăng ký',
            'degree_type' => 'loại văn bằng',
            'major_name' => 'ngành/chuyên ngành',
            'ranking' => 'xếp loại',
            'granting_date' => 'ngày cấp',
            'graduation_year' => 'năm tốt nghiệp',
            'decision_number' => 'số quyết định',
            'council_decision_number' => 'số QĐ thành lập hội đồng',
            'council_decision_date' => 'ngày QĐ thành lập hội đồng',
            'graduation_decision_number' => 'số QĐ công nhận tốt nghiệp',
            'graduation_decision_date' => 'ngày QĐ công nhận tốt nghiệp',
            'defense_date' => 'ngày bảo vệ',
            'training_start_date' => 'ngày bắt đầu đào tạo',
            'training_end_date' => 'ngày kết thúc đào tạo',
            'diploma_blank_id' => 'phôi văn bằng',
        ];

        $fieldLabel = $fieldLabels[$field] ?? $field;
        return "Cập nhật {$fieldLabel}";
    }
}
