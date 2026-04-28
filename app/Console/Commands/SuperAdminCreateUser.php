<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SuperAdminCreateUser extends Command
{
    protected $signature = 'superadmin:create-user {email? : The email address} {password? : The password}';
    protected $description = 'Create a super admin user';

    public function handle(): int
    {
        $email = $this->argument('email') ?? 'admin@pms.test';
        $password = $this->argument('password') ?? 'password';

        DB::connection('landlord')->table('users')->insert([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => bcrypt($password),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("Super admin user created: {$email} / {$password}");
        return Command::SUCCESS;
    }
}