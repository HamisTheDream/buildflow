<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class OwnerCreateAdmin extends Command
{
    protected $signature = 'owner:create-admin {email} {--name=Owner} {--super}';
    protected $description = 'Create an owner/admin account for BuildFlow';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?: 'Owner';
        $isSuper = (bool) $this->option('super');

        if (Admin::where('email', $email)->exists()) {
            $this->error('Admin already exists with that email.');
            return self::FAILURE;
        }

        $password = $this->secret('Choose a password (input hidden)');
        if (!$password || strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_super' => $isSuper,
        ]);

        $this->info('Admin created successfully.');
        return self::SUCCESS;
    }
}
