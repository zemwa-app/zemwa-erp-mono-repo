<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\LeadCustomForm;
use App\Models\LeadForm;
use Illuminate\Http\Request;

class LeadCustomFormController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.lead.leadForm';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));

            return $next($request);
        });
    }

    public function index()
    {
        abort_403(user()->permission('manage_lead_custom_forms') != 'all');

        $defaultForm = LeadForm::defaultForCompany();

        if ($defaultForm) {
            return redirect()->route('lead-forms.show', $defaultForm->id);
        }

        return redirect()->route('lead-forms.index');
    }

    public function update(Request $request, $id)
    {
        abort_403(user()->permission('manage_lead_custom_forms') != 'all');

        $defaultForm = LeadForm::defaultForCompany();
        $updateData = [];

        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        if ($request->has('required')) {
            $updateData['required'] = $request->required;
        }

        LeadCustomForm::where('id', $id)
            ->when($defaultForm, fn($q) => $q->where('lead_form_id', $defaultForm->id))
            ->update($updateData);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function sortFields()
    {
        abort_403(user()->permission('manage_lead_custom_forms') != 'all');

        $defaultForm = LeadForm::defaultForCompany();
        $sortedValues = request('sortedValues');

        foreach ($sortedValues as $key => $value) {
            LeadCustomForm::where('id', $value)
                ->when($defaultForm, fn($q) => $q->where('lead_form_id', $defaultForm->id))
                ->update(['field_order' => $key + 1]);
        }

        return Reply::dataOnly([]);
    }
}
