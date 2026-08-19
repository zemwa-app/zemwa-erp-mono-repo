<?php

namespace Modules\Monitor\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Monitor\Entities\ProductivityRule;
use Modules\Monitor\Services\Analytics\ProductivityClassifierService;

class ProductivityRulesSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = require __DIR__ . '/data/productivity_rules_defaults.php';

        foreach ($defaults as $rule) {
            ProductivityRule::query()->updateOrCreate(
                [
                    'company_id' => null,
                    'type' => $rule['type'],
                    'pattern' => $rule['pattern'],
                ],
                [
                    'category' => $rule['category'],
                    'subcategory' => $rule['subcategory'],
                    'priority' => ProductivityClassifierService::GLOBAL_PRIORITY,
                    'match_count' => 0,
                ]
            );
        }
    }
}
