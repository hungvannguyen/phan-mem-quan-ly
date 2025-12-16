<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Automatically detects environment and runs appropriate seeder.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            // Production: Only create admin and diploma blank types
            $this->call(ProductionSeeder::class);
        } else {
            // Development/Local: Create full demo data with proper relationships
            $this->call(DevelopmentSeeder::class);
        }
    }
}
