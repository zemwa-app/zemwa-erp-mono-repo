<?php

namespace Modules\LandingPagePro\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Modules\LandingPagePro\Entities\LandingPage;
use Modules\LandingPagePro\Entities\LandingPageCategory;
use Modules\LandingPagePro\Entities\LandingPageProSetting;
use Modules\LandingPagePro\Entities\LandingPageTemplate;
use Modules\LandingPagePro\Scopes\UserPermissionScope;
use View;
use function Psl\Str\is_empty;
use function Psl\Type\int;

class LandingPageProController extends AccountBaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('landingpagepro::app.menu.landingpagepro');

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
		$this->viewLfpPermission = $viewPermission = user()->permission('view_landingpage');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$this->pageTitle = __('landingpagepro::app.menu.landingpagepro');
		$this->usertype = $this->user;
		$userId = $this->user->employeeDetail->user_id;

		$landingPages = LandingPage::withoutGlobalScope(ModuleCompanyScope::class)
			->with('landingPageCategory')
			->userPermission($viewPermission, $userId)
			->where('company_id', company()->id)
			->get();
		$this->templatePages = LandingPageTemplate::all();
		$this->lpCategories = LandingPageCategory::all();
		$this->userPageLimit = LandingPageProSetting::value('page_limit');
		$this->userPageCount = LandingPage::count();

		foreach ($landingPages as $landingPage) {
			$landingPage->categoryName = optional($landingPage->landingPageCategory)->name;
			$landingPage->templateName = optional($landingPage->landingPageTemplate)->name;
			$landingPage->statusText = $landingPage->status_text;
		}
		$this->landingPages = $landingPages;

		return view('landingpagepro::index', $this->data);
	}

	public function edit($id)
	{
		$this->viewLfpPermission = $viewPermission = user()->permission('edit_landingpage');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$id = Crypt::decrypt($id);
		$userId = $this->user->employeeDetail->user_id;
		$result = LandingPage::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($viewPermission, $userId)
			->where('company_id', company()->id)
			->find($id);
		if (!$result || $result === null) {
			return response()->json(['error' => 'You are not authorised to edit this template.'], 403);
		}


		$validator = Validator::make(['id' => $id], [
			'id' => 'required|numeric|exists:landing_pages,id',
		]);

		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], 422);
		}

		$userPageLimit = LandingPageProSetting::value('page_limit');
		$userPageCount = LandingPage::count();

		if ($userPageCount > $userPageLimit) {
			return response()->json(['error' => 'User has reached the page creation limit.'], 422);
		}

		$uid = Crypt::encrypt($id);
		$responseData = [
			'formView' => route('template.form', ['id' => $uid]),
			'iframeView' => route('template.page', ['id' => $uid]),
		];

		return response()->json($responseData);
	}

	public function destroy($id)
	{
		$this->viewLfpPermission = $viewPermission = user()->permission('delete_landingpage');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$id = Crypt::decrypt($id);
		$userId = $this->user->employeeDetail->user_id;
		$record = LandingPage::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($viewPermission, $userId)
			->where('company_id', company()->id)
			->find($id);
		if (!$record || $record === null) {
			return response()->json(['error' => 'You are not authorised to edit this template.'], 403);
		}

		$validator = Validator::make(['id' => $id], [
			'id' => 'required|numeric|exists:landing_pages,id',
		]);

		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], 422);
		}

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function builder($id)
	{
		$this->viewLfpPermission = $viewPermission = user()->permission('add_landingpage');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$id = (int)$id;
		$userId = $this->user->employeeDetail->user_id;
		$record = LandingPage::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($viewPermission, $userId)
			->where('company_id', company()->id)
			->where('template_id', $id)
			->get();


		$validator = Validator::make(['id' => $id], [
			'id' => 'required|numeric|exists:landing_page_templates,id',
		]);

		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], 422);
		}

		$userPageLimit = LandingPageProSetting::value('page_limit');

		$userPageCount = LandingPage::count();

		if ($userPageCount > $userPageLimit) {
			return response()->json(['error' => 'User has reached the page creation limit.'], 422);
		}

		$landingPage = LandingPage::updateOrCreate(
			[
				'company_id' => company()->id,
				'template_id' => $id,
				'category_id' => null,
				'user_id' => $this->user->employeeDetail->user_id,
				'status' => 3
			],
			[
				'company_id' => company()->id,
				'template_id' => $id,
				'user_id' => $this->user->employeeDetail->user_id
			]
		);
		$uid = Crypt::encrypt($landingPage->id);
		$responseData = [
			'formView' => route('template.form', ['id' => $uid]),
			'iframeView' => route('template.page', ['id' => $uid]),
		];

		return response()->json($responseData);
	}

	public function form($id)
	{
		$this->viewLfpPermission = $viewPermission = user()->permission('edit_landingpage');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$id = Crypt::decrypt($id);
		$result = LandingPage::find($id);

		// If there is no previous data entered load default content to page
		if (isset($result) && ($result->template_contents == null || is_empty($result->template_contents))) {
			$defaultResult = LandingPageTemplate::find($result->template_id);

			$this->id = Crypt::encrypt($result->id);
			$this->content = json_decode($defaultResult->template_contents, true);
			$this->categories = LandingPageCategory::all();
			$this->selectedCategory = $result->category_id;
			$this->selectedStatus = $result->status;

			$view = 'landingpagepro::templates.form_' . $result->template_id;
			return view($view, $this->data);
		}

		$this->id = Crypt::encrypt($result->id);
		$this->content = json_decode($result->template_contents, true);
		$this->categories = LandingPageCategory::all();
		$this->selectedCategory = $result->category_id;
		$this->selectedStatus = $result->status;

		$view = 'landingpagepro::templates.form_' . $result->template_id;
		return view($view, $this->data);
	}

	public function preview($id)
	{
		$id = Crypt::decrypt($id);
		$result = LandingPage::find($id);

		// If there is no previous data entered, load default content to page from template table
		if (isset($result) && ($result->template_contents == null || is_empty($result->template_contents))) {
			$defaultResult = LandingPageTemplate::find($result->template_id);

			$this->id = Crypt::encrypt($result->id);
			$this->content = json_decode($defaultResult->template_contents, true);

			$view = 'landingpagepro::templates.template_' . $result->template_id;
			return view($view, $this->data);
		}

		$this->content = json_decode($result->template_contents, true);

		$this->view = $view = 'landingpagepro::templates.template_' . $result->template_id;

		return view($view, $this->data);
	}

	public function updatePage(Request $request)
	{
		$this->viewLfpPermission = $viewPermission = user()->permission('edit_landingpage');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$id = (int)(Crypt::decrypt(trim($request->input('uid'))));
		$userId = $this->user->employeeDetail->user_id;
		$record = LandingPage::withoutGlobalScope(ModuleCompanyScope::class)
			->userPermission($viewPermission, $userId)
			->where('company_id', company()->id)
			->find($id);
		if (!$record || $record === null) {
			return response()->json(['error' => 'You are not authorised to edit this template.'], 403);
		}
		//Finance landing Page
		$validator = Validator::make([
			'id' => $id,
			'page_name' => trim($request->input('page_name')),
			'page_category' => trim($request->input('page_category'))
		], [
			'id' => 'required|numeric|exists:landing_pages,id',
			'page_name' => 'required|min:3',
			'page_category' => 'required|min:1'
		]);

		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], 422);
		}

		$userPageLimit = LandingPageProSetting::value('page_limit');
		$userPageCount = LandingPage::count();

		if ($userPageCount > $userPageLimit) {
			return response()->json(['error' => 'User has reached the page creation limit.'], 422);
		}

		$name = trim($request->input('page_name'));
		$category_id = trim($request->input('page_category'));
		$formData = $request->all();
		unset($formData['_token']);
		unset($formData['uid']);

		$landingPage = LandingPage::where('id', $id)
			->where('company_id', company()->id)
			->update([
				'name' => $name,
				'category_id' => $category_id,
				'template_contents' => json_encode($formData),
				'user_id' => $this->user->employeeDetail->user_id,
				'status' => trim($request->input('page_status'))
			]);

		$url = route('template.page', ['id' => trim($request->input('uid'))]);

		return response()->json(['url' => $url], 200)->header('Content-Type', 'application/json');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function storeCategory(Request $request)
	{
		$this->viewLfpPermission = $viewPermission = user()->permission('add_landingpagecategory');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$catCount = LandingPageCategory::where('company_id', company()->id)->count();
		$catMax = LandingPageProSetting::where('package_id', company()->package_id)->value('category_limit');

		if ($catCount >= $catMax) {
			return response()->json([
				'status' => 'error',
				'message' => 'You had already created the allowed number of category.'
			], 403);
		}

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'lpCatName' => 'required|min:3',
		], [
			'lpCatName.required' => 'Please enter the category name you want to create.',
			'lpCatName.min' => 'The category name must be at least :min characters.',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		// Process the validated data
		$lfpCategory = LandingPageCategory::updateOrCreate(
			[
				'id' => $request->input('lpCatId'),
				'company_id' => company()->id
			],
			[
				'company_id' => company()->id,
				'name' => $request->input('lpCatName'),
				//'added_by' => auth()->id,
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
		$this->viewLfpPermission = $viewPermission = user()->permission('edit_landingpagecategory');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$lfpCategory = LandingPageCategory::where('id', $id)
			->first();

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
		$this->viewLfpPermission = $viewPermission = user()->permission('delete_landingpagecategory');
		abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

		$record = LandingPageCategory::where('id', $id)
			->first();

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}
}

