<?php

namespace App\Http\Controllers;

use App\DataTables\LeadFormsDataTable;
use App\Helper\Reply;
use App\Http\Requests\LeadForm\StoreRequest;
use App\Http\Requests\LeadForm\UpdateRequest;
use App\Models\LeadCategory;
use App\Models\LeadCustomForm;
use App\Models\LeadForm;
use App\Models\LeadPipeline;
use App\Models\LeadSource;
use App\Models\PipelineStage;
use Illuminate\Http\Request;

class LeadFormController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.lead.leadForms';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));

            return $next($request);
        });
    }

    private function checkPermission(): void
    {
        abort_403(user()->permission('manage_lead_custom_forms') != 'all');
    }

    public function index(LeadFormsDataTable $dataTable)
    {
        $this->checkPermission();

        return $dataTable->render('leads.lead-forms.index', $this->data);
    }

    public function create()
    {
        $this->checkPermission();
        $this->pageTitle = __('modules.lead.addLeadForm');
        $this->formOptions();

        return $this->returnAjax('leads.lead-forms.ajax.create');
    }

    public function store(StoreRequest $request)
    {
        $this->checkPermission();

        $defaultForm = LeadForm::defaultForCompany();

        $leadForm = new LeadForm();
        $leadForm->name = trim($request->name);
        $leadForm->slug = $request->slug;
        $leadForm->status = $request->status ?? 'active';
        $leadForm->lead_pipeline_id = $request->lead_pipeline_id;
        $leadForm->pipeline_stage_id = $request->pipeline_stage_id;
        $leadForm->category_id = $request->category_id;
        $leadForm->lead_source_id = $request->lead_source_id;
        $leadForm->is_default = false;
        $leadForm->save();

        if ($defaultForm) {
            $defaultFields = LeadCustomForm::where('lead_form_id', $defaultForm->id)->get();

            foreach ($defaultFields as $field) {
                $newField = $field->replicate();
                $newField->lead_form_id = $leadForm->id;
                $newField->save();
            }
        } else {
            $data = LeadCustomForm::FORM_FIELDS;
            array_walk($data, function (&$a) use ($leadForm) {
                $a['company_id'] = company()->id;
                $a['lead_form_id'] = $leadForm->id;
            });
            LeadCustomForm::insert($data);
        }

        return Reply::success(__('messages.recordSaved'));
    }

    public function show($id)
    {
        $this->checkPermission();

        $this->leadForm = LeadForm::findOrFail($id);
        $this->pageTitle = $this->leadForm->name;
        $this->leadFormFields = LeadCustomForm::where('lead_form_id', $this->leadForm->id)
            ->orderBy('field_order')
            ->get();

        return view('leads.lead-forms.fields', $this->data);
    }

    public function edit($id)
    {
        $this->checkPermission();

        $this->leadForm = LeadForm::findOrFail($id);
        $this->pageTitle = __('app.edit') . ' ' . __('modules.lead.leadForm');
        $this->formOptions();

        return $this->returnAjax('leads.lead-forms.ajax.edit');
    }

    public function update(UpdateRequest $request, $id)
    {
        $this->checkPermission();

        $leadForm = LeadForm::findOrFail($id);
        $leadForm->name = trim($request->name);
        $leadForm->slug = $request->slug ?: $leadForm->slug;
        $leadForm->status = $request->status ?? $leadForm->status;
        $leadForm->lead_pipeline_id = $request->lead_pipeline_id;
        $leadForm->pipeline_stage_id = $request->pipeline_stage_id;
        $leadForm->category_id = $request->category_id;
        $leadForm->lead_source_id = $request->lead_source_id;
        $leadForm->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $this->checkPermission();

        $leadForm = LeadForm::findOrFail($id);

        if ($leadForm->is_default) {
            return Reply::error(__('modules.lead.cannotDeleteDefaultForm'));
        }

        if (LeadForm::where('company_id', company()->id)->count() <= 1) {
            return Reply::error(__('modules.lead.cannotDeleteLastForm'));
        }

        $leadForm->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function updateField(Request $request, $leadFormId, $fieldId)
    {
        $this->checkPermission();

        LeadForm::findOrFail($leadFormId);

        $updateData = [];

        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        if ($request->has('required')) {
            $updateData['required'] = $request->required;
        }

        LeadCustomForm::where('id', $fieldId)
            ->where('lead_form_id', $leadFormId)
            ->update($updateData);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function sortFields($leadFormId)
    {
        $this->checkPermission();

        LeadForm::findOrFail($leadFormId);

        $sortedValues = request('sortedValues');

        foreach ($sortedValues as $key => $value) {
            LeadCustomForm::where('id', $value)
                ->where('lead_form_id', $leadFormId)
                ->update(['field_order' => $key + 1]);
        }

        return Reply::dataOnly([]);
    }

    private function formOptions(): void
    {
        $this->leadPipelines = LeadPipeline::all();
        $this->categories = LeadCategory::all();
        $this->sources = LeadSource::all();
        $this->stages = PipelineStage::all();
    }
}
