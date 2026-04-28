<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantMigrate extends Command
{
    protected $signature   = 'tenant:migrate {domain : The tenant domain}';
    protected $description = 'Run pending migrations on a single tenant database';

    public function handle(): int
    {
        $tenant = Tenant::on('landlord')->where('domain', $this->argument('domain'))->first();

        if (! $tenant) {
            $this->error("Tenant [{$this->argument('domain')}] not found.");

            return self::FAILURE;
        }

        $tenant->makeCurrent();

        $this->call('migrate', [
            '--database' => 'mysql',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);

        Tenant::forgetCurrent();

        $this->info("Migrations run for [{$tenant->name}].");

        return self::SUCCESS;
    }
}
