<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $settings = [
            ['key' => SystemSetting::SCHOOL_NAME, 'value' => 'Trường Đại học ABC'],
            ['key' => SystemSetting::ADDRESS, 'value' => $this->faker->address()],
            ['key' => SystemSetting::PHONE, 'value' => $this->faker->phoneNumber()],
            ['key' => SystemSetting::EMAIL, 'value' => $this->faker->companyEmail()],
            ['key' => SystemSetting::WEBSITE, 'value' => $this->faker->url()],
        ];

        $setting = $this->faker->randomElement($settings);

        return [
            'setting_key' => $setting['key'],
            'setting_value' => $setting['value'],
        ];
    }

    /**
     * Create school name setting.
     */
    public function schoolName(string $name = 'Trường Đại học ABC'): static
    {
        return $this->state(fn(array $attributes) => [
            'setting_key' => SystemSetting::SCHOOL_NAME,
            'setting_value' => $name,
        ]);
    }

    /**
     * Create address setting.
     */
    public function address(string $address = null): static
    {
        return $this->state(fn(array $attributes) => [
            'setting_key' => SystemSetting::ADDRESS,
            'setting_value' => $address ?? $this->faker->address(),
        ]);
    }

    /**
     * Create phone setting.
     */
    public function phone(string $phone = null): static
    {
        return $this->state(fn(array $attributes) => [
            'setting_key' => SystemSetting::PHONE,
            'setting_value' => $phone ?? $this->faker->phoneNumber(),
        ]);
    }

    /**
     * Create email setting.
     */
    public function email(string $email = null): static
    {
        return $this->state(fn(array $attributes) => [
            'setting_key' => SystemSetting::EMAIL,
            'setting_value' => $email ?? $this->faker->companyEmail(),
        ]);
    }
}
