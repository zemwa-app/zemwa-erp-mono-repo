<?php

namespace Modules\LandingPagePro\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\SuperAdmin\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Modules\LandingPagePro\Entities\LandingPageProSetting;
use Modules\LandingPagePro\Entities\LandingPageTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class LandingPageProSettingController extends AccountBaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('landingpagepro::app.menu.landingpageproSetting');
		$this->activeSettingMenu = 'landingpagepro_settings';

		$this->middleware(function ($request, $next) {
			abort_403(!user()->is_superadmin && !in_array(LandingPageProSetting::MODULE_NAME, $this->user->modules));

			return $next($request);
		});
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{

		$this->pageTitle = 'Landing Page Configurations';

		//Get Id of packages
		$this->excludedPackageIds = $excludedPackageIds = LandingPageProSetting::pluck('package_id');

		$this->packages = Package::when(user()->is_superadmin, function ($query) {
			return $query->select('id', 'name');
		})->get();

		$this->packageLimits = LandingPageProSetting::with(['getPackage' => function($query) {
			$query->select('id', 'name');
		}])
			->when(user()->is_superadmin, function ($query) {
				//$query->where('id', null);
			})
			->get();

		$this->lpTemplates = LandingPageTemplate::when(user()->is_superadmin, function ($query) {
			return $query;
		})->get()->map(function ($template) {
			// Decode the associated_packages JSON into an array of IDs
			$packageIds = json_decode($template->associated_packages);
			// Load the package names using the IDs
			$packages = Package::whereIn('id', $packageIds)->get()->pluck('name');
			// Add the package names to the template object
			$template->packageNames = $packages;
			return $template;
		});

		return view('landingpagepro::settings.index', $this->data);
	}

	public function store(Request $request)
	{
		// Validate the request data
		$validator = Validator::make($request->all(), [
			'package' => 'required|numeric',
			'pageLimit' => 'required|numeric',
			'categoryLimit' => 'required|numeric',
		], [
			'package.required' => 'Please select the package you want to set the limit.',
			'package.numeric' => 'You can select any one from the drop-down list. Please do not alter the value!',
			'pageLimit.required' => 'Please enter the maximum number of forms you want to allow for the selected package.',
			'pageLimit.numeric' => 'Only numeric fields are allowed for setting the form limit',
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
		$leadFormsProSetting = LandingPageProSetting::updateOrCreate(
			[
				'package_id' => $request->input('package')
			],
			[
				'package_id' => $request->input('package'),
				'page_limit' => $request->input('pageLimit'),
				'category_limit' => $request->input('categoryLimit'),
				'added_by' => user()->id,
				'status' => 1
			]
		);

		// Return a JSON response
		return response()->json([
			'status' => 'success',
			'message' => 'Successfully set the page and category limit for the Package.'
		], Response::HTTP_CREATED); // 201 status code for resource creation
	}

	public function edit($id)
	{
		$landingpagepro = LandingPageProSetting::find($id);

		// Check if the record exists
		if (!$landingpagepro) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'package_id' => $landingpagepro->package_id,
			'page_limit' => $landingpagepro->page_limit,
			'category_limit' => $landingpagepro->category_limit,
		]);
	}

	public function destroy($id)
	{
		$record = LandingPageProSetting::find($id);

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

	public function storeTemplate(Request $request)
	{
		try {
			$thumbnailValidation = $request->input('id') ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024' : 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024';
			// Validate the request data
			$validator = Validator::make($request->all(), [
				'templateName' => 'required|string|max:255',
				'templateImage' => $thumbnailValidation, // max size 1MB
				'packages' => 'required|array|min:1',
				'packages.*' => 'exists:packages,id', // Assuming 'packages' is your table name
			]);

			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'message' => $validator->errors()
				], 422);
			}

			$imagePath = LandingPageTemplate::find($request->input('id'))->thumbnail ?? '';

			if ($request->hasFile('templateImage')) {
				$file = $request->file('templateImage');
				$imageName = time() . '.' . $file->getClientOriginalExtension();
				// Store the file on the specified disk (e.g., 'public')
				$path = $file->storeAs('landingpagepro', $imageName, 'public');
				// $path will be the relative path to the file
				$imagePath = $path;
			}

			// Process the validated data
			$leadFormsProSetting = LandingPageTemplate::updateOrCreate(
				[
					'id' => $request->input('id')
				],
				[
					'name' => $request->templateName,
					'thumbnail' => $imagePath,
					'associated_packages' => json_encode($request->packages),
					'added_by' => user()->id,
					'status' => 1
				]
			);

			// Return a JSON response for success
			return response()->json([
				'status' => 'success',
				'message' => 'Successfully saved the template information.'
			], Response::HTTP_CREATED); // 201 status code for resource creation

		} catch (QueryException $e) {
			// Return a JSON response for error
			return response()->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], Response::HTTP_INTERNAL_SERVER_ERROR); // 500 status code for server error
		} catch (\Exception $e) {
			// Return a JSON response for error
			return response()->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], Response::HTTP_INTERNAL_SERVER_ERROR); // 500 status code for server error
		}
	}

	public function editTemplate($id)
	{
		$lpTemplates = LandingPageTemplate::find($id);

		// Check if the record exists
		if (!$lpTemplates) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'id' => $lpTemplates->id,
			'templateName' => $lpTemplates->name,
			'packages' => json_decode($lpTemplates->associated_packages),
		]);
	}

	public function destroyTemplate($id)
	{
		$record = LandingPageTemplate::find($id);

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

}
