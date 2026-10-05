<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->administrator()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        User::factory()->viewer()->create([
            'name' => 'Viewer Demo',
            'email' => 'viewer@example.com',
            'password' => 'password',
        ]);
    }
}
