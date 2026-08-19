<?php

namespace Modules\ServerManager\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\ServerManager\Entities\ServerDomainType;

class ServerDomainTypeSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all(['id']);

        foreach ($companies as $company) {
            // Create defaults if missing for company
            foreach (ServerDomainType::DEFAULT_TYPES as $type) {
                $type = ServerDomainType::normalizeType($type);
                $label = ServerDomainType::displayLabel($type);

                ServerDomainType::firstOrCreate(
                    ['company_id' => $company->id, 'type' => $type],
                    ['label' => $label, 'is_active' => true]
                );
            }
        }
    }
}

