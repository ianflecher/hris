<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * A fresh deployment has nobody to sign in with: the first admin comes from
 * the ADMIN_USERNAME and ADMIN_PASSWORD environment variables, once - when
 * there is no admin yet. Change the password after signing in.
 */
class EnsureAdmin extends Command
{
    protected $signature = 'hris:ensure-admin';

    protected $description = 'Create the first admin from ADMIN_USERNAME / ADMIN_PASSWORD when there is none';

    public function handle(): int
    {
        if (DB::table('users')->where('role', 'admin')->exists()) {
            $this->line('An admin already exists - nothing to do.');

            return self::SUCCESS;
        }

        $username = (string) env('ADMIN_USERNAME', '');
        $password = (string) env('ADMIN_PASSWORD', '');
        if ($username === '' || strlen($password) < 8) {
            $this->warn('No admin yet: set ADMIN_USERNAME and ADMIN_PASSWORD (8+ characters) and restart.');

            return self::SUCCESS;
        }

        User::create([
            'full_name' => 'HRIS Administrator',
            'username' => $username,
            'email' => $username.'@hris.local',
            'password' => $password,
            'role' => 'admin',
        ]);
        $this->info("Admin '{$username}' created.");

        return self::SUCCESS;
    }
}
