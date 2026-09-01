<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SyncExistingMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:sync-existing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks all migrations for tables that already exist in the database as completed in the migrations table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!Schema::hasTable('migrations')) {
            $this->warn('Migrations table does not exist yet.');
            return Command::SUCCESS;
        }

        $existingMigrations = DB::table('migrations')->pluck('migration')->toArray();
        $existingMigrationsMap = array_flip($existingMigrations);

        $directories = [
            base_path('database/migrations'),
        ];

        // Add module migrations directories
        $modulesDir = base_path('Modules');
        if (File::isDirectory($modulesDir)) {
            foreach (File::directories($modulesDir) as $module) {
                $dir = $module . '/Database/Migrations';
                if (File::isDirectory($dir)) {
                    $directories[] = $dir;
                }

                // Nested bundle modules
                $nested = $module . '/Modules';
                if (File::isDirectory($nested)) {
                    foreach (File::directories($nested) as $subModule) {
                        $subDir = $subModule . '/Database/Migrations';
                        if (File::isDirectory($subDir)) {
                            $directories[] = $subDir;
                        }
                    }
                }
            }
        }

        $syncedCount = 0;
        $maxBatch = DB::table('migrations')->max('batch') ?? 1;

        foreach ($directories as $directory) {
            $files = File::glob($directory . '/*.php');
            foreach ($files as $file) {
                $migrationName = basename($file, '.php');
                
                if (isset($existingMigrationsMap[$migrationName])) {
                    continue;
                }

                // Check content for table name
                $content = File::get($file);
                $matchedTable = null;
                if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
                    $matchedTable = $matches[1];
                }

                // If the table already exists in the database, mark this migration as completed
                if ($matchedTable && Schema::hasTable($matchedTable)) {
                    DB::table('migrations')->insert([
                        'migration' => $migrationName,
                        'batch' => $maxBatch,
                    ]);
                    $existingMigrationsMap[$migrationName] = true;
                    $syncedCount++;
                    $this->line("Synced existing table migration: <info>{$migrationName}</info> (table: {$matchedTable})");
                }
            }
        }

        $this->info("Completed! Synced {$syncedCount} existing migrations.");
        return Command::SUCCESS;
    }
}
