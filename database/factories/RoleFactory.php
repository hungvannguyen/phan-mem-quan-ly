<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = [
            ['name' => 'Admin', 'description' => 'Quản trị viên hệ thống'],
            ['name' => 'DiplomaManager', 'description' => 'Quản lý văn bằng'],
            ['name' => 'CertificateManager', 'description' => 'Quản lý chứng chỉ'],
            ['name' => 'StudentManager', 'description' => 'Quản lý sinh viên'],
            ['name' => 'Viewer', 'description' => 'Chỉ xem thông tin'],
        ];

        $role = $this->faker->randomElement($roles);

        return [
            'role_name' => $role['name'],
            'description' => $role['description'],
        ];
    }

    /**
     * Create admin role.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_name' => 'Admin',
            'description' => 'Quản trị viên hệ thống với toàn quyền',
        ]);
    }

    /**
     * Create diploma manager role.
     */
    public function diplomaManager(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_name' => 'DiplomaManager',
            'description' => 'Quản lý văn bằng và phôi văn bằng',
        ]);
    }
}
