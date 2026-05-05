<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'charlin@example.com'],
            [
                'name' => 'Charlin Iverson',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'reviewer@example.com'],
            [
                'name' => 'Ajaia Reviewer',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'team@example.com'],
            [
                'name' => 'Team Member',
                'password' => Hash::make('password'),
            ]
        );
    }
}