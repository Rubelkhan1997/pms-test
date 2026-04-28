<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCreate extends Command
{
    protected $signature   = 'tenant:create {name : The hotel name} {domain : The domain (e.g. marriott.pms.test)}';
    protected $description = 'Provision a new tenant with an isolated database';

    public function handle(): int
    {
        $name   = $this->argument('name');
        $domain = $this->argument('domain');
        $slug   = Str::slug($name);
        $dbName = 'pms_' . Str::replace('-', '_', $slug);

        // Guard: duplicate domain
        if (Tenant::on('landlord')->where('domain', $domain)->exists()) {
            $this->error("A tenant with domain [{$domain}] already exists.");

            return self::FAILURE;
        }

        // Create tenant record
        $tenant = Tenant::create([
            'name'     => $name,
            'slug'     => $slug,
            'domain'   => $domain,
            'database' => $dbName,
            'status'   => 'active',
        ]);

        $this->info("Tenant record created (ID: {$tenant->id})");

        // Create the database
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->info("Database [{$dbName}] created");

        // Run tenant migrations
        $tenant->makeCurrent();
        $this->call('migrate', [
            '--database' => 'mysql',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
        Tenant::forgetCurrent();

        $this->info("Tenant [{$name}] provisioned successfully.");
        $this->table(['Field', 'Value'], [
            ['Domain',   $domain],
            ['Database', $dbName],
            ['Status',   'active'],
        ]);

        return self::SUCCESS;
    }
}
