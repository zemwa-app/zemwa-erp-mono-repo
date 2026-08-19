<?php

namespace Modules\Monitor\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\Monitor\Services\Analytics\ProductivityClassifierService;

class ClassifyActivityLogs extends Command
{
    protected $signature = 'monitor:classify-activity-logs {--company= : Company ID} {--days=30 : Look back N days}';

    protected $description = 'Classify agent activity logs using productivity rules';

    public function handle(ProductivityClassifierService $classifier): int
    {
        $days = (int) $this->option('days');
        $since = now()->subDays($days)->startOfDay();
        $companyId = $this->option('company');

        $companies = $companyId
            ? Company::where('id', $companyId)->get()
            : Company::query()->where('status', 'active')->get();

        $totalClassified = 0;
        $totalUncategorised = 0;

        foreach ($companies as $company) {
            $result = $classifier->classifyCompanyLogs($company->id, $since);
            $totalClassified += $result['classified'];
            $totalUncategorised += $result['uncategorised'];

            $this->info(sprintf(
                'Company %s (#%d): classified %d entries, %d remain uncategorised',
                $company->company_name,
                $company->id,
                $result['classified'],
                $result['uncategorised']
            ));
        }

        $this->info("ClassifyActivityLogs: {$totalClassified} classified, {$totalUncategorised} uncategorised (last {$days} days)");

        return self::SUCCESS;
    }
}
