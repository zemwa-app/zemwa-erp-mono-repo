<?php

namespace App\Observers;

use App\Models\DealEmailTemplate;

class DealEmailTemplateObserver
{
    public function creating(DealEmailTemplate $model): void
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
