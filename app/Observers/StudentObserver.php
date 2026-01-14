<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

class StudentObserver
{
    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        ChangeLog::create([
            'entity_type' => class_basename(Student::class),
            'entity_id' => $student->student_id,
            'change_description' => 'Tạo mới sinh viên',
            'action_type' => 'create',
            'changed_by' => Auth::id(),
        ]);
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        // Chỉ log khi có thay đổi thực sự
        if ($student->isDirty()) {
            $changes = $student->getDirty();
            $original = $student->getOriginal();

            foreach ($changes as $field => $newValue) {
                // Bỏ qua các trường không cần log
                if ($this->shouldSkipField($field)) {
                    continue;
                }

                $oldValue = $original[$field] ?? null;

                // Chỉ log nếu giá trị thực sự thay đổi
                if ($oldValue != $newValue) {
                    ChangeLog::create([
                        'entity_type' => class_basename(Student::class),
                        'entity_id' => $student->student_id,
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
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        ChangeLog::create([
            'entity_type' => class_basename(Student::class),
            'entity_id' => $student->student_id,
            'change_description' => 'Xóa sinh viên',
            'action_type' => 'delete',
            'changed_by' => Auth::id(),
        ]);
    }

    /**
     * Handle the Student "restored" event.
     */
    public function restored(Student $student): void
    {
        ChangeLog::create([
            'entity_type' => class_basename(Student::class),
            'entity_id' => $student->student_id,
            'change_description' => 'Khôi phục sinh viên',
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
            'training_type' => 'hình thức đào tạo',
        ];

        $fieldLabel = $fieldLabels[$field] ?? $field;
        return "Cập nhật {$fieldLabel}";
    }
}
