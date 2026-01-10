<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Degree;
use App\Models\DegreeReissue;
use App\Models\DiplomaBlank;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Support\Facades\DB;

class DegreeReissueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo dữ liệu mẫu cho lịch sử cấp lại văn bằng
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Lấy một số degrees có diploma_blank_id để tạo lịch sử cấp lại
            $degrees = Degree::with(['student', 'diplomaBlank.type'])
                ->whereNotNull('diploma_blank_id')
                ->inRandomOrder()
                ->limit(15)
                ->get();

            $editReasons = [
                'Sửa lỗi chính tả trong họ và tên',
                'Điều chỉnh ngày sinh theo giấy khai sinh',
                'Cập nhật tên ngành đào tạo mới',
                'Sửa lỗi in ấn trên văn bằng',
                'Thay đổi xếp loại tốt nghiệp theo quyết định mới',
                'Điều chỉnh năm tốt nghiệp',
                'Bổ sung thông tin chuyên ngành',
                'Sửa lỗi số hiệu văn bằng',
                'Cập nhật theo quyết định hội đồng',
                'Thay thế văn bằng bị hư hỏng',
                'Cấp lại do mất văn bằng gốc',
                'Điều chỉnh thông tin theo hồ sơ',
            ];

            $decisionPrefixes = ['QĐ-HVANND-CL', 'QĐ-BGH-CL', 'QĐ-ĐHQG-CL'];
            $totalReissues = 0;

            foreach ($degrees as $degree) {
                if (!$degree->diplomaBlank || !$degree->diplomaBlank->type_id) {
                    continue;
                }

                // Mỗi văn bằng có 1-3 lần cấp lại
                $reissueCount = rand(1, 3);
                $typeId = $degree->diplomaBlank->type_id;
                $currentBlankId = $degree->diploma_blank_id;

                for ($i = 0; $i < $reissueCount; $i++) {
                    // Tìm phôi mới từ kho (cùng loại, chỉ lấy phôi còn IN_STOCK)
                    $newBlank = DiplomaBlank::where('type_id', $typeId)
                        ->where('status', DiplomaBlankStatus::IN_STOCK->value)
                        ->where('diploma_blank_id', '!=', $currentBlankId)
                        ->inRandomOrder()
                        ->first();

                    if (!$newBlank) {
                        // Không còn phôi trong kho, dừng tạo thêm lần cấp lại
                        break;
                    }

                    // Ngày quyết định
                    $decisionDate = now()->subMonths(rand(1, 24));

                    // Tạo số quyết định
                    $decisionPrefix = collect($decisionPrefixes)->random();
                    $decisionNumber = $decisionPrefix . '-' . $decisionDate->format('Y') . '-' 
                        . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

                    // Lý do cấp lại
                    $editContent = collect($editReasons)->random();

                    // Random trạng thái xử lý phôi cũ: 40% thu hồi, 30% hủy, 30% chưa thu hồi
                    $statusRandom = rand(1, 100);
                    $isRecalled = false;
                    $isDestroyed = false;
                    
                    if ($statusRandom <= 40) {
                        // 40% đã thu hồi
                        $isRecalled = true;
                    } elseif ($statusRandom <= 70) {
                        // 30% đã hủy
                        $isDestroyed = true;
                    }
                    // 30% còn lại: chưa thu hồi (giữ nguyên trạng thái)

                    // Ghi chú (40% có ghi chú)
                    $notes = rand(0, 100) <= 40 ? collect([
                        'Đã xác minh thông tin với phòng đào tạo',
                        'Sinh viên đã nộp đầy đủ hồ sơ',
                        'Đã thu hồi văn bằng cũ',
                        'Văn bằng mới đã được in và đóng dấu',
                        'Đã cập nhật vào hệ thống quản lý',
                    ])->random() : null;

                    DegreeReissue::create([
                        'degree_id' => $degree->degree_id,
                        'old_diploma_blank_id' => $currentBlankId,
                        'new_diploma_blank_id' => $newBlank->diploma_blank_id,
                        'edit_content' => $editContent,
                        'recall_decision' => $decisionNumber,
                        'decision_date' => $decisionDate,
                        'is_recalled' => $isRecalled,
                        'is_destroyed' => $isDestroyed,
                        'notes' => $notes,
                        'created_at' => $decisionDate->copy()->addDays(rand(1, 7)),
                        'updated_at' => $decisionDate->copy()->addDays(rand(1, 7)),
                    ]);

                    // Cập nhật trạng thái phôi cũ
                    $oldBlank = DiplomaBlank::find($currentBlankId);
                    if ($oldBlank) {
                        if ($isDestroyed) {
                            $oldBlank->update(['status' => DiplomaBlankStatus::DESTROYED->value]);
                        } elseif ($isRecalled) {
                            $oldBlank->update(['status' => DiplomaBlankStatus::RECALLED->value]);
                        }
                        // Nếu không phải recalled hay destroyed, giữ nguyên trạng thái ISSUED
                    }

                    // Cập nhật trạng thái phôi mới thành đã cấp
                    $newBlank->update(['status' => DiplomaBlankStatus::ISSUED->value]);

                    // Cập nhật degree's diploma_blank_id
                    $currentBlankId = $newBlank->diploma_blank_id;
                    
                    $totalReissues++;
                }

                // Cập nhật diploma_blank_id cuối cùng cho degree
                $degree->update(['diploma_blank_id' => $currentBlankId]);
            }

            $this->command->info('✓ Đã tạo ' . $totalReissues . ' lịch sử cấp lại văn bằng mẫu');
        });
    }
}
