<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Credentials come from the environment, never hardcoded — see ADMIN_EMAIL /
     * ADMIN_PASSWORD in .env.example.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set in .env — skipping admin user seed.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'INZRA Admin',
                'password' => Hash::make($password),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
