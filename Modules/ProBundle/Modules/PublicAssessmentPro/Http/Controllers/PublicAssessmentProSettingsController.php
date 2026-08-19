<?php

namespace Modules\PublicAssessmentPro\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProSetting;
use App\Models\SuperAdmin\Package;

class PublicAssessmentProSettingsController extends AccountBaseController
{
    public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('publicassessmentpro::app.menu.publicassessmentproSetting');
		$this->activeSettingMenu = 'publicassessmentpro_settings';

		$this->middleware(function ($request, $next) {
			abort_403(!user()->is_superadmin && !in_array(PublicAssessmentProSetting::MODULE_NAME, $this->user->modules));
			return $next($request);
		});
	}

     /**
     * Display a listing of the resource.
     */
    public function index()
    {
		$this->pageTitle = 'Public Assessment Maximum Limit';
		//Get Id of packages
	    $this->excludedPackageIds = $excludedPackageIds = PublicAssessmentProSetting::pluck('package_id');

	    $this->papPackages = $packages = Package::when(user()->is_superadmin, function ($query) {
	    })
		    //->whereNotIn('id', $excludedPackageIds)
		    ->get();

	    $this->papPAPSs = PublicAssessmentProSetting::with(['getPackage' => function($query) {
		    $query->select('id', 'name');
	    }])
		    ->when(user()->is_superadmin, function ($query) {
			    //$query->where('id', null);
		    })
		    ->get();

	    return view('publicassessmentpro::settings.index', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
	    // Validate the request data
	    $validator = Validator::make($request->all(), [
		    'package' => 'required|numeric',
		    'formLimit' => 'required|integer',
	    ], [
		    'package.required' => 'Please select the package you want to set the limit.',
		    'package.numeric' => 'You can select any one from the drop-down list. Please do not alter the value!',
		    'formLimit.required' => 'Please enter the maximum number of assessment you want to allow for the selected package.',
		    'formLimit.integer' => 'Only numeric fields are allowed for setting the form limit',
	    ]);

	    if ($validator->fails()) {
		    return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
		    ], 422);
	    }

	    // Process the validated data
	    $publicAssessmentProSetting = PublicAssessmentProSetting::updateOrCreate(
		    [
			    'package_id' => $request->input('package')
		    ],
		    [
			    'package_id' => $request->input('package'),
			    'assessment_limit' => $request->input('formLimit'),
			    'added_by' => user()->id,
			    'status' => 1
		    ]
	    );

	    // Return a JSON response
	    return response()->json([
		    'status' => 'success',
		    'message' => 'Public s Pro setting created successfully'
	    ], Response::HTTP_CREATED); // 201 status code for resource creation
    }
/**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
	    $publicAssessmentProSetting = PublicAssessmentProSetting::find($id);

	    // Check if the record exists
	    if (!$publicAssessmentProSetting) {
		    return response()->json(['error' => 'Record not found'], 404);
	    }

	    // Return the data as JSON
	    return response()->json([
		    'package_id' => $publicAssessmentProSetting->package_id,
		    'assessment_limit' => $publicAssessmentProSetting->assessment_limit,
	    ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
	    $record = PublicAssessmentProSetting::find($id);

	    if (!$record) {
		    return response()->json(['error' => 'Record not found'], 404);
	    }

	    // Perform deletion
	    $record->delete();

	    return response()->json(['success' => 'Record deleted successfully'], 200);
    }
}
