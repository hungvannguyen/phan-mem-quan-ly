<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\ChangeLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class StudentUpdateSeeder extends Seeder
{
    /**
     * Seed student updates để test change logs
     */
    public function run(): void
    {
        // Lấy user đầu tiên để làm người thực hiện
        $user = User::first();

        if (!$user) {
            $this->command->warn('Không tìm thấy user. Vui lòng chạy UserSeeder trước.');
            return;
        }

        // Lấy 5 sinh viên đầu tiên
        $students = Student::take(5)->get();

        if ($students->isEmpty()) {
            $this->command->warn('Không tìm thấy sinh viên. Vui lòng chạy StudentSeeder trước.');
            return;
        }

        // Lưu event dispatcher để bật lại sau
        $dispatcher = Student::getEventDispatcher();

        $this->command->info('Bắt đầu seed student updates...');

        foreach ($students as $index => $student) {
            $studentNumber = $index + 1;
            $this->command->info("Cập nhật sinh viên #" . $studentNumber . ": {$student->full_name}");

            // Update 1: Thay đổi lớp học
            if ($index % 2 === 0) {
                $oldClass = $student->class_name;
                $newClass = $oldClass . ' (Chuyển lớp)';

                // Cập nhật student (tắt Observer để không tạo log tự động)
                Student::unsetEventDispatcher();
                $student->class_name = $newClass;
                $student->save();
                Student::setEventDispatcher($dispatcher);

                // Tạo log thủ công với đầy đủ thông tin quyết định
                ChangeLog::create([
                    'entity_type' => 'Student',
                    'entity_id' => $student->student_id,
                    'changed_field' => 'class_name',
                    'old_value' => $oldClass,
                    'new_value' => $newClass,
                    'change_description' => 'Chuyển lớp học',
                    'decision_number' => 'QĐ-HVANND-' . now()->year . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'decision_date' => now()->subDays(rand(30, 180)),
                    'changed_by' => $user->user_id,
                    'action_type' => 'update',
                ]);

                $this->command->info("  ✓ Đổi lớp: {$oldClass} → {$newClass}");
            }

            // Update 2: Thay đổi trạng thái
            if ($index === 0) {
                $oldStatus = $student->status->label();
                Student::unsetEventDispatcher();
                $student->status = \App\Enums\StudentStatus::Graduate;
                $student->save();
                Student::setEventDispatcher($dispatcher);

                ChangeLog::create([
                    'entity_type' => 'Student',
                    'entity_id' => $student->student_id,
                    'changed_field' => 'status',
                    'old_value' => $oldStatus,
                    'new_value' => 'Đã tốt nghiệp',
                    'change_description' => 'Thay đổi trạng thái học tập',
                    'decision_number' => 'QĐ-HVANND-' . now()->year . '-' . str_pad(100 + $index, 4, '0', STR_PAD_LEFT),
                    'decision_date' => now()->subDays(rand(30, 180)),
                    'changed_by' => $user->user_id,
                    'action_type' => 'update',
                ]);

                $this->command->info("  ✓ Đổi trạng thái: {$oldStatus} → Đã tốt nghiệp");
            }

            // Update 3: Thay đổi nhiều trường cùng lúc
            if ($index === 1) {
                $oldBirth = $student->place_of_birth;
                $oldHometown = $student->hometown;
                $oldOrigin = $student->place_of_origin;

                Student::unsetEventDispatcher();
                $student->update([
                    'place_of_birth' => 'Hà Nội',
                    'hometown' => 'Nam Định',
                    'place_of_origin' => 'Thanh Hóa',
                ]);
                Student::setEventDispatcher($dispatcher);

                // Tạo log cho từng trường
                $decisionDate = now()->subDays(rand(30, 180));
                $decisionNumber = 'QĐ-HVANND-' . now()->year . '-' . str_pad(200 + $index, 4, '0', STR_PAD_LEFT);

                ChangeLog::create([
                    'entity_type' => 'Student',
                    'entity_id' => $student->student_id,
                    'changed_field' => 'place_of_birth',
                    'old_value' => $oldBirth,
                    'new_value' => 'Hà Nội',
                    'change_description' => 'Cập nhật nơi sinh',
                    'decision_number' => $decisionNumber,
                    'decision_date' => $decisionDate,
                    'changed_by' => $user->user_id,
                    'action_type' => 'update',
                ]);

                ChangeLog::create([
                    'entity_type' => 'Student',
                    'entity_id' => $student->student_id,
                    'changed_field' => 'hometown',
                    'old_value' => $oldHometown,
                    'new_value' => 'Nam Định',
                    'change_description' => 'Cập nhật quê quán',
                    'decision_number' => $decisionNumber,
                    'decision_date' => $decisionDate,
                    'changed_by' => $user->user_id,
                    'action_type' => 'update',
                ]);

                ChangeLog::create([
                    'entity_type' => 'Student',
                    'entity_id' => $student->student_id,
                    'changed_field' => 'place_of_origin',
                    'old_value' => $oldOrigin,
                    'new_value' => 'Thanh Hóa',
                    'change_description' => 'Cập nhật nguyên quán',
                    'decision_number' => $decisionNumber,
                    'decision_date' => $decisionDate,
                    'changed_by' => $user->user_id,
                    'action_type' => 'update',
                ]);

                $this->command->info("  ✓ Cập nhật thông tin địa chỉ");
            }

            // Update 4: Thay đổi niên khóa
            if ($index === 2) {
                $oldYear = $student->academic_year;
                Student::unsetEventDispatcher();
                $student->academic_year = '2020-2024';
                $student->save();
                Student::setEventDispatcher($dispatcher);

                ChangeLog::create([
                    'entity_type' => 'Student',
                    'entity_id' => $student->student_id,
                    'changed_field' => 'academic_year',
                    'old_value' => $oldYear,
                    'new_value' => '2020-2024',
                    'change_description' => 'Thay đổi niên khóa',
                    'decision_number' => 'QĐ-HVANND-' . now()->year . '-' . str_pad(300 + $index, 4, '0', STR_PAD_LEFT),
                    'decision_date' => now()->subDays(rand(30, 180)),
                    'changed_by' => $user->user_id,
                    'action_type' => 'update',
                ]);

                $this->command->info("  ✓ Đổi niên khóa: {$oldYear} → 2020-2024");
            }

            // Update 5: Thay đổi số sổ gốc
            // Đã chuyển xử lý số sổ gốc sang Degree, không cập nhật ở Student nữa

            // Delay nhỏ để các timestamp khác nhau
            usleep(100000); // 0.1 second
        }

        // Test soft delete và restore
        if ($students->count() > 0) {
            $testStudent = $students->last();
            $this->command->info("\nTest soft delete và restore:");

            $studentName = $testStudent->full_name;
            Student::unsetEventDispatcher();
            $testStudent->delete();
            Student::setEventDispatcher($dispatcher);

            ChangeLog::create([
                'entity_type' => 'Student',
                'entity_id' => $testStudent->student_id,
                'change_description' => "Xóa sinh viên: {$studentName}",
                'decision_number' => 'QĐ-HVANND-' . now()->year . '-' . str_pad(500, 4, '0', STR_PAD_LEFT),
                'decision_date' => now()->subDays(rand(10, 60)),
                'changed_by' => $user->user_id,
                'action_type' => 'delete',
            ]);

            $this->command->info("  ✓ Xóa mềm sinh viên: {$studentName}");

            sleep(1);

            Student::unsetEventDispatcher();
            $testStudent->restore();
            Student::setEventDispatcher($dispatcher);

            ChangeLog::create([
                'entity_type' => 'Student',
                'entity_id' => $testStudent->student_id,
                'change_description' => "Khôi phục sinh viên: {$studentName}",
                'decision_number' => 'QĐ-HVANND-' . now()->year . '-' . str_pad(501, 4, '0', STR_PAD_LEFT),
                'decision_date' => now()->subDays(rand(1, 30)),
                'changed_by' => $user->user_id,
                'action_type' => 'restore',
            ]);

            $this->command->info("  ✓ Khôi phục sinh viên: {$studentName}");
        }

        $this->command->info("\n✅ Hoàn thành seed student updates!");
        $this->command->info("Kiểm tra change logs trong database hoặc trên giao diện edit student.");
    }
}
