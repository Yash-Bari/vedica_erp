<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@vedica.com'],
            [
                'name' => 'Vedica Admin',
                'password' => Hash::make('VedicaAdmin2024!'),
                'email_verified_at' => now(),
                'role' => 'Admin'
            ]
        );

        // Create corresponding employee record
        Employee::firstOrCreate(
            ['email' => 'admin@vedica.com'],
            [
                'first_name' => 'Vedica',
                'last_name' => 'Admin',
                'password' => Hash::make('VedicaAdmin2024!'),
                'role' => 'Admin',
                'status' => 'Active',
                'username' => 'vadmin'
            ]
        );
    }
}