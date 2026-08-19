<?php

namespace Modules\Monitor\Database\Seeders;

use Illuminate\Database\Seeder;

class MonitorDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        config(['app.seeding' => true]);

        $this->call(ProductivityRulesSeeder::class);

        if (app()->environment('demo')) {
            $this->call(MonitorDemoSeeder::class);
        }

        config(['app.seeding' => false]);
    }
}
