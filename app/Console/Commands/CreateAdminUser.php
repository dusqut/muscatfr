<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email}';

    protected $description = 'Create the first catalog administrator';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error('An account already uses that email address.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Administrator name'));
        $password = (string) $this->secret('Password (at least 14 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if ($name === '' || strlen($password) < 14 || ! hash_equals($password, $confirmation)) {
            $this->error('Name, password length, or password confirmation is invalid.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
        ]);

        $this->info('Administrator account created.');

        return self::SUCCESS;
    }
}
