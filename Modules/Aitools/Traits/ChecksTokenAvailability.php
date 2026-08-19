<?php

namespace Modules\Aitools\Traits;

use App\Helper\Reply;
use Modules\Aitools\Entities\AiToolsUsageHistory;
use App\Models\SuperAdmin\Package;

trait ChecksTokenAvailability
{
    /**
     * Check if company has remaining tokens available
     * 
     * @return bool|array Returns true if tokens available, or error response array if not
     */
    protected function checkTokenAvailability()
    {
        // Skip check for superadmin
        if (user()->is_superadmin) {
            return true;
        }

        // Get company package
        $company = company();
        if (!$company) {
            return Reply::error(__('aitools::messages.companyNotFound'));
        }

        $package = $company->package;
        if (!$package) {
            return Reply::error(__('aitools::messages.packageNotFound'));
        }

        // Get total assigned tokens from package
        $totalAssignedTokens = $package->ai_chatgpt_tokens ?? 0;

        // If no tokens assigned, allow usage (unlimited)
        if ($totalAssignedTokens <= 0) {
            return true;
        }

        // Get total tokens used by company
        $usageHistoryRecords = AiToolsUsageHistory::where('company_id', $company->id)->get();
        $totalTokensUsed = $usageHistoryRecords->sum('total_tokens');

        // Calculate remaining tokens
        $remainingTokens = $totalAssignedTokens - $totalTokensUsed;

        // Check if tokens are exhausted
        if ($remainingTokens <= 0) {
            return Reply::error(__('aitools::messages.tokenLimitExceeded', [
                'assigned' => number_format($totalAssignedTokens),
                'used' => number_format($totalTokensUsed)
            ]));
        }

        return true;
    }
}
