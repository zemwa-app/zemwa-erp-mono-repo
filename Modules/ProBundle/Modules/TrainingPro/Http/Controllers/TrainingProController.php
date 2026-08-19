<?php

namespace Modules\TrainingPro\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ModuleCompanyScope;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\TrainingPro\Entities\TrainingProAssessment;
use Modules\TrainingPro\Entities\TrainingProAssessmentLog;
use Modules\TrainingPro\Entities\TrainingProAssignee;
use Modules\TrainingPro\Entities\TrainingProCategory;
use Modules\TrainingPro\Entities\TrainingProProgramme;
use Modules\TrainingPro\Entities\TrainingProProgress;
use Modules\TrainingPro\Entities\TrainingProQuestion;
use Modules\TrainingPro\Entities\TrainingProResult;
use Modules\TrainingPro\Entities\TrainingProSetting;
use Modules\TrainingPro\Entities\TrainingProTopic;
use PDO;
use Nwidart\Modules\Facades\Module;

class TrainingProController extends AccountBaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('trainingpro::app.menu.trainingpro');

		$this->middleware(function ($request, $next) {
			abort_403(!user()->is_superadmin && !in_array(TrainingProSetting::MODULE_NAME, $this->user->modules));
			return $next($request);
		});
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		abort_403(!in_array('admin', user_roles()) && user()->permission('view_category') != 'none');

		$this->pageTitle = __('trainingpro::app.menu.trainingpro');
		$this->usertype = $this->user;

		$this->assessments = TrainingProAssessment::where('is_enabled', 1)->count();
		$this->topics = TrainingProTopic::where('is_enabled', 1)->count();
		$this->programmes = TrainingProProgramme::where('is_enabled', 1)->count();
		$this->categories = TrainingProCategory::where('is_enabled', 1)->count();
		//dd(now()->month);
		$this->recentResults = TrainingProAssessmentLog::withoutGlobalScope(ModuleCompanyScope::class)
			->select(
				'users.name as username',
				'training_pro_assessment_logs.score as score',
				'training_pro_assessment_logs.updated_at',
				'training_pro_assessments.name as assessment',
				'training_pro_topics.name as topic'
			)
			->join('users', 'users.id', '=', 'training_pro_assessment_logs.user_id')
			->leftJoin('training_pro_assessments', 'training_pro_assessment_logs.assessment_id', '=', 'training_pro_assessments.id')
			->leftJoin('training_pro_topics', 'training_pro_assessments.programme_id', '=', 'training_pro_topics.programme_id')
			->where('training_pro_assessment_logs.company_id', company()->id)
			->where('training_pro_assessment_logs.assessment_status', 1)
			->whereMonth('training_pro_assessment_logs.updated_at', now()->month)
			->latest('training_pro_assessment_logs.updated_at')
			->groupBy('training_pro_assessments.name')
			->limit(10)
			->get();

		$this->recentAssessments = TrainingProAssessment::withoutGlobalScope(ModuleCompanyScope::class)
			->select('users.name as username', 'training_pro_assessments.*')
			->join('users', 'users.id', '=', 'training_pro_assessments.added_by')
			->with('topic.programmes.category')
			->where('training_pro_assessments.company_id', company()->id)
			->where('training_pro_assessments.is_enabled', 1)
			->whereMonth('training_pro_assessments.created_at', now()->month)
			->latest('training_pro_assessments.created_at')
			->limit(10)
			->get();

		return view('trainingpro::index', $this->data);
	}

	public function config()
	{
		abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('view_category'), ['all', 'added', 'owned', 'both']));

		$this->pageTitle = __('trainingpro::app.menu.trainingproSetting');
		$this->usertype = $this->user;

		$this->tpAssessments = TrainingProAssessment::with('programmes.category')->orderBy('order', 'asc')->get();

		$this->tpCategories = TrainingProCategory::select('id', 'name', 'description', 'is_enabled')->get();
		$this->tpProgrammes = TrainingProProgramme::withoutGlobalScope(ModuleCompanyScope::class)
			->with('category')
			->select('id', 'category_id', 'name', 'description', 'duration', 'order', 'is_enabled')
			->orderBy('order', 'asc')
			->get();
		$this->tpTopics = TrainingProTopic::withoutGlobalScope(ModuleCompanyScope::class)
			->with('programmes.category')
			->select('id', 'programme_id', 'name', 'description', 'type', 'value', 'order', 'is_enabled')
			->where('company_id', company()->id)
			->orderBy('order', 'asc')
			->get();
		$this->assignees = TrainingProAssignee::withoutGlobalScope(ModuleCompanyScope::class)
			->with('category', 'programme', 'department', 'designation', 'user')
			->where('company_id', company()->id)
			->orderBy('created_at', 'asc')
			->get();

		return view('trainingpro::config', $this->data);
	}

	public function results()
	{
		abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('view_category'), ['all', 'added', 'owned', 'both']));

		$this->pageTitle = __('trainingpro::app.menu.trainingproResult');

		$this->recentResults = TrainingProAssessmentLog::withoutGlobalScope(ModuleCompanyScope::class)
			->with('assessment', 'user')
			->select('*')
			->where('company_id', company()->id)
			->where('assessment_status', 1)
			->latest('updated_at')
			->get();
			//->paginate(20);

		return view('trainingpro::results', $this->data);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function createCategory(Request $request)
	{
		$userPermission = user()->permission('add_category');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'tpCategoryId' => 'nullable|integer',
			'tpCategory' => 'required',
			'tpDescription' => 'nullable',
			'tpStatus' => 'nullable|integer',
		], [
			'tpCategory.required' => 'Please enter the category name.',
			'docCategoryId.integer' => 'Only numeric fields are allowed for id.',
			'tpStatus.integer' => 'Please do not alter the value of Status.',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		try {
			$tpCategoryId = $request->input('tpCategoryId');
			$tpCategory = $request->input('tpCategory');
			$tpDescription = $request->input('tpDescription');
			$tpStatus = $request->input('tpStatus');

			TrainingProCategory::updateOrCreate(
				[
					'id' => $tpCategoryId
				],
				[
					'name' => $tpCategory,
					'description' => $tpDescription,
					'company_id' => company()->id,
					'is_enabled' => $tpStatus,
					'added_by' => auth()->id(),
					'status' => 1
				]
			);

			// Return a JSON response
			return response()->json([
				'status' => 'success',
				'message' => 'Successfully created assessment category.'
			], Response::HTTP_CREATED); // 201 status code for resource creation
		} catch (\Exception $e) {
			return response()->json([
				'status' => 'error',
				'message' => 'Server Error: ' . $e->getMessage(),
			], 500); // 500 status code for internal server error
		}
	}

	public function editCategory($id)
	{
		$userPermission = user()->permission('edit_category');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		$tProCategory = TrainingProCategory::select('id', 'name', 'description', 'is_enabled')
			->where('id', $id)
			->first();

		// Check if the record exists
		if (!$tProCategory) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'tpCategoryId' => $tProCategory->id,
			'tpCategory' => $tProCategory->name,
			'tpDescription' => $tProCategory->description,
			'tpStatus' => $tProCategory->is_enabled,
		]);
	}

	public function destroyCategory($id)
	{
		$userPermission = user()->permission('delete_category');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		$record = TrainingProCategory::where('id', $id)->first();

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}
		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function createProgramme(Request $request)
	{
		$userPermission = user()->permission('add_program');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'tpProgrammeId' => 'nullable|integer',
			'tpProCategory' => 'required|integer',
			'tpProgramme' => 'required',
			'tpProDescription' => 'nullable',
			'tpProDuration' => 'required|integer',
			'tpProOrder' => 'required|integer',
			'tpProStatus' => 'nullable|integer',
		], [
			'tpProgrammeId.integer' => 'Only numeric fields are allowed for id.',
			'tpProCategory.required' => 'Please select category.',
			'tpProCategory.integer' => 'Please select category.',
			'tpProgramme.required' => 'Please enter the programme name.',
			'tpProDuration.required' => 'Please enter the programme duration(in mins).',
			'tpProDuration.integer' => 'The programme duration should only be a number.',
			'tpProOrder.required' => 'Please enter the programme order.',
			'tpProOrder.integer' => 'The programme order should only be a number.',
			'tpProStatus.integer' => 'Please do not alter the value of Status.',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		try {
			$tpProgrammeId = $request->input('tpProgrammeId');
			$tpProCategory = $request->input('tpProCategory');
			$tpProgramme = $request->input('tpProgramme');
			$tpProDescription = $request->input('tpProDescription');
			$tpProDuration = $request->input('tpProDuration');
			$tpProOrder = $request->input('tpProOrder');
			$tpProStatus = $request->input('tpProStatus');

			TrainingProProgramme::updateOrCreate(
				[
					'id' => $tpProgrammeId
				],
				[
					'category_id' => $tpProCategory,
					'name' => $tpProgramme,
					'description' => $tpProDescription,
					'company_id' => company()->id,
					'duration' => $tpProDuration,
					'order' => $tpProOrder,
					'is_enabled' => $tpProStatus,
					'added_by' => auth()->id(),
					'status' => 1
				]
			);

			// Return a JSON response
			return response()->json([
				'status' => 'success',
				'message' => 'Successfully created assessment programme.'
			], Response::HTTP_CREATED); // 201 status code for resource creation
		} catch (\Exception $e) {
			return response()->json([
				'status' => 'error',
				'message' => 'Server Error: ' . $e->getMessage(),
			], 500); // 500 status code for internal server error
		}
	}

	public function editProgramme($id)
	{
		$userPermission = user()->permission('edit_program');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		$tpProgramme = TrainingProProgramme::select('id', 'category_id', 'name', 'description', 'duration', 'order', 'is_enabled')
			->where('id', $id)
			->first();

		// Check if the record exists
		if (!$tpProgramme) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'tpProgrammeId' => $tpProgramme->id,
			'tpProCategory' => $tpProgramme->category_id,
			'tpProgramme' => $tpProgramme->name,
			'tpProDescription' => $tpProgramme->description,
			'tpProDuration' => $tpProgramme->duration,
			'tpProOrder' => $tpProgramme->order,
			'tpProStatus' => $tpProgramme->is_enabled,
		]);
	}

	public function destroyProgramme($id)
	{
		$userPermission = user()->permission('delete_program');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		$record = TrainingProProgramme::where('id', $id)->first();

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}
		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function createTopic(Request $request)
	{
		$userPermission = user()->permission('add_topic');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'tpTopicId' => 'nullable|integer',
			'tpTopCategory' => 'required|integer',
			'tpTopProgramme' => 'required|integer',
			'tpTopic' => 'required',
			'tpTopicType' => 'required|in:video,pdf,presentation',
			'tpTopicValue' => 'required',
			'tpTopDescription' => 'nullable',
			'tpTopOrder' => 'required|integer',
			'tpTopStatus' => 'nullable|integer',
		], [
			'tpTopicId.integer' => 'Please do not manually alter any submitted value.',
			'tpTopCategory.required' => 'Please select category.',
			'tpTopCategory.integer' => 'Please do not manually alter any submitted value.',
			'tpTopProgramme.required' => 'Please select programme.',
			'tpTopProgramme.integer' => 'Please do not manually alter any submitted value.',
			'tpTopic.required' => 'Please enter the topic name.',
			'tpTopicType.required' => 'Please select the type of the topic.',
			'tpTopicType.in' => 'The type of the topic must be one of: Video, PDF, Slideshow/Presentation.',
			'tpTopicValue.required' => 'Please enter the topic value (link only).',
			'tpTopOrder.required' => 'Please enter topic order.',
			'tpTopOrder.integer' => 'Topic order value should be a number.',
			'tpTopStatus.integer' => 'Please do not alter the value of Status.',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		try {
			$tpTopicId = $request->input('tpTopicId');
			$tpTopCategory = $request->input('tpTopCategory');
			$tpTopProgramme = $request->input('tpTopProgramme');
			$tpTopic = $request->input('tpTopic');
			$tpTopicType = $request->input('tpTopicType');
			$tpTopicValue = $request->input('tpTopicValue');
			$tpTopDescription = $request->input('tpTopDescription');
			$tpTopOrder = $request->input('tpTopOrder');
			$tpTopStatus = $request->input('tpTopStatus');

			TrainingProTopic::updateOrCreate(
				[
					'id' => $tpTopicId
				],
				[
					'programme_id' => $tpTopProgramme,
					'name' => $tpTopic,
					'description' => $tpTopDescription,
					'type' => $tpTopicType,
					'value' => $tpTopicValue,
					'company_id' => company()->id,
					'order' => $tpTopOrder,
					'is_enabled' => $tpTopStatus,
					'added_by' => auth()->id(),
					'status' => 1
				]
			);

			// Return a JSON response
			return response()->json([
				'status' => 'success',
				'message' => 'Successfully created assessment topic.'
			], Response::HTTP_CREATED); // 201 status code for resource creation
		} catch (\Exception $e) {
			return response()->json([
				'status' => 'error',
				'message' => 'Server Error: ' . $e->getMessage(),
			], 500); // 500 status code for internal server error
		}
	}

	public function editTopic($id)
	{
		$userPermission = user()->permission('edit_topic');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		$tpProgramme = TrainingProTopic::withoutGlobalScope(ModuleCompanyScope::class)
			->with(['programmes.category'])
			->select('id', 'programme_id', 'name', 'description', 'type', 'value', 'order', 'is_enabled')
			->where('id', $id)
			->where('company_id', company()->id)
			->first();

		// Check if the record exists
		if (!$tpProgramme) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'tpTopicId' => $tpProgramme->id,
			'tpTopCategory' => $tpProgramme->programmes->category->id,
			'tpTopProgramme' => $tpProgramme->programme_id,
			'tpTopic' => $tpProgramme->name,
			'tpTopDescription' => $tpProgramme->description,
			'tpTopicType' => $tpProgramme->type,
			'tpTopicValue' => $tpProgramme->value,
			'tpTopOrder' => $tpProgramme->order,
			'tpTopStatus' => $tpProgramme->is_enabled,
		]);
	}

	public function destroyTopic($id)
	{
		$userPermission = user()->permission('delete_topic');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		$record = TrainingProTopic::where('id', $id)->first();

		if (!$record) {
			return response()->json(['error' => 'Record not found'], 404);
		}
		// Perform deletion
		$record->delete();

		return response()->json(['success' => 'Record deleted successfully'], 200);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function createAssessment(Request $request)
	{
		$userPermission = user()->permission('add_assessment');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'tpAssessmentId' => 'nullable|integer',
			'tpAssessProgramme' => 'required|integer',
			'tpAssessment' => 'required',
			'tpAssessMaxScore' => 'required|integer',
			'tpAssessMinScore' => 'required|integer',
			'tpAssessDuration' => 'required|integer',
			'tpAssessDescription' => 'nullable',
			'tpAssessOrder' => 'required|integer',
			'tpAssessStatus' => 'nullable|integer',
		], [
			'tpAssessmentId.integer' => 'Please do not manually alter any submitted value.',
			'tpAssessProgramme.required' => 'Please select topic.',
			'tpAssessProgramme.integer' => 'Please do not manually alter any submitted value.',
			'tpAssessment.required' => 'Please enter the topic name.',
			'tpAssessMaxScore.required' => 'Please enter the max. score (in number).',
			'tpAssessMaxScore.integer' => 'Max. Score value should be a number.',
			'tpAssessMinScore.required' => 'Please enter the min. score (in number).',
			'tpAssessMinScore.integer' => 'Min. Score value should be a number.',
			'tpAssessDuration.required' => 'Please enter the assessment duration (Enter 0 for no time restriction and duration should be in minutes).',
			'tpAssessDuration.integer' => 'Assessment duration should be a number.',
			'tpTopicType.in' => 'The type of the topic must be one of: Video, PDF, Slideshow/Presentation.',
			'tpTopicValue.required' => 'Please enter the topic value (link only).',
			'tpAssessOrder.required' => 'Please enter assessment order.',
			'tpAssessOrder.integer' => 'Topic order value should be a number.',
			'tpAssessStatus.integer' => 'Please do not alter the value of Status.',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status' => 'error',
				'message' => $validator->errors()
			], 422);
		}

		try {
			$tpAssessmentId = $request->input('tpAssessmentId');
			$tpAssessProgramme = $request->input('tpAssessProgramme');
			$tpAssessment = $request->input('tpAssessment');
			$tpAssessDescription = $request->input('tpAssessDescription');
			$tpAssessMaxScore = $request->input('tpAssessMaxScore');
			$tpAssessMinScore = $request->input('tpAssessMinScore');
			$tpAssessDuration = $request->input('tpAssessDuration');
			$tpAssessOrder = $request->input('tpAssessOrder');
			$tpAssessStatus = $request->input('tpAssessStatus');

			TrainingProAssessment::updateOrCreate(
				[
					'id' => $tpAssessmentId
				],
				[
					'programme_id' => $tpAssessProgramme,
					'name' => $tpAssessment,
					'description' => $tpAssessDescription,
					'max_score' => $tpAssessMaxScore,
					'min_score' => $tpAssessMinScore,
					'duration' => $tpAssessDuration,
					'company_id' => company()->id,
					'order' => $tpAssessOrder,
					'is_enabled' => $tpAssessStatus,
					'added_by' => auth()->id(),
					'status' => 1
				]
			);

			// Return a JSON response
			return response()->json([
				'status' => 'success',
				'message' => 'Successfully created assessment.'
			], Response::HTTP_CREATED); // 201 status code for resource creation
		} catch (\Exception $e) {
			return response()->json([
				'status' => 'error',
				'message' => 'Server Error: ' . $e->getMessage(),
			], 500); // 500 status code for internal server error
		}
	}

	public function editAssessment($id)
	{
		$userPermission = user()->permission('edit_assessment');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		$tpAssessment = TrainingProAssessment::withoutGlobalScope(ModuleCompanyScope::class)
			->with(['programmes.category'])
			->select('id', 'programme_id', 'name', 'description', 'max_score', 'min_score', 'duration', 'order', 'is_enabled')
			->where('id', $id)
			->where('company_id', company()->id)
			->first();

		// Check if the record exists
		if (!$tpAssessment) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		// Return the data as JSON
		return response()->json([
			'tpAssessmentId' => $tpAssessment->id,
			'tpAssessCategory' => $tpAssessment->programmes->category->id,
			'tpAssessProgramme' => $tpAssessment->programme_id,
			'tpAssessment' => $tpAssessment->name,
			'tpAssessDescription' => $tpAssessment->description,
			'tpAssessMaxScore' => $tpAssessment->max_score,
			'tpAssessMinScore' => $tpAssessment->min_score,
			'tpAssessDuration' => $tpAssessment->duration,
			'tpAssessOrder' => $tpAssessment->order,
			'tpAssessStatus' => $tpAssessment->is_enabled,
		]);
	}

	public function destroyAssessment($id)
	{
		$userPermission = user()->permission('delete_assessment');
		abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

		try {
			$record = TrainingProAssessment::where('id', $id)->first();

			if (!$record) {
				return response()->json(['error' => 'Record not found'], 404);
			}
			$record->delete();

			return response()->json(['success' => 'Record deleted successfully'], 200);
		} catch (\Illuminate\Database\QueryException $e) {
			if ($e->errorInfo[1] === 1451) {
				return response()->json(['error' => 'Cannot delete the assessment due to existing dependencies.'], 422);
			} else {
				return response()->json(['error' => 'Failed to delete the record'], 500);
			}
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to delete the record'], 500);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function showQa($id)
	{
		abort_403(User::isAdmin(auth()->user()->id));

		$this->viewPermission = user()->permission('view_category');
		abort_403(!in_array($this->viewPermission, ['all', 'added', 'both', 'owned']));

		$this->pageTitle = __('trainingpro::app.header.qaTitle');
		$this->usertype = $this->user;

		$this->tpAssessment = TrainingProAssessment::findOrFail($id);
		$this->tpQuestions = TrainingProQuestion::with('answers')->where('assessment_id', $id)->get();

		if (request()->ajax()) {
			$html = view('trainingpro::ajax.show', $this->data)->render();
			return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
		}

		$this->view = 'trainingpro::ajax.show';
		return view('trainingpro::qaconfig', $this->data);

	}

	public function createQa($id)
	{
		abort_403(User::isAdmin(auth()->user()->id));

		$this->viewPermission = user()->permission('view_category');
		abort_403(!in_array($this->viewPermission, ['all', 'added', 'both', 'owned']));

		$this->pageTitle = __('trainingpro::app.header.addQa');
		$this->usertype = $this->user;

		$this->tpAssessment = TrainingProAssessment::findOrFail($id);

		if (request()->ajax()) {
			$html = view('trainingpro::ajax.create', $this->data)->render();
			return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
		}

		$this->view = 'trainingpro::ajax.create';
		return view('trainingpro::qaconfig', $this->data);
	}

	public function storeQa(Request $request)
	{
		abort_403(User::isAdmin(auth()->user()->id));

		$this->viewPermission = user()->permission('view_category');
		abort_403(!in_array($this->viewPermission, ['all', 'added', 'both', 'owned']));

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'quesId' => 'nullable|integer',
			'assessmentId' => 'required|integer',
			'assesQa' => 'required',
			'assesMark' => 'required|integer',
			'rightAns' => 'required',
			'assesAnsOne' => 'required',
			'assesAnsTwo' => 'required',
			'tpQsStatus' => 'nullable|integer',
		], [
			'quesId.integer' => 'Invalid data format.',
			'assessmentId.required' => 'Assessment ID is required.',
			'assessmentId.integer' => 'Invalid data format for Assessment ID.',
			'assesQa.required' => 'Assessment question is mandatory.',
			'assesMark.required' => 'Mark field is required.',
			'assesMark.integer' => 'Mark must be a number.',
			'rightAns.required' => 'Select the correct answer.',
			'assesAnsOne.required' => 'Provide at least two answer options.',
			'assesAnsTwo.required' => 'Provide at least two answer options.',
			'tpQsStatus.integer' => 'Invalid data format for Status.',
		]);

		if ($validator->fails()) {
			return Reply::formErrors($validator->errors());
		}

		$quesId = $request->input('quesId');
		$assessmentId = $request->input('assessmentId');
		$assesQa = $request->input('assesQa');
		$assesMark = $request->input('assesMark');
		$rightAns = $request->input('rightAns');
		$assesAnsOne = $request->input('assesAnsOne');
		$assesAnsTwo = $request->input('assesAnsTwo');
		$assesAnsThree = $request->has('assesAnsThree') ? $request->input('assesAnsThree') : '';
		$assesAnsFour = $request->input('assesAnsFour');
		$tpQsStatus = $request->input('tpQsStatus');

		$maxMark = TrainingProAssessment::where('id', $assessmentId)->value('max_score');
		$totalMarks = TrainingProQuestion::where('assessment_id', $assessmentId)->sum('mark');

		if (($totalMarks + $assesMark) > $maxMark) {
			return Reply::error('Please ensure the total score does not exceed the allowed maximum mark.', 'error', ['Limit exceeded']);
		}

		try {
			DB::beginTransaction();
			$question = TrainingProQuestion::updateOrCreate(
				[
					'id' => $quesId,
				],
				[
					'company_id' => company()->id,
					'assessment_id' => $assessmentId,
					'question' => $assesQa,
					'correct_answer' => $rightAns,
					'mark' => $assesMark,
					'is_enabled' => $tpQsStatus
				]
			);

			// Save or update the answers
			$answerData = [
				['company_id' => company()->id, 'ans_code' => 'a1', 'option_text' => $assesAnsOne],
				['company_id' => company()->id, 'ans_code' => 'a2', 'option_text' => $assesAnsTwo],
				['company_id' => company()->id, 'ans_code' => 'a3', 'option_text' => $assesAnsThree],
				['company_id' => company()->id, 'ans_code' => 'a4', 'option_text' => $assesAnsFour],
			];

			foreach ($answerData as $data) {
				$question->answers()->updateOrCreate(
					[
						'question_id' => $quesId,
						'option_text' => $data['option_text'],
					],
					$data
				);
			}

			DB::commit();
			// Return a JSON response
			return Reply::successWithData(__('trainingpro::app.message.recordSaved'), ['redirectUrl' => route('config.home')]);
		} catch (\Exception $e) {
			DB::rollback();
			return Reply::error('Server Error:', 'error', $e->getMessage());
		}
	}

	public function editQa($aid, $qid)
	{
		abort_403(User::isAdmin(auth()->user()->id));

		$this->viewPermission = user()->permission('view_category');
		abort_403(!in_array($this->viewPermission, ['all', 'added', 'both', 'owned']));

		$this->pageTitle = __('trainingpro::app.menu.trainingproSetting');
		$this->usertype = $this->user;

		$this->tpAssessment = TrainingProAssessment::findOrFail($aid);
		$this->tpQuestion = TrainingProQuestion::with('answers')->where('id', $qid)->first();

		if (request()->ajax()) {
			$html = view('trainingpro::ajax.edit', $this->data)->render();
			return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
		}

		$this->view = 'trainingpro::ajax.edit';
		return view('trainingpro::qaconfig', $this->data);

	}

	public function destroyQa($id)
	{
		try {
			$id = (int)$id;
			$record = TrainingProQuestion::where('id', $id)->first();
			if (!$record) {
				return Reply::error('Record not found.', 'error', ['Record not found']);
			}
			$record->delete();

			return Reply::successWithData(__('trainingpro::app.message.recordDeleted'), ['redirectUrl' => route('config.home')]);
		} catch (\Illuminate\Database\QueryException $e) {
			if ($e->errorInfo[1] === 1451) {
				return Reply::error('Cannot delete the assessment due to existing dependencies.', 'error', ['Cannot delete the assessment due to existing dependencies']);
			} else {
				return Reply::error('Failed to delete the record.', 'error', ['Failed to delete the record']);
			}
		} catch (\Exception $e) {
			return Reply::error('Failed to delete the record.', 'error', ['Failed to delete the record']);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function createAssignee()
	{
		abort_403(User::isAdmin(auth()->user()->id));

		$this->viewPermission = $addPermission = user()->permission('view_category');
		abort_403(!in_array($this->viewPermission, ['all', 'added', 'both', 'owned']));

		$this->pageTitle = __('trainingpro::app.header.addAssignee');
		$this->usertype = $this->user;

		$this->employees = User::allEmployees(null, true, ($addPermission == 'all' ? 'all' : null));
		$this->departments = Team::allDepartments();
		$this->designations = Designation::allDesignations();
		$this->categories = TrainingProCategory::allCategories();

		if (request()->ajax()) {
			$html = view('trainingpro::ajax.create-assignee', $this->data)->render();
			return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
		}

		$this->view = 'trainingpro::ajax.create-assignee';
		return view('trainingpro::qaconfig', $this->data);
	}

	public function storeAssignee(Request $request)
	{
		abort_403(User::isAdmin(auth()->user()->id));

		$this->viewPermission = user()->permission('view_category');
		abort_403(!in_array($this->viewPermission, ['all', 'added', 'both', 'owned']));

		// Validate the request data
		$validator = Validator::make($request->all(), [
			'assigneeId' => 'nullable|integer',
			//'department_id' => 'required|integer',
			'department_id' => ['required', 'integer', Rule::notIn([0]),],
			'designation' => 'nullable|integer',
			'user_id' => 'nullable|integer',
			//'assignment_category' => 'required|integer',
			'assignment_category' => ['required', 'integer', Rule::notIn([0]),],
			'assignment_programme' => 'nullable|integer',
			'assignee_order' => 'required|integer',
			'assignee_status' => 'nullable|integer',
		], [
			'assigneeId.integer' => 'Invalid data format.',
			'department_id.required' => 'Department is required.',
			'department_id.not_in' => 'Department is required.',
			'department_id.integer' => 'Invalid data format for Department.',
			'designation.integer' => 'Invalid data format for Designation.',
			'user_id.integer' => 'Invalid data format for User.',
			'assignment_category.required' => 'Assignment category is required.',
			'assignment_category.not_in' => 'Assignment category is required.',
			'assignment_category.integer' => 'Invalid data format for Assignment category.',
			'assignment_programme.integer' => 'Invalid data format for Assignment programme.',
			'assignee_order.required' => 'Assignment order is required.',
			'assignee_order.integer' => 'Invalid data format for Assignment order.',
			'assignee_status.integer' => 'Invalid data format for Status.',
		]);

		if ($validator->fails()) {
			return Reply::formErrors($validator->errors());
		}
		$assigneeId = $request->input('assigneeId');
		$department_id = $request->input('department_id');
		$designation = $request->input('designation') ?: null;
		$user_id = $request->input('user_id') ?: null;
		$assignment_category = $request->input('assignment_category');
		$assignment_programme = $request->input('assignment_programme') ?: null;
		$assignee_order = $request->input('assignee_order');
		$assignee_status = $request->input('assignee_status');

		try {
			DB::beginTransaction();
			TrainingProAssignee::updateOrCreate(
				[
					'id' => $assigneeId,
				],
				[
					'company_id' => company()->id,
					'user_id' => $user_id,
					'role_id' => null,
					'designation_id' => $designation,
					'department_id' => $department_id,
					'category_id' => $assignment_category,
					'programme_id' => $assignment_programme,
					'order' => $assignee_order,
					($assigneeId ? 'updated_by' : 'added_by') => auth()->user()->id,
					'is_enabled' => $assignee_status,
				]
			);
			DB::commit();
			// Return a JSON response
			return Reply::successWithData(__('trainingpro::app.message.recordSaved'), ['redirectUrl' => route('config.home')]);
		} catch (\Exception $e) {
			DB::rollback();
			return Reply::error('Server Error:', 'error', $e->getMessage());
		}
	}

	public function editAssignee($id)
	{
		abort_403(User::isAdmin(auth()->user()->id));

		$this->viewPermission = $addPermission = user()->permission('view_category');
		abort_403(!in_array($this->viewPermission, ['all', 'added', 'both', 'owned']));

		$this->pageTitle = __('trainingpro::app.header.editAssignee');
		$this->usertype = $this->user;

		$this->assignee = TrainingProAssignee::findOrFail($id);
		$this->departments = Team::allDepartments();
		$this->categories = TrainingProCategory::allCategories();
		$this->designations = Designation::allDesignations();

		$deptId = $this->assignee->department_id ?? 0;
		$desigId = $this->assignee->designation_id ?? 0;
		$this->employees = $this->byDepartment($deptId, $desigId, 'web');

		$catId = $this->assignee->category_id ?? 0;
		$this->programmes = $this->byCategory($catId, 'web');

		if (request()->ajax()) {
			$html = view('trainingpro::ajax.edit-assignee', $this->data)->render();
			return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
		}

		$this->view = 'trainingpro::ajax.edit-assignee';
		return view('trainingpro::qaconfig', $this->data);

	}

	public function destroyAssignee($id)
	{
		try {
			$id = (int)$id;
			$record = TrainingProAssignee::where('id', $id)->first();
			if (!$record) {
				return Reply::error('Record not found.', 'error', ['Record not found']);
			}
			$record->delete();

			return Reply::successWithData(__('trainingpro::app.message.recordDeleted'), ['redirectUrl' => route('config.home')]);
		} catch (\Illuminate\Database\QueryException $e) {
			if ($e->errorInfo[1] === 1451) {
				return Reply::error('Cannot delete the assessment due to existing dependencies.', 'error', ['Cannot delete the assessment due to existing dependencies']);
			} else {
				return Reply::error('Failed to delete the record.', 'error', ['Failed to delete the record']);
			}
		} catch (\Exception $e) {
			return Reply::error('Failed to delete the record.', 'error', ['Failed to delete the record']);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function trainings()
	{
		abort_403(!in_array('employee', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none');
		abort_403(in_array('admin', user_roles()));

		$this->pageTitle = __('trainingpro::app.header.trainingTitle');
		$this->userDetails = $userDetails = $this->user;

		if (User::isAdmin(user()->employeeDetail->user_id) === true) {
			$this->completedTrainings = array();
			$this->totalAssessments = array();
			$this->userTrainings = array();
			$this->totalTopics = 0;
		} else {
			$this->completedTrainings = TrainingProProgress::where('user_id', user()->employeeDetail->user_id)->get();
			$this->completedAssessments = TrainingProAssessmentLog::where('user_id', user()->employeeDetail->user_id)->get();
			$assignedTrainings = TrainingProAssignee::withoutGlobalScope(ModuleCompanyScope::class)
				->with(['programme.topics.assessment', 'category.programmes.topics', 'programme.assessment'])
				->select('*')
				->where('company_id', company()->id)
				->where('department_id', $userDetails->employeeDetail->department->id)
				->groupBy('programme_id')
				->get();

			$this->totalAssessments = $assignedTrainings->sum(function ($item) {
				//return $item->programme->topics->pluck('assessment')->unique()->flatten()->count();
				return $item->programmes->pluck('topics')->flatten()
					->whereNotNull('assessment')
					->unique()
					->count();
			});

			$this->totalTopics = $assignedTrainings->sum(function ($item) {
				return $item->programme->topics->count();// + $item->category->programmes->pluck('topics')->flatten()->count();
			});

			$this->userTrainings = TrainingProAssignee::withoutGlobalScope(ModuleCompanyScope::class)
				->with('category', 'programme.topics')
				->where('training_pro_assignees.company_id', company()->id)
				->where('training_pro_assignees.is_enabled', 1)
				->where(function ($query) use ($userDetails) {
					$query->where('department_id', $userDetails->employeeDetail->department->id);

					$query->orWhere(function ($subQuery) use ($userDetails) {
						$subQuery->where('user_id', user()->employeeDetail->user_id);
						$subQuery->orWhere('designation_id', $userDetails->employeeDetail->designation->id);
					});
				})
				->select(
					'training_pro_assignees.*',
					'users.name AS user_name',
					'teams.team_name AS department_name',
					'designations.name AS designation_name',
					DB::raw('0 AS status')
				)
				->leftJoin('users', 'users.id', '=', 'training_pro_assignees.user_id')
				->leftJoin('teams', 'teams.id', '=', 'training_pro_assignees.department_id')
				->leftJoin('designations', 'designations.id', '=', 'training_pro_assignees.designation_id')
				->orderBy('training_pro_assignees.programme_id', 'asc')
				->groupBy('programme_id')
				->get();
		}

		return view('trainingpro::trainings', $this->data);
	}

	public function startTraining($id)
	{
		abort_403(!in_array('employee', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none');

		$this->tpProgramme = $tpProgramme = TrainingProProgramme::with('assessment')->findOrFail($id);

		if (TrainingProAssignee::isAssignedUser($id)) {

			$this->tpProgress = TrainingProProgress::updateOrCreateProgress($id);

			$this->tpTrainings = TrainingProTopic::select('*')
				->where('programme_id', $id)
				->where('is_enabled', 1)
				->orderBy('order', 'asc')
				->get();
		} else {
			$this->tpTrainings = array();
		}

		$this->pageTitle = __('trainingpro::app.header.startTraining') . ': ' . $tpProgramme->name;
		$this->userDetails = $this->user;

		if (request()->ajax()) {
			$html = view('trainingpro::ajax.start-training', $this->data)->render();
			return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
		}

		$this->view = 'trainingpro::ajax.start-training';
		return view('trainingpro::qaconfig', $this->data);
	}

	public function exitTraining($id)
	{
		if (TrainingProAssignee::isAssignedUser($id)) {
			$result = TrainingProProgress::updateOrCreateProgress($id, 'exit');
		}
		return $result;
	}

	public function assessments()
	{
		abort_403(!in_array('employee', user_roles()) && user()->permission('create_category') != 5 && user()->permission('view_category') != 'none');
		abort_403(in_array('admin', user_roles()));

		$this->pageTitle = __('trainingpro::app.menu.trainingpro');

		$this->recentResults = TrainingProAssessmentLog::withoutGlobalScope(ModuleCompanyScope::class)
			->with('assessment')
			->select('*')
			->where('company_id', company()->id)
			->where('user_id', $this->user->employeeDetail->user_id)
			->where('assessment_status', 1)
			->latest('updated_at')
			->get();

		return view('trainingpro::assessments', $this->data);
	}

	public function startAssessment($id)
	{
		abort_403(!in_array('employee', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none');

		$this->tpAssessment = $tpAssessment = TrainingProAssessment::findOrFail($id);

		if (!$tpAssessment || !(TrainingProAssignee::isAssignedUser($tpAssessment->id))) {
			return Reply::error('You are not authorized to take this assessment.', 'error', ['You are not authorized to take this assessment.']);
		}
		$this->pageTitle = __('trainingpro::app.header.startAssessment');
		$this->userDetails = $this->user;

		return view('trainingpro::ajax.start-assessment', $this->data);
	}

// TrainingProAssignee
// TrainingProAssessment
// TrainingProTopic
	public function getQans($id, $type = 'ajax')
	{
		$id = (int)$id;
		$tpAssessment = TrainingProAssessment::findOrFail($id);

		if (!$tpAssessment) {
			TrainingProAssessmentLog::assessmentLog($id, null, null, true);
			return Reply::error('You are not authorize to take this assessment.', 'error', ['You are not authorize to take this assessment.']);
		}

		$assessmentLog = TrainingProAssessmentLog::assessmentLog($id, null, null, 0);

		if (!$assessmentLog || $assessmentLog->duration_took >= $tpAssessment->duration) {
			return Reply::error('You cannot take this assessment as your allocated time is over. Contact your Manager or Admin.', 'error', ['You cannot take this assessment as your allocated time is over. Contact your Manager or Admin.']);
		}

		$assessment = TrainingProAssessment::with([
			'questions' => function ($query) use ($id) {
				$assessment = TrainingProAssessment::find($id);
				$maxQuestions = $assessment->max_ques; // Access max_ques value

				$query->where('is_enabled', true) // Filter enabled questions
				->inRandomOrder()
					->limit($maxQuestions);
			},
			'questions.answers' => function ($query) {
				$query->orderBy('ans_code'); // Order answers by code
			}
		])->find($id);

		if ($type === 'ajax') {
			$htmlContent = '<form id="assessmentForm" method="POST">';

			foreach ($assessment->questions as $index => $question) {
				$htmlContent .= '
					<div class="container-fluid my-1">
						<div class="row p-1">
					  		<div class="col-sm-11 m-auto bg-white py-2">
						  		<div class="col-12">
									<h3 class="heading-h3">' . ($index + 1) . '. ' . $question->question . '</h3>
						  		</div>
						  		<div class="col-12">
						  			<div class="row">';

				foreach ($question->answers as $answer) {
					$htmlContent .= '
										<div class="col-sm-6 py-2">
										<input id="a' . $answer->id . '" name="q' . $question->id . '" value="' . $answer->ans_code . '"type="radio">&nbsp;
										<label for="a' . $answer->id . '">
							  ' . $answer->option_text . '
										</label></div>';
				}
				// Add blank divs if needed to make it 4 answers
				for ($i = count($question->answers); $i < 2; $i++) {
					$htmlContent .= '
										<div class="col-sm-6 py-2"></div>';
				}
				$htmlContent .= '
									</div>
					  			</div>
					  		</div>
						</div>
					</div>';

			}
			$htmlContent .= '</form>';
			$qBlock = $htmlContent;

			session()->put('assessmentKey', $assessmentLog->id); // Set Assessment Start Key
			session()->put('assessmentStamp', $assessmentLog->started_at); // Set Assessment Start Stamp

			return Reply::dataOnly(['status' => 'success', 'data' => $qBlock]);
		} else {
			return $assessment;
		}
	}

	public function updateAssessmentStamp(Request $request)
	{
		if (session()->has('assessmentKey') && session()->has('assessmentStamp')) {
			$assessmentKey = session()->get('assessmentKey');
			$data = $request->except(['_token', 'status']);
			$status = $request->input('status');
			$isAssessmentDone = $status === true || $status === 'true' ? 1 : 0;
			$assessmentData = json_encode($data);

			$response = TrainingProAssessmentLog::assessmentLog(null, $assessmentKey, $assessmentData, $isAssessmentDone);
			return ['status' => 'success', 'data' => $response];
		} else {
			// Session variable does not exist
			return ['status' => 'failed', 'data' => 'No session found.'];
		}
	}

	public function finishAssessment()
	{
		if (session()->has('assessmentKey') && session()->has('assessmentStamp')) {

			$assessmentId = session()->get('assessmentKey');
			$assessmentLog = TrainingProAssessmentLog::withoutGlobalScope(ModuleCompanyScope::class)
				->select('assessment_data', 'assessment_id')
				->where('id', $assessmentId)
				->where('assessment_status', '=', 1)
				->first();

			if ($assessmentLog) { // Check if assessment log exists
				$assessment = TrainingProAssessment::withoutGlobalScope(ModuleCompanyScope::class)
					->select('programme_id', 'max_score', 'min_score')
					->where('id', $assessmentLog->assessment_id)
					->first();

				$dataArray = json_decode(json_decode($assessmentLog->assessment_data, true), true);
				$assessmentData = [];
				foreach ($dataArray as $key => $value) {
					$newKey = intval(substr($key, 1));
					$assessmentData[$newKey] = $value;
				}

				$totalMark = 0;
				foreach ($assessmentData as $key => $value) {
					$question = TrainingProQuestion::withoutGlobalScope(ModuleCompanyScope::class)
						->select('mark')
						->where('id', $key)
						->where('correct_answer', $value) // Check for correct answer
						->first();
					if ($question) {
						$totalMark += $question->mark; // Increment totalMark if correct answer
					}
				}
				// Update the score in the assessment log
				$maxScore = $assessment->max_score;
				$minScore = $assessment->min_score;
				$scorePercentage = ($totalMark / $maxScore) * 100;

				$result = TrainingProAssessmentLog::withoutGlobalScope(ModuleCompanyScope::class)
					->where('id', $assessmentId)
					->update([
						'max_score' => $maxScore,
						'min_score' => $minScore,
						'score' => $totalMark,
						'score_percentage' => $scorePercentage
					]);
			}
		}
		// Handle the case where assessment log is not found or session variables are missing
		return redirect()->route('config.trainings'); // Adjust as needed
	}

	public function getProgrammes($id)
	{
		$tpProgrammes = TrainingProProgramme::select('id', 'name')
			->where('category_id', $id)
			->where('is_enabled', 1)
			->get();

		// Check if the record exists
		if (!$tpProgrammes) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		$result = $tpProgrammes->map(function ($tpProgramme) {
			return [
				'tpProId' => $tpProgramme->id,
				'tpProName' => $tpProgramme->name,
			];
		});

		return response()->json($result->toArray());
	}

	public function getTopics($id)
	{
		$tpTopics = TrainingProTopic::select('id', 'name')
			->where('programme_id', $id)
			->where('is_enabled', 1)
			->get();

		// Check if the record exists
		if (!$tpTopics) {
			return response()->json(['error' => 'Record not found'], 404);
		}

		$result = $tpTopics->map(function ($tpTopic) {
			return [
				'tpTopicId' => $tpTopic->id,
				'tpTopicName' => $tpTopic->name,
			];
		});

		return response()->json($result->toArray());
	}

	public function byCategory($id, $type = 'ajax')
	{
		$programmes = TrainingProProgramme::select('id', 'name')
			->where('category_id', $id)
			->where('is_enabled', 1)
			->get();
		if ($type === 'ajax') {
			$options = '<option value="0">--</option>';
			foreach ($programmes as $item) {
				$options .= '<option  data-content="' . $item->name . '" value="' . $item->id . '"> ' . $item->name . ' </option>';
			}
			return Reply::dataOnly(['status' => 'success', 'data' => $options]);
		} else {
			return $programmes;
		}
	}

	public function byDepartment($id, $desigId, $type = 'ajax')
	{
		$users = User::join('employee_details', 'employee_details.user_id', '=', 'users.id');

		if ($id != 0) {
			$users = $users->where('employee_details.department_id', $id);
		}
		if ($desigId != 0) {
			$users = $users->where('employee_details.designation_id', $desigId);
		}
		$users = $users->select('users.*')->get();

		if ($type === 'ajax') {
			$options = '<option value="0">--</option>';
			foreach ($users as $item) {
				$options .= '<option  data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div>  ' . $item->name . '" value="' . $item->id . '"> ' . $item->name . ' </option>';
			}

			return Reply::dataOnly(['status' => 'success', 'data' => $options]);
		} else {
			return $users;
		}
	}

	public function byDesignation($id, $deptId)
	{
		$users = User::join('employee_details', 'employee_details.user_id', '=', 'users.id');

		if ($id != 0) {
			$users = $users->where('employee_details.designation_id', $id);
		}
		if ($deptId != 0) {
			$users = $users->where('employee_details.department_id', $deptId);
		}

		$users = $users->select('users.*')->get();

		$options = '<option value="0">--</option>';

		foreach ($users as $item) {
			$options .= '<option  data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div>  ' . $item->name . '" value="' . $item->id . '"> ' . $item->name . ' </option>';
		}

		return Reply::dataOnly(['status' => 'success', 'data' => $options]);
	}
}
