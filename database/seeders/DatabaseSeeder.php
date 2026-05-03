<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ⚠️ NEVER RUN migrate:fresh IN PRODUCTION
        // Use `php artisan migrate` for future fixes.

        $this->call([
            AdminSeeder::class,
            SampleDataSeeder::class,
        ]);
    }
}
