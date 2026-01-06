<?php

namespace Database\Factories;

use App\Models\DegreeAdjustment;
use App\Models\Degree;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DegreeAdjustment>
 */
class DegreeAdjustmentFactory extends Factory
{
    /**
     * Danh sách các trường có thể điều chỉnh (chỉ các trường thuộc bảng degrees)
     */
    private const ADJUSTABLE_FIELDS = [
        'registration_number' => 'Số đăng ký',
        'degree_type' => 'Loại văn bằng',
        'major_name' => 'Ngành/Chuyên ngành',
        'ranking' => 'Xếp loại',
        'granting_date' => 'Ngày cấp',
        'graduation_year' => 'Năm tốt nghiệp',
        'decision_number' => 'Số quyết định',
        'council_decision_number' => 'Số QĐ thành lập hội đồng',
        'council_decision_date' => 'Ngày QĐ thành lập hội đồng',
        'graduation_decision_number' => 'Số QĐ công nhận tốt nghiệp',
        'graduation_decision_date' => 'Ngày QĐ công nhận tốt nghiệp',
        'defense_date' => 'Ngày bảo vệ',
        'training_start_date' => 'Ngày bắt đầu đào tạo',
        'training_end_date' => 'Ngày kết thúc đào tạo',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $decisionDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $adjustedField = $this->faker->randomElement(array_keys(self::ADJUSTABLE_FIELDS));

        // Generate sample old and new values based on field type
        [$oldValue, $newValue] = $this->generateSampleValues($adjustedField);

        // Generate content matching the adjusted field
        $adjustmentContent = $this->generateAdjustmentContent($adjustedField, $oldValue, $newValue);

        return [
            'degree_id' => Degree::factory(),
            'adjusted_field' => $adjustedField,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'adjustment_content' => $adjustmentContent,
            'decision_number' => $this->faker->regexify('[0-9]{3}/QĐ-DC-[0-9]{4}'),
            'decision_date' => $decisionDate->format('Y-m-d'),
            'adjusted_by' => User::factory(),
        ];
    }

    /**
     * Create adjustment for specific degree.
     */
    public function forDegree(Degree $degree): static
    {
        return $this->state(fn(array $attributes) => [
            'degree_id' => $degree->degree_id,
        ]);
    }

    /**
     * Create adjustment by specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'adjusted_by' => $user->user_id,
        ]);
    }

    /**
     * Create adjustment with specific content.
     */
    public function withContent(string $content): static
    {
        return $this->state(fn(array $attributes) => [
            'adjustment_content' => $content,
        ]);
    }

    /**
     * Generate sample old and new values based on field type
     */
    private function generateSampleValues(string $field): array
    {
        return match ($field) {
            'registration_number' => [
                $this->faker->regexify('[A-Z]{2}[0-9]{4}[0-9]{6}'),
                $this->faker->regexify('[A-Z]{2}[0-9]{4}[0-9]{6}')
            ],
            'degree_type' => [
                $this->faker->randomElement(['bachelor', 'master', 'doctor']),
                $this->faker->randomElement(['master', 'doctor', 'bachelor'])
            ],
            'major_name' => [
                $this->faker->randomElement(['Công nghệ thông tin', 'Kỹ thuật phần mềm', 'Quản trị kinh doanh']),
                $this->faker->randomElement(['Khoa học máy tính', 'Hệ thống thông tin', 'Quản trị doanh nghiệp'])
            ],
            'ranking' => [
                $this->faker->randomElement(['Khá', 'Trung bình', 'Trung bình khá']),
                $this->faker->randomElement(['Giỏi', 'Khá', 'Xuất sắc'])
            ],
            'granting_date' => [
                $this->faker->date('d/m/Y'),
                $this->faker->date('d/m/Y')
            ],
            'graduation_year' => [
                $this->faker->year(),
                $this->faker->year()
            ],
            'decision_number', 'council_decision_number', 'graduation_decision_number' => [
                $this->faker->regexify('[0-9]{3}/QĐ-[0-9]{4}'),
                $this->faker->regexify('[0-9]{3}/QĐ-[0-9]{4}')
            ],
            'council_decision_date', 'graduation_decision_date', 'defense_date',
            'training_start_date', 'training_end_date' => [
                $this->faker->date('d/m/Y'),
                $this->faker->date('d/m/Y')
            ],
            default => ['Giá trị cũ', 'Giá trị mới']
        };
    }

    /**
     * Generate adjustment content matching the adjusted field
     */
    private function generateAdjustmentContent(string $field, string $oldValue, string $newValue): string
    {
        return match ($field) {
            'registration_number' => "Điều chỉnh số đăng ký văn bằng từ '{$oldValue}' sang '{$newValue}' do phát hiện sai sót trong hồ sơ gốc hoặc theo quyết định điều chỉnh của Hiệu trưởng.",

            'degree_type' => "Cập nhật loại văn bằng từ '{$oldValue}' sang '{$newValue}' theo quyết định điều chỉnh chương trình đào tạo.",

            'major_name' => "Điều chỉnh tên ngành đào tạo từ '{$oldValue}' sang '{$newValue}' theo quyết định điều chỉnh chương trình đào tạo và danh mục ngành.",

            'ranking' => "Điều chỉnh xếp loại tốt nghiệp từ '{$oldValue}' lên '{$newValue}' sau khi hội đồng xét tốt nghiệp phúc khảo và công nhận lại kết quả.",

            'granting_date' => "Thay đổi ngày cấp bằng từ '{$oldValue}' sang '{$newValue}' theo quyết định điều chỉnh của Hội đồng trường về thời gian tốt nghiệp.",

            'graduation_year' => "Điều chỉnh năm tốt nghiệp từ '{$oldValue}' sang '{$newValue}' theo quyết định điều chỉnh thời gian hoàn thành chương trình.",

            'decision_number' => "Cập nhật số quyết định từ '{$oldValue}' sang '{$newValue}' theo văn bản điều chỉnh của cơ quan có thẩm quyền.",

            'council_decision_number' => "Cập nhật số quyết định thành lập hội đồng đánh giá từ '{$oldValue}' sang '{$newValue}' theo quyết định bổ sung của Hiệu trưởng.",

            'council_decision_date' => "Điều chỉnh ngày quyết định thành lập hội đồng từ '{$oldValue}' sang '{$newValue}' theo văn bản điều chỉnh.",

            'graduation_decision_number' => "Thay đổi số quyết định công nhận tốt nghiệp từ '{$oldValue}' sang '{$newValue}' sau khi phát hiện sai sót trong văn bản gốc.",

            'graduation_decision_date' => "Cập nhật ngày quyết định công nhận tốt nghiệp từ '{$oldValue}' sang '{$newValue}' theo quyết định điều chỉnh.",

            'defense_date' => "Điều chỉnh ngày bảo vệ luận văn từ '{$oldValue}' sang '{$newValue}' theo biên bản họp hội đồng.",

            'training_start_date' => "Cập nhật ngày bắt đầu đào tạo từ '{$oldValue}' sang '{$newValue}' sau khi đối chiếu với hồ sơ nhập học.",

            'training_end_date' => "Điều chỉnh ngày kết thúc đào tạo từ '{$oldValue}' sang '{$newValue}' do sinh viên hoàn thành chương trình đào tạo sớm hơn dự kiến.",

            default => "Điều chỉnh thông tin văn bằng từ '{$oldValue}' sang '{$newValue}' theo yêu cầu cập nhật dữ liệu."
        };
    }
}
