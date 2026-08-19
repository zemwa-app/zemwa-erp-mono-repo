<?php

namespace Modules\LeadFormsPro\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\SuperAdmin\Package;

//use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Modules\LeadFormsPro\Entities\LeadFormsProSetting;
use Modules\LeadFormsPro\Entities\LfpCategory;

class LeadFormsProSettingsController extends AccountBaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('leadformspro::app.menu.leadformsproSetting');
		$this->activeSettingMenu = 'leadformspro_settings';

		$this->middleware(function ($request, $next) {
			abort_403(!user()->is_superadmin && !in_array(LeadFormsProSetting::MODULE_NAME, $this->user->modules));

			return $next($request);
		});
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		$this->pageTitle = 'Lead Form Maximum Limit';
		//Get Id of packages
		$this->excludedPackageIds = $excludedPackageIds = LeadFormsProSetting::pluck('package_id');

		$this->lfpPackages = Package::when(user()->is_superadmin, function ($query) {

		})
			//->whereNotIn('id', $excludedPackageIds)
			->get();

		$this->lfpLFSs = LeadFormsProSetting::with(['getPackage' => function ($query) {
			$query->select('id', 'name');
		}])
			->when(user()->is_superadmin, function ($query) {
				//$query->where('id', null);
			})
			->get();

		return view('leadformspro::settings.index', $this->data);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		return view('leadformspro::create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		// Validate the request data
		$validator = Validator::make($request->all(), [
			'package' => 'required|numeric',
			'formLimit' => 'required|numeric',
			'categoryLimit' => 'required|numeric',
		], [
			'package.required' => 'Please select the package you want to set the limit.',
			'package.numeric' => 'You can select any one from the drop-down list. Please do not alter the value!',
			'formLimit.required' => 'Please enter the maximum number of forms you want to allow for the selected package.',
			'formLimit.numeric' => 'Only numeric fields are allowed for setting the form limit',
			'categoryLimit.required' => 'Please enter the maximum number of categories you want to allow for the selected package.',
			'categoryLimit.numeric' => 'Only numeric fields are allowed for setting the category limit',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		// Process the validated data
		$leadFormsProSetting = LeadFormsProSetting::updateOrCreate(
			[
				'package_id' => $request->input('package')
			],
			[
				'package_id' => $request->input('package'),
				'form_limit' => $request->input('formLimit'),
				'category_limit' => $request->input('categoryLimit'),
				'added_by' => auth()->user()->id,
				'status' => 1
			]
		);

		// Return a JSON response
		return response()->json([
			'status' => 'success',
			'message' => 'Lead form pro setting created successfully'
		], Response::HTTP_CREATED); // 201 status code for resource creation
	}

	/**
	 * Show the specified resource.
	 */
	public function show($id)
	{
		return view('leadformspro::show');
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit($id)
	{
		$leadformsproSetting = LeadFormsProSetting::find($id);

		// Check if the record exists
		if (!$leadformsproSetting) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'package_id' => $leadformsproSetting->package_id,
			'form_limit' => $leadformsproSetting->form_limit,
			'cat_limit' => $leadformsproSetting->category_limit,
		]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, $id)//: RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy($id)
	{
		$record = LeadFormsProSetting::find($id);

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}
}
