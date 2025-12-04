<?php

namespace Database\Factories;

use App\Models\DiplomaBlankType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiplomaBlankType>
 */
class DiplomaBlankTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate unique names to avoid conflicts when called multiple times
        return [
            'type_name' => $this->faker->words(3, true) . ' ' . $this->faker->randomNumber(3),
            'prefix' => $this->faker->regexify('[A-Z]{2,3}'),
        ];
    }

    /**
     * Bằng cử nhân
     */
    public function bachelor(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng cử nhân',
            'prefix' => 'CN',
        ]);
    }

    /**
     * Bằng kỹ sư
     */
    public function engineer(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng kỹ sư',
            'prefix' => 'KS',
        ]);
    }

    /**
     * Bằng thạc sĩ
     */
    public function master(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng thạc sĩ',
            'prefix' => 'THS',
        ]);
    }

    /**
     * Bằng tiến sĩ
     */
    public function doctor(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng tiến sĩ',
            'prefix' => 'TS',
        ]);
    }

    /**
     * Bằng tốt nghiệp trung cấp lý luận chính trị
     */
    public function intermediatePolitical(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng tốt nghiệp trung cấp lý luận chính trị',
            'prefix' => 'TCLLCT',
        ]);
    }

    /**
     * Bằng tốt nghiệp cao cấp lý luận chính trị
     */
    public function advancedPolitical(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng tốt nghiệp cao cấp lý luận chính trị',
            'prefix' => 'CCLLCT',
        ]);
    }

    /**
     * Chứng chỉ nghiệp vụ 6 tháng
     */
    public function sixMonthCertificate(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Chứng chỉ nghiệp vụ 6 tháng',
            'prefix' => 'CCNV6T',
        ]);
    }

    /**
     * Chứng chỉ tương đương trung cấp lý luận chính trị
     */
    public function equivalentIntermediatePolitical(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Chứng chỉ tương đương trung cấp lý luận chính trị',
            'prefix' => 'CCTDTCLLCT',
        ]);
    }

    /**
     * Chứng chỉ QSVT 45 ngày
     */
    public function militaryCertificate(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Chứng chỉ QSVT 45 ngày',
            'prefix' => 'CCQSVT',
        ]);
    }

    /**
     * Chứng chỉ bổ sung kiến thức
     */
    public function knowledgeSupplementCertificate(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Chứng chỉ bổ sung kiến thức',
            'prefix' => 'CCBSKT',
        ]);
    }

    /**
     * Chứng chỉ bồi dưỡng khác
     */
    public function otherTrainingCertificate(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Chứng chỉ bồi dưỡng khác',
            'prefix' => 'CCBDK',
        ]);
    }
}