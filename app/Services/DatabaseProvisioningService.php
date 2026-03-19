<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

/**
 * Database Provisioning Service
 * 
 * Manages creation, configuration, and deletion of per-tenant databases.
 * Each tenant gets their own isolated database for complete data separation.
 */
class DatabaseProvisioningService
{
    /**
     * Create a new database for a tenant.
     * 
     * @throws Exception
     */
    public function createDatabaseForTenant(Tenant $tenant): bool
    {
        $databaseName = $tenant->database_name;
        
        try {
            // Create database
            $this->createDatabase($databaseName);
            
            // Create database user with restricted access
            $this->createDatabaseUser($tenant);
            
            // Run migrations on tenant database
            $this->runMigrations($tenant);
            
            // Seed initial data
            $this->seedTenantData($tenant);
            
            return true;
        } catch (Exception $e) {
            // Cleanup on failure
            $this->deleteDatabase($databaseName);
            throw $e;
        }
    }
    
    /**
     * Create PostgreSQL database.
     */
    protected function createDatabase(string $databaseName): void
    {
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $port = config("database.connections.{$connection}.port", 5432);

        // Connect to PostgreSQL server using 'postgres' database (required for creating databases)
        DB::purge('pgsql');
        
        // Temporarily set database to 'postgres' for database creation
        $originalDatabase = config("database.connections.{$connection}.database");
        config(["database.connections.{$connection}.database" => 'postgres']);
        
        DB::purge('pgsql');

        try {
            DB::statement("CREATE DATABASE \"{$databaseName}\"
                WITH
                ENCODING = 'UTF8'
                LC_COLLATE = 'en_US.UTF-8'
                LC_CTYPE = 'en_US.UTF-8'
                TEMPLATE = template0");
        } finally {
            // Restore original database configuration
            config(["database.connections.{$connection}.database" => $originalDatabase]);
        }
    }
    
    /**
     * Create database user with restricted permissions.
     */
    protected function createDatabaseUser(Tenant $tenant): void
    {
        $databaseName = $tenant->database_name;
        $username = 'tenant_' . $tenant->id;
        $password = Str::random(32);
        
        // Store credentials in tenant metadata
        $tenant->update([
            'metadata' => array_merge(
                $tenant->metadata ?? [],
                [
                    'db_username' => $username,
                    'db_password' => encrypt($password),
                    'db_host' => config('database.connections.pgsql.host'),
                    'db_port' => config('database.connections.pgsql.port'),
                ]
            ),
        ]);
        
        // Create user with password
        DB::statement("CREATE USER \"{$username}\" WITH PASSWORD '{$password}'");
        
        // Grant permissions on tenant database only
        DB::statement("GRANT ALL PRIVILEGES ON DATABASE \"{$databaseName}\" TO \"{$username}\"");
        
        // Grant schema permissions (will be executed on tenant database)
        $this->grantSchemaPermissions($databaseName, $username);
    }
    
    /**
     * Grant schema-level permissions.
     */
    protected function grantSchemaPermissions(string $databaseName, string $username): void
    {
        // Connect to tenant database
        $originalConnection = config('database.default');
        $originalDatabase = config('database.connections.pgsql.database');
        
        config([
            'database.default' => 'pgsql',
            "database.connections.pgsql.database" => $databaseName,
        ]);
        
        DB::purge('pgsql');
        
        try {
            // Grant schema permissions
            DB::statement("GRANT ALL ON SCHEMA public TO \"{$username}\"");
            DB::statement("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO \"{$username}\"");
            DB::statement("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO \"{$username}\"");
            DB::statement("GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO \"{$username}\"");
        } finally {
            // Restore original connection
            config([
                'database.default' => $originalConnection,
                "database.connections.pgsql.database" => $originalDatabase,
            ]);
            DB::setDefaultConnection($originalConnection);
            DB::purge('pgsql');
        }
    }
    
    /**
     * Run migrations on tenant database.
     */
    public function runMigrations(Tenant $tenant): void
    {
        // Store original connection config
        $originalConnection = config('database.default');
        $originalDatabase = config('database.connections.pgsql.database');
        $connectionName = $this->getConnectionName($tenant);

        // Set tenant database connection
        $this->setTenantConnection($tenant);

        try {
            // Run tenant database migrations
            \Artisan::call('migrate', [
                '--database' => $connectionName,
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        } finally {
            // Restore original connection
            config([
                'database.default' => $originalConnection,
                "database.connections.pgsql.database" => $originalDatabase,
            ]);
            DB::setDefaultConnection($originalConnection);
            DB::purge('pgsql');
        }
    }

    /**
     * Seed initial tenant data.
     */
    public function seedTenantData(Tenant $tenant): void
    {
        // Store original connection config
        $originalConnection = config('database.default');
        $originalDatabase = config('database.connections.pgsql.database');
        $connectionName = $this->getConnectionName($tenant);

        $this->setTenantConnection($tenant);

        try {
            // Seed tenant database with initial data
            \Artisan::call('db:seed', [
                '--class' => \Database\Seeders\TenantDatabaseSeeder::class,
                '--database' => $connectionName,
                '--force' => true,
            ]);
        } finally {
            // Restore original connection
            config([
                'database.default' => $originalConnection,
                "database.connections.pgsql.database" => $originalDatabase,
            ]);
            DB::setDefaultConnection($originalConnection);
            DB::purge('pgsql');
        }
    }
    
    /**
     * Delete tenant database.
     */
    public function deleteDatabaseForTenant(Tenant $tenant): bool
    {
        try {
            // Drop database user first
            $username = 'tenant_' . $tenant->id;
            DB::statement("DROP USER IF EXISTS \"{$username}\"");
            
            // Drop database
            $this->deleteDatabase($tenant->database_name);
            
            return true;
        } catch (Exception $e) {
            \Log::error('Failed to delete tenant database', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Drop PostgreSQL database.
     */
    protected function deleteDatabase(string $databaseName): void
    {
        // First, reconnect to 'postgres' database if we're connected to the one being dropped
        $currentDb = config('database.connections.pgsql.database');
        if ($currentDb === $databaseName) {
            config(['database.connections.pgsql.database' => 'postgres']);
            DB::purge('pgsql');
            DB::reconnect('pgsql');
        }

        // Terminate all connections to database
        DB::statement("
            SELECT pg_terminate_backend(pid)
            FROM pg_stat_activity
            WHERE datname = '{$databaseName}'
            AND pid <> pg_backend_pid()
        ");

        // Drop database
        DB::statement("DROP DATABASE IF EXISTS \"{$databaseName}\"");
    }
    
    /**
     * Set tenant database connection.
     */
    public function setTenantConnection(Tenant $tenant): void
    {
        $databaseName = $tenant->database_name;
        $connectionName = 'tenant_' . $tenant->id;
        
        // Get tenant DB credentials
        $username = $tenant->metadata['db_username'] ?? null;
        $password = $tenant->metadata['db_password'] ? decrypt($tenant->metadata['db_password']) : null;
        
        // Configure tenant connection
        config([
            "database.connections.{$connectionName}" => [
                'driver' => 'pgsql',
                'host' => config('database.connections.pgsql.host'),
                'port' => config('database.connections.pgsql.port'),
                'database' => $databaseName,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
                'search_path' => 'public',
                'sslmode' => 'prefer',
            ],
        ]);
        
        // Set as default for tenant operations
        DB::purge($connectionName);
        DB::setDefaultConnection($connectionName);
    }
    
    /**
     * Get tenant database connection name.
     */
    public function getConnectionName(Tenant $tenant): string
    {
        return 'tenant_' . $tenant->id;
    }
    
    /**
     * Check if tenant database exists.
     */
    public function databaseExists(string $databaseName): bool
    {
        $result = DB::select("
            SELECT 1 FROM pg_database 
            WHERE datname = '{$databaseName}'
        ");
        
        return count($result) > 0;
    }
    
    /**
     * Get database size.
     */
    public function getDatabaseSize(string $databaseName): int
    {
        $result = DB::select("
            SELECT pg_database_size('{$databaseName}') as size
        ");
        
        return $result[0]->size ?? 0;
    }
    
    /**
     * Backup tenant database.
     */
    public function backupDatabase(Tenant $tenant): string
    {
        $databaseName = $tenant->database_name;
        $backupPath = storage_path("app/backups/{$databaseName}_" . now()->format('Y-m-d_His') . '.sql');
        
        // Use pg_dump for backup
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -U %s %s > %s',
            config('database.connections.pgsql.password'),
            config('database.connections.pgsql.host'),
            config('database.connections.pgsql.username'),
            $databaseName,
            $backupPath
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Database backup failed');
        }
        
        return $backupPath;
    }
    
    /**
     * Restore tenant database from backup.
     */
    public function restoreDatabase(Tenant $tenant, string $backupFile): void
    {
        $databaseName = $tenant->database_name;
        
        $command = sprintf(
            'PGPASSWORD=%s psql -h %s -U %s -d %s < %s',
            config('database.connections.pgsql.password'),
            config('database.connections.pgsql.host'),
            config('database.connections.pgsql.username'),
            $databaseName,
            $backupFile
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Database restore failed');
        }
    }
}
