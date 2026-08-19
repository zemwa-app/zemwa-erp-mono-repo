<?php

namespace Modules\LeadFormsPro\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\LeadCustomForm;
use App\Models\LeadSource;
use App\Models\Product;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Modules\LeadFormsPro\Entities\LeadFormsProSetting;
use Modules\LeadFormsPro\Entities\LfpCategory;
use Modules\LeadFormsPro\Entities\LfpLeadForm;

class LeadFormsProController extends AccountBaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('leadformspro::app.menu.leadformspro');

		$this->middleware(function ($request, $next) {
			if ($request->route()->named('front.lead_pro_form')) {
				return $next($request);
			}
			abort_403(!user()->is_superadmin && !in_array(LeadFormsProSetting::MODULE_NAME, $this->user->modules));

			return $next($request);
		});
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		$this->viewLfpPermission = $viewPermission = user()->permission('view_leadform');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$userId = $this->user->employeeDetail->user_id;
		$this->leadPages = LfpLeadForm::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($viewPermission, $userId)
			->with('category')
			->where('company_id', company()->id)
			->get();
		$this->leadFormFields = LeadCustomForm::get();
		$this->leadCategories = LfpCategory::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($viewPermission, $userId)
			->where('company_id', company()->id)
			->get();
		$this->allowedLimits = LeadFormsProSetting::select('form_limit', 'category_limit')
			->where('package_id', '=', company()->package_id)
			->get()->first();

		$this->pageTitle = __('leadformspro::app.menu.leadformspro');
		$this->usertype = $this->user;

		return view('leadformspro::index', $this->data);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function storeLeadForm(Request $request)
	{
		$this->userPermission = $userPermission = user()->permission('add_leadform');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']));

		if ($request->input('id')) {
			$id = $request->input('id');
			$userId = $this->user->employeeDetail->user_id;
			$result = LfpLeadForm::withoutGlobalScope(ModuleCompanyScope::class)
				->userPermission($userPermission, $userId)
				->where('company_id', company()->id)
				->find($id);
			if (!$result) {
				return response()->json([
					'status' => 'error',
					'message' => 'You are not authorised to edit this template.'
				], 403);
			}
		}

		$leadFormCount = LfpLeadForm::count();
		$leadFormMaxCount = LeadFormsProSetting::where('package_id', company()->package_id)->value('form_limit');

		if ($leadFormCount >= $leadFormMaxCount) {
			return response()->json([
				'status' => 'error',
				'message' => 'You had already created the allowed number of lead forms.'
			], 403);
		}

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'leadFormName' => 'required|min:3',
			'leadCat' => 'required',
		], [
			'leadCatName.required' => 'Please enter the lead form name you want to create.',
			'leadCatName.min' => 'The lead form name must be at least :min characters.',
			'leadCat.required' => 'Please select the category',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		try {
			// Process the validated data
			$lfpCategory = LfpLeadForm::updateOrCreate(
				[
					'id' => $request->input('id')
				],
				[
					'company_id' => company()->id,
					'name' => $request->input('leadFormName'),
					'category_id' => $request->input('leadCat'),
					'form_fields' => json_encode($request->input('checkboxes')),
					'hash' => "",
					'added_by' => $this->user->employeeDetail->user_id,
					'status' => 1
				]);

			return response()->json([
				'status' => 'success',
				'message' => 'Lead form pro setting created successfully'
			], Response::HTTP_CREATED);
		} catch (\Exception $e) {

			return response()->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], 500); // 201 status code for resource creation
		}

	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function editLeadForm($id)
	{
		$this->userPermission = $userPermission = user()->permission('edit_leadform');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']));

		$userId = $this->user->employeeDetail->user_id;
		$result = LfpLeadForm::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($userPermission, $userId)
			->where('company_id', company()->id)
			->find($id);
		if (!$result) {
			return response()->json([
				'status' => 'error',
				'message' => 'You are not authorised to edit this template.'
			], 403);
		}

		$lfpLeadForm = LfpLeadForm::find($id);

		// Check if the record exists
		if (!$lfpLeadForm) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		$fields = json_decode($lfpLeadForm->form_fields, true);

		// Return the data as JSON
		return response()->json([
			'id' => $lfpLeadForm->id,
			'name' => $lfpLeadForm->name,
			'leadCat' => $lfpLeadForm->category_id,
			'fields' => $fields,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroyLeadForm($id)
	{
		$this->userPermission = $userPermission = user()->permission('delete_leadform');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']));

		$userId = $this->user->employeeDetail->user_id;
		$record = LfpLeadForm::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($userPermission, $userId)
			->where('company_id', company()->id)
			->find($id);
		if (!$record) {
			return response()->json([
				'status' => 'error',
				'message' => 'You are not authorised to edit this template.'
			], 403);
		}

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

	/**
	 * custom lead form
	 */
	public function leadForm($id)
	{

		$this->withLogo = \request()->get('with_logo');
		$this->styled = \request()->get('styled');

		$this->pageTitle = 'modules.lead.leadForm';
		//$this->company = Company::where('hash', $id)->firstOrFail();
		$this->globalSetting = global_setting();
		$this->countries = countries();
		$this->sources = LeadSource::where('company_id', $this->company->id)->get();
		$this->products = Product::where('company_id', $this->company->id)->get();

		$id = Crypt::decrypt($id);
		$this->lfp = LfpLeadForm::with('category')->find($id);
		$lfpLeadForm = LfpLeadForm::find($id);
		$fields = json_decode($lfpLeadForm->form_fields, true);

		$checkedSettingIds = array_map('intval', array_column(array_filter($fields, function ($field) {
			return $field['checked'] === 'true';
		}), 'settingId'));

		$this->leadFormFields = LeadCustomForm::with('customField')
			->where('company_id', company()->id)
			->where(function ($query) use ($checkedSettingIds) {
				$query->whereIn('id', $checkedSettingIds)
					->orWhere('field_name', 'name');
			})
			->orderBy('field_order')
			->get();

		$this->leadFormFields->each(function ($leadCustomForm) {
			$leadCustomForm->update(['status' => 'active']);
		});

		return view('lead-form', $this->data);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function storeCategory(Request $request)
	{
		$this->userPermission = $userPermission = user()->permission('add_leadformcategory');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']));

		if ($request->input('id')) {
			$id = $request->input('id');
			$userId = $this->user->employeeDetail->user_id;
			$result = LfpCategory::withoutGlobalScope(ModuleCompanyScope::class)
				->userPermission($userPermission, $userId)
				->where('company_id', company()->id)
				->find($id);
			if (!$result) {
				return response()->json([
					'status' => 'error',
					'message' => 'You are not authorised to edit this template.'
				], 403);
			}
		}

		$catCount = LfpCategory::count();
		$catMax = LeadFormsProSetting::where('package_id', company()->package_id)->value('category_limit');

		if ($catCount >= $catMax) {
			return response()->json([
				'status' => 'error',
				'message' => 'You had already created the allowed number of category.'
			], 403);
		}

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'leadCatName' => 'required|min:3',
		], [
			'leadCatName.required' => 'Please enter the category name you want to create.',
			'leadCatName.min' => 'The category name must be at least :min characters.',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		// Process the validated data
		$lfpCategory = LfpCategory::updateOrCreate(
			[
				'id' => $request->input('leadCatId')
			],
			[
				'company_id' => company()->id,
				'name' => $request->input('leadCatName'),
				'added_by' => $this->user->employeeDetail->user_id,
				'status' => 1
			]);

		// Return a JSON response
		return response()->json([
			'status' => 'success',
			'message' => 'Lead form pro setting created successfully'
		], Response::HTTP_CREATED); // 201 status code for resource creation
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function editCategory($id)
	{
		$this->userPermission = $userPermission = user()->permission('edit_leadformcategory');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']));

		$userId = $this->user->employeeDetail->user_id;
		$result = LfpCategory::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($userPermission, $userId)
			->where('company_id', company()->id)
			->find($id);
		if (!$result) {
			return response()->json([
				'status' => 'error',
				'message' => 'You are not authorised to edit this template.'
			], 403);
		}

		$lfpCategory = LfpCategory::find($id);

		// Check if the record exists
		if (!$lfpCategory) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'id' => $lfpCategory->id,
			'name' => $lfpCategory->name,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroyCategory($id)
	{
		$this->userPermission = $userPermission = user()->permission('delete_leadformcategory');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']));

		$userId = $this->user->employeeDetail->user_id;
		$record = LfpCategory::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($userPermission, $userId)
			->where('company_id', company()->id)
			->find($id);
		if (!$record) {
			return response()->json([
				'status' => 'error',
				'message' => 'You are not authorised to edit this template.'
			], 403);
		}

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

}
