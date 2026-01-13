<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1 Superadmin
        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );

        // 2 Admins
        User::updateOrCreate(
            ['email' => 'admin1@example.com'],
            [
                'name' => 'Admin One',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin2@example.com'],
            [
                'name' => 'Admin Two',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 3 Heads of Department
        User::updateOrCreate(
            ['email' => 'head1@example.com'],
            [
                'name' => 'IT Department Head',
                'password' => Hash::make('password'),
                'role' => 'head_of_department',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'head2@example.com'],
            [
                'name' => 'HR Department Head',
                'password' => Hash::make('password'),
                'role' => 'head_of_department',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'head3@example.com'],
            [
                'name' => 'Finance Department Head',
                'password' => Hash::make('password'),
                'role' => 'head_of_department',
                'email_verified_at' => now(),
            ]
        );

        // 5 Staff users
        User::updateOrCreate(
            ['email' => 'staff1@example.com'],
            [
                'name' => 'Alice Johnson',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff2@example.com'],
            [
                'name' => 'Bob Williams',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff3@example.com'],
            [
                'name' => 'Charlie Brown',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff4@example.com'],
            [
                'name' => 'Diana Martinez',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff5@example.com'],
            [
                'name' => 'Ethan Davis',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Users seeded successfully!');
    }
}
