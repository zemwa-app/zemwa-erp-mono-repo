<?php

namespace App\Observers;

use App\Models\UserchatFile;

class UserchatFileObserver
{

    public function creating(UserchatFile $model)
    {
        if (!is_null($model->company_id)) {
            return;
        }

        if ($model->chat && $model->chat->company_id) {
            $model->company_id = $model->chat->company_id;

            return;
        }

        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
