<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@crawlix.com'],
            [
                'name' => 'Crawlix Admin',
                'password' => 'Crawlix@Admin2026',
                'is_admin' => true,
            ]
        );

        // Legacy username login still works for the old local account.
        User::query()->updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'admin',
                'password' => 'password',
                'is_admin' => true,
            ]
        );
    }
}
