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
     * Create diploma blank manager role.
     */
    public function diplomaBlankManager(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_name' => 'Quản lý phôi',
            'description' => 'Quản lý phôi văn bằng',
        ]);
    }

    /**
     * Create diploma manager role.
     */
    public function diplomaManager(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_name' => 'Quản lý văn bằng',
            'description' => 'Quản lý văn bằng',
        ]);
    }

    /**
     * Create certificate manager role.
     */
    public function certificateManager(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_name' => 'Quản lý chứng chỉ',
            'description' => 'Quản lý chứng chỉ',
        ]);
    }

    /**
     * Create viewer role.
     */
    public function viewer(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_name' => 'Tra cứu',
            'description' => 'Chỉ xem thông tin, tra cứu',
        ]);
    }
}
