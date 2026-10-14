<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAureliaAdmin extends Command
{
    protected $signature = 'aurelia:create-admin {email} {--name=} {--password=}';
    protected $description = 'Create or update an Aurelia admin account without hardcoded credentials';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
        $name = $this->option('name') ?: $this->ask('Admin name', 'Aurelia Administrator');
        $password = $this->option('password') ?: $this->secret('Admin password');
        if (! $password || strlen($password) < 12) { $this->error('Use a password of at least 12 characters.'); return self::FAILURE; }

        $role = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Full operational access']);
        User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make($password), 'role_id' => $role->id]);
        $this->info("Admin account ready for {$email}.");
        return self::SUCCESS;
    }
}
