<?php

namespace Modules\LeadFormsPro\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LeadCustomForm;
use App\Models\LeadSource;
use App\Models\Product;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Modules\LeadFormsPro\Entities\LfpLeadForm;
use function Psl\Str\is_empty;

class LfController extends Controller
{
	/**
	 * custom lead form
	 */
	public function leadForm($id)
	{
		$this->id = (int)(Crypt::decrypt($id));
		$this->withLogo = \request()->get('with_logo');
		$this->styled = \request()->get('styled');
		$this->pageTitle = 'modules.lead.leadForm';//Lead Form';

		$this->lfp = null;
		$query = "SELECT l.*, c.name as category_name FROM lfp_lead_forms l LEFT JOIN lfp_categories c ON l.category_id = c.id WHERE l.id = ?";
		$results = DB::select($query, [$this->id]);
		
		if (!empty($results)) {
			$this->lfp = (object)$results[0];
		} else {
			return redirect('https://zemwa.com/');
		}

		$this->company = Company::where('id', $this->lfp->company_id)->firstOrFail();

		$this->globalSetting = global_setting();
		$this->countries = countries();
		$this->sources = LeadSource::where('company_id', $this->lfp->company_id)->get();
		$this->products = Product::where('company_id', $this->lfp->company_id)->get();

		//$lfpLeadForm = LfpLeadForm::find($this->id);
		$fields = json_decode($this->lfp->form_fields, true);
		$checkedSettingIds = array_map('intval', array_column(array_filter($fields, function ($field) {
			return $field['checked'] === 'true';
		}), 'settingId'));

		$this->leadFormFields = LeadCustomForm::with('customField')
			->where('company_id', $this->lfp->company_id)
			->where(function ($query) use ($checkedSettingIds) {
				$query->whereIn('id', $checkedSettingIds)
					->orWhere('field_name', 'name');
			})
			->orderBy('field_order')
			->get();
		$this->leadFormFields->each(function ($leadCustomForm) {
			$leadCustomForm->update(['status' => 'active']);
		});
		//dd($this->data);
		//return view('lead-form', $this->data);
		return view('leadformspro::lead-form', $this->data);
	}
}
