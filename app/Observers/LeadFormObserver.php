<?php

namespace App\Observers;

use App\Models\LeadForm;
use Illuminate\Support\Str;

class LeadFormObserver
{
    public function creating(LeadForm $leadForm): void
    {
        if (company() && empty($leadForm->company_id)) {
            $leadForm->company_id = company()->id;
        }

        if (user() && empty($leadForm->added_by)) {
            $leadForm->added_by = user()->id;
        }

        if (empty($leadForm->slug)) {
            $leadForm->slug = Str::slug($leadForm->name);
        }

        $baseSlug = $leadForm->slug;
        $counter = 1;

        while (LeadForm::where('company_id', $leadForm->company_id)->where('slug', $leadForm->slug)->exists()) {
            $leadForm->slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    public function saving(LeadForm $leadForm): void
    {
        if (user()) {
            $leadForm->last_updated_by = user()->id;
        }
    }

    public function deleting(LeadForm $leadForm): bool
    {
        if ($leadForm->is_default) {
            return false;
        }

        if (LeadForm::where('company_id', $leadForm->company_id)->count() <= 1) {
            return false;
        }

        $leadForm->fields()->delete();

        return true;
    }
}
