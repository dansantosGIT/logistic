<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create main admin account (email: admin@logistics.com / password: adm1n)
        User::updateOrCreate(
            ['email' => 'admin@logistics.com'],
            ['name' => 'Main Admin', 'password' => Hash::make('adm1n')]
        );

        // Example test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
