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
        // User::factory(10)->create();

        $adminEmail = 'admin@example.com';
        if (!User::where('email', $adminEmail)->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => bcrypt('admin1234'),
                'is_admin' => true,
            ]);
        }
    }
}
