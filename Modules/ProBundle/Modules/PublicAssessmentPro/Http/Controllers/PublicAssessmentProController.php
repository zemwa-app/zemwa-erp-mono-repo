<?php

namespace Modules\PublicAssessmentPro\Http\Controllers;


use App\Http\Controllers\AccountBaseController;
use App\Helper\Reply;
use App\Models\Product;
use App\Scopes\ModuleCompanyScope;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;


use Modules\PublicAssessmentPro\Entities\PublicAssessmentAnswer;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProSetting;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProAssessment;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProQuestCategory;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProQuestion;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProAnswer;
use Modules\PublicAssessmentPro\Entities\PublicAssessment;

class PublicAssessmentProController extends AccountBaseController
{
    //$id = (int)(Crypt::decrypt($id));
    public function __construct()
	{
		parent::__construct();
		$this->pageTitle = __('publicassessmentpro::app.menu.publicassessmentproSetting');

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


		abort_403(!in_array('admin', user_roles()) && user()->permission('view_assessment') != 'none');
		$this->pageTitle = __('publicassessmentpro::app.menu.publicassessmentpro');
		$this->usertype = $this->user;
		$this->assessments = PublicAssessmentProAssessment::where('status', 1)->count();
		$this->participants = PublicAssessment::where('company_id', company()->id)->count();
    
        
        $this->recentAssessments = PublicAssessmentProAssessment::withoutGlobalScope(ModuleCompanyScope::class)
            ->select('public_assessment_pro_assessments.*','users.name as username','products.name as product')
            ->leftJoin('users', 'users.user_auth_id', '=', 'public_assessment_pro_assessments.added_by')
			->leftJoin('products','public_assessment_pro_assessments.product_id', '=','products.id')
            ->where('public_assessment_pro_assessments.company_id', company()->id)
            ->where('public_assessment_pro_assessments.status', 1)
            ->whereMonth('public_assessment_pro_assessments.created_at', now()->month)
            ->latest('public_assessment_pro_assessments.created_at')
            ->limit(10)
            ->get();
         
            $this->recentResults = PublicAssessment::withoutGlobalScope(ModuleCompanyScope::class)
            ->select(
                'public_assessments.*',
                'public_assessment_pro_assessments.assessment_name as assessment',
                'public_assessment_pro_assessments.assessment_type',
                'public_assessment_pro_assessments.max_score',
                'public_assessment_pro_assessments.min_score'

            )
            ->join('public_assessment_pro_assessments', 'public_assessments.assessment_id', '=', 'public_assessment_pro_assessments.id')
            ->where('public_assessments.company_id', company()->id)
            ->where('public_assessment_pro_assessments.status', 1)
            ->whereMonth('public_assessments.updated_at', now()->month)
            ->latest('public_assessments.updated_at')
            ->groupBy('public_assessment_pro_assessments.assessment_name')
            ->limit(10)
            ->get();

       

		return view('publicassessmentpro::index', $this->data);
	}

	public function config()
	{
		abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('view_assessment'), ['all', 'added', 'owned', 'both']));

		$this->pageTitle = __('publicassessmentpro::app.menu.publicassessmentpro');
		$this->usertype = $this->user;
		$this->paAssessments = PublicAssessmentProAssessment::orderBy('id', 'asc')->get();
		$this->papAssessments = PublicAssessmentProAssessment::select('id','assessment_name')->where('status',1)->orderBy('id', 'asc')->get();
        $this->paProducts = Product::select('id','name','company_id')->get();

        // validateAssessmentLimit();
        $this->assessmentCount=$assessmentCount = PublicAssessmentProAssessment::count();
        $this->alocatedCount=$alocatedCount = PublicAssessmentProSetting::where('package_id', company()->package_id)->value('assessment_limit');


        return view('publicassessmentpro::config', $this->data);
	}

    public function participants()
	{
		abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('view_category'), ['all', 'added', 'owned', 'both']));

		$this->pageTitle = __('publicassessmentpro::app.menu.publicassessmentParticipants');

		$this->recentResults = PublicAssessment::withoutGlobalScope(ModuleCompanyScope::class)
			->with('assessment','answers')
			->select('*')
			->where('company_id', company()->id)
			->get();
			

		return view('publicassessmentpro::participants', $this->data);
	}

    public function createAssessment(Request $request)
    {
		abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('add_assessment'), ['all', 'added', 'owned', 'both']));

       // validateAssessmentLimit();
        $assessmentCount = PublicAssessmentProAssessment::count();
        $alocatedCount = PublicAssessmentProSetting::where('package_id', company()->package_id)->value('assessment_limit');
        if (!filled($request->input('tpAssessmentId')) && $assessmentCount >= $alocatedCount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Already added allowed number of Assessments.'
            ], 403);
        }
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'tpAssessmentId' => 'nullable|integer',
            'paAssessProduct' => 'required|integer',
            'paAssessType' => 'required|integer',
            'tpAssessment' => 'required',
            'tpAssessDescription' => 'nullable',
            'paAssessSubLimit' => 'required|integer',
            'tpAssessStatus' => 'nullable|integer',
        ], [
            'tpAssessmentId.integer' => 'Please do not manually alter any submitted value.',
            'paAssessProduct.required' => 'Please select Product or Other for any.',
            'paAssessProduct.integer' => 'Please do not manually alter any submitted value.',
            'paAssessType.required' => 'Please select assessment type.',
            'paAssessType.integer' => 'Please do not manually alter any submitted value.',
            'tpAssessment.required' => 'Please enter the topic name.',
            'paAssessSubLimit.required' => 'Please enter assessment submission limit. (Enter 0 for no restriction)',
            'paAssessSubLimit.integer' => 'Submission limit value should be a number.',
            'tpAssessStatus.integer' => 'Please do not alter the value of Status.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('tpAssessMaxScore')) {
                $validator->addRules([
                    'tpAssessMaxScore' => 'required|integer'
                ],[
                    'tpAssessMaxScore.integer' => 'Max. Score value should be a number.',
                    'tpAssessMaxScore.required' => 'Please enter the max. score (in number).',
                ]);
            }

            if ($request->filled('tpAssessMinScore')) {
                $validator->addRules([
                    'tpAssessMinScore' => 'required|integer'
                ],[
                    'tpAssessMinScore.integer' => 'Min. Score value should be a number.',
                    'tpAssessMinScore.required' => 'Please enter the min. score (in number).',
                ]);
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        try {
            $tpAssessmentId = $request->input('tpAssessmentId');
            $paAssessProduct = $request->input('paAssessProduct');
            $paAssessType = $request->input('paAssessType');
            $tpAssessment = $request->input('tpAssessment');
            $tpAssessDescription = $request->input('tpAssessDescription');
            $tpAssessMaxScore = $request->filled('tpAssessMaxScore') ? $request->input('tpAssessMaxScore') : 0;
            $tpAssessMinScore = $request->filled('tpAssessMinScore') ? $request->input('tpAssessMinScore') : 0;
            $paAssessSubLimit = $request->input('paAssessSubLimit');
            $tpAssessStatus = $request->input('tpAssessStatus');

            PublicAssessmentProAssessment::updateOrCreate(
                [
                    'id' => $tpAssessmentId
                ],
                [
                    'product_id' => $paAssessProduct,
                    'assessment_type' => $paAssessType,
                    'assessment_name' => $tpAssessment,
                    'description' => $tpAssessDescription,
                    'max_score' => $tpAssessMaxScore,
                    'min_score' => $tpAssessMinScore,
                    'company_id' => company()->id,
                    'submission_limit' => $paAssessSubLimit,
                    'status' => $tpAssessStatus,
                    'added_by' => auth()->id(),
                ]
            );

            // Return a JSON response
            return response()->json([
                'status' => 'success',
                'message' => 'Assessment details recorded successfully!.'
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

        $tpAssessment = PublicAssessmentProAssessment::select('id','product_id','assessment_type','assessment_name','description','max_score','min_score','submission_limit','status')
            ->where('id', $id)
            ->first();

        // Check if the record exists
        if (!$tpAssessment) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Return the data as JSON
        $data = [
            'tpAssessmentId' => $tpAssessment->id,
            'paAssessProduct' => $tpAssessment->product_id,
            'paAssessType' => $tpAssessment->assessment_type,
            'tpAssessment' => $tpAssessment->assessment_name,
            'tpAssessDescription' => $tpAssessment->description,
            'tpAssessMaxScore' => $tpAssessment->max_score,
            'tpAssessMinScore' => $tpAssessment->min_score,
            'paAssessSubLimit' => $tpAssessment->submission_limit,
            'tpAssessStatus' => $tpAssessment->status,
        ];
        return response()->json([
            'status' => 'success',
            'data' => $data],201);
    }

    public function destroyAssessment($id)
    {
        $userPermission = user()->permission('delete_assessment');
        abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));
        try {
            $record = PublicAssessmentProAssessment::where('id', $id)->first();
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


    public function getAssessTypeFields($id)
    {
        // $userPermission = user()->permission('view_assessment');
        // abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));
        //if for other than Score based MCQ
        if ($id>0) {
            // Return the data as JSON
            if (request()->ajax()) {
                $html = view('publicassessmentpro::partials.atype-scoreless', $this->data)->render();
                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }
            $this->view = 'publicassessmentpro::partials.atype-scoreless';
        }
        else
        {
            // Return the data as JSON
            if (request()->ajax()) {
                $html = view('publicassessmentpro::partials.atype-scored', $this->data)->render();
                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }
            $this->view = 'publicassessmentpro::partials.atype-scored';

        }
        return view('publicassessmentpro::config', $this->data);

    }
// function list questions of an assessment
    public function getAssessQuestion($id)
    {
        $userPermission = user()->permission('view_category');
        abort_403(!in_array($userPermission, ['all', 'added', 'both', 'owned']) && !User::isAdmin(auth()->user()->id));

        $this->selectedAssessment = PublicAssessmentProAssessment::findOrFail($id);
        $this->pasAssessQuestion = PublicAssessmentProQuestion::select('id','assessment_id','quest_cat_id','question','score', 'status')
            ->where('assessment_id', $id)
            ->get();

        // Check if the record exists
//        if (!$this->pasAssessquestion) {
//            return response()->json(['error' => 'Record not found'], 404);
//        }

        // Return the data as JSON
        if (request()->ajax()) {
            $html = view('publicassessmentpro::ajax.show-questions', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }
        $this->view = 'publicassessmentpro::ajax.show-questions';
        return view('publicassessmentpro::config', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function getQuesCategoryList(string $action = 'a')
    {
        $this->paCategories = PublicAssessmentProQuestCategory::select('id','category_name')->where('status',1)->orderBy('id', 'asc')->get();
        $view = ($action === 'e')
                ? 'publicassessmentpro::ajax.edit-question'
                : 'publicassessmentpro::ajax.create-question';

        if (request()->ajax()) {
            $html = view('publicassessmentpro::partials.list-qcategory', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }
        $this->view = 'publicassessmentpro::ajax.partials.list-qcategory';
        return view($view, $this->data);
    }

    //later change name to  question as using for it.
    public function createQuestion($id)
    {
        abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('add_category'), ['all', 'added', 'owned', 'both']));
        $this->pageTitle = __('publicassessmentpro::app.header.addQuestion');
        $this->usertype = $this->user;

        $this->paAssessment = PublicAssessmentProAssessment::findOrFail($id);
        $this->papCategories = PublicAssessmentProQuestCategory::select('id','category_name')->where('status',1)->orderBy('id', 'asc')->get();


        if (request()->ajax()) {
           $html = view('publicassessmentpro::ajax.create-question', $this->data)->render();
           return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'publicassessmentpro::ajax.create-question';
        return view('publicassessmentpro::qaconfig', $this->data);
    }
    //get categories dynamically for select filed in add or edit question after closing modal of Question Category
    public function getCategorySelect($type = 'ajax')
    {
        $paQcats = PublicAssessmentProQuestCategory::select('id', 'category_name')
            ->where('status', 1)
            ->get();


        if ($type === 'ajax') {
            $options = '<option value="0">--</option>';
            foreach ($paQcats as $item) {
                $options .= '<option  data-content="' . $item->category_name . '" value="' . $item->id . '"> ' . $item->category_name . ' </option>';
            }
            return Reply::dataOnly(['status' => 'success', 'data' => $options]);
        } else {
            return $paQcats;
        }
    }


    public function createCategory(Request $request)
    {
        abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('add_category'), ['all', 'added', 'owned', 'both']));

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'paQuestCategoryId' => 'nullable|integer',
            'paQcategryName' => 'required|string',
        ], [
            'paQcategryName.required' => 'Please enter the category name.',
            'tpCategoryId.integer' => 'Only numeric fields are allowed for id.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        try {
            $paQuestCategoryId = $request->input('paQuestCategoryId');
            $paQcategryName = $request->input('paQcategryName');

            PublicAssessmentProQuestCategory::updateOrCreate(
                [
                    'id' => $paQuestCategoryId
                ],
                [
                    'category_name' => $paQcategryName,
                    'company_id' => company()->id,
                    'status' => 1
                ]
            );

            // Return a JSON response
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully created question category.'
            ], Response::HTTP_CREATED); // 201 status code for resource creation
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500); // 500 status code for internal server error
        }
    }

    public function destroyCategory($id)
    {

        abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('delete_category'), ['all', 'added', 'owned', 'both']));

        $record = PublicAssessmentProQuestCategory::where('id', $id)->first();
        if (!$record) {
            return response()->json(['error' => 'Record not found'], 404);
        }
        // Perform deletion
        $record->delete();

        return response()->json(['success' => 'Record deleted successfully'], 200);
    }




    public function storeQa(Request $request)
           {
              // dd($request);
              // abort_403(!User::isAdmin(auth()->user()->id));

               abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('add_category'), ['all', 'added', 'owned', 'both']));

                //dd($request->input('assesQcategory'));

               // Validate the request data
               $validator = Validator::make($request->all(), [
                   'quesId' => 'nullable|integer',
                   'assessmentId' => 'required|integer',
                   'assessQaCategory' => 'required|integer',
                   'assesQa' => 'required',
                   //'assesMark' => 'required|integer',
                   //'rightAns' => 'required',
                   //'assesAnsOne' => 'required',
                   //'assesAnsTwo' => 'required',
                   'tpQsStatus' => 'nullable|integer',
               ], [
                   'quesId.integer' => 'Invalid data format.',
                   'assessmentId.required' => 'Assessment ID is required.',
                   'assessmentId.integer' => 'Invalid data format for Assessment ID.',
                   'assessQaCategory.required' => 'Question category is required.',
                   'assessQaCategory.integer' => 'Invalid data format for Category ID.',
                   'assesQa.required' => 'Assessment question is mandatory.',
                   //'assesMark.required' => 'Mark field is required.',
                   'assesMark.integer' => 'Mark must be a number.',
                   //'rightAns.required' => 'Select the correct answer.',
                   //'assesAnsOne.required' => 'Provide at least two answer options.',
                   //'assesAnsTwo.required' => 'Provide at least two answer options.',
                   'tpQsStatus.integer' => 'Invalid data format for Status.',
               ]);



            $validator->after(function ($validator) use ($request) {
                $rules = [];
                $messages = [];

                if ($request->filled('assesMark')) {
                    $rules['assesMark'] = 'required|integer';
                    $messages['assesMark.required'] = 'Mark field is required.';
                }

                foreach (['rightAns', 'assesAnsOne', 'assesAnsTwo'] as $field) {
                    if ($request->filled($field)) {
                        $rules[$field] = 'required';
                        $messages[$field.'.required'] = ($field === 'rightAns') ? 'Select the correct answer.' : 'Provide at least two answer options.';
                    }
                }

                $validator->addRules($rules, $messages);
            });

               if ($validator->fails()) {
                   return Reply::formErrors($validator->errors());
               }

               $quesId = $request->input('quesId');
               $assessmentId = $request->input('assessmentId');
               $assesQcategory = $request->input('assessQaCategory');
               $assesQa = $request->input('assesQa');
               $assesMark = $request->filled('assesMark') ? $request->input('assesMark') : 0;
               $rightAns = $request->filled('rightAns') ? $request->input('rightAns') : '';
               $assesAnsOne = $request->filled('assesAnsOne') ? $request->input('assesAnsOne') : '';
               $assesAnsTwo = $request->filled('assesAnsTwo') ? $request->input('assesAnsTwo') : '';
               $assesAnsThree = $request->has('assesAnsThree')? $request->input('assesAnsThree') : '';
               $assesAnsFour = $request->has('assesAnsFour')? $request->input('assesAnsFour') : '';
               $tpQsStatus = $request->input('tpQsStatus');

               // $asessType = PublicAssessmentProAssessment::where('id', $assessmentId)->value('assessment_type');
               $maxMark = PublicAssessmentProAssessment::where('id', $assessmentId)->value('max_score');
               $totalMarks = PublicAssessmentProQuestion::where('assessment_id', $assessmentId)->sum('score');

               if (($totalMarks + $assesMark) > $maxMark) {
                   return Reply::error('Please ensure the total score does not exceed the allowed maximum mark.', 'error', ['Limit exceeded']);
               }

               try {
                   DB::beginTransaction();
                   $question = PublicAssessmentProQuestion::updateOrCreate(
                       [
                           'id' => $quesId,
                       ],
                       [
                           'company_id' => company()->id,
                           'assessment_id' => $assessmentId,
                            'quest_cat_id' => $assesQcategory,
                           'question' => $assesQa,
                           'correct_answer' => $rightAns,
                           'score' => $assesMark,
                           'status' => $tpQsStatus
                       ]
                   );

                   // Save or update the answers
                   $answerData = [
                       ['company_id' => company()->id, 'ans_code' => 'a1', 'answer' => $assesAnsOne],
                       ['company_id' => company()->id, 'ans_code' => 'a2', 'answer' => $assesAnsTwo],
                       ['company_id' => company()->id, 'ans_code' => 'a3', 'answer' => $assesAnsThree],
                       ['company_id' => company()->id, 'ans_code' => 'a4', 'answer' => $assesAnsFour],
                   ];

                   foreach ($answerData as $data) {
                       $question->answers()->updateOrCreate(
                           [
                               'question_id' => $quesId,
                               'answer' => $data['answer'],
                           ],
                           $data
                       );
                   }

                   DB::commit();
                   // Return a JSON response
                   return Reply::successWithData(__('publicassessmentpro::app.message.recordSaved'), ['redirectUrl' => route('publicassessmentpro.config.home')]);
               } catch (\Exception $e) {
                   DB::rollback();
                   return Reply::error('Server Error:', 'error', $e->getMessage());
               }
           }

           public function editQa($aid, $qid)
           {
             //  abort_403(!User::isAdmin(auth()->user()->id));
               abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('edit_category'), ['all', 'added', 'owned', 'both']));


               $this->pageTitle = __('publicassessmentpro::app.menu.editQa');
               $this->usertype = $this->user;
               $this->paAssessment = PublicAssessmentProAssessment::findOrFail($aid);
               $this->papCategories = PublicAssessmentProQuestCategory::select('id','category_name')->where('status',1)->orderBy('id', 'asc')->get();
               $this->paQuestion = PublicAssessmentProQuestion::with('answers')->where('id', $qid)->first();

               if (request()->ajax()) {
                   $html = view('publicassessmentpro::ajax.edit-question', $this->data)->render();
                   return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
               }

               $this->view = 'publicassessmentpro::ajax.edit-question';
               return view('publicassessmentpro::qaconfig', $this->data);

           }

           public function destroyQa($id)
           {
               try {
                   $id = (int)$id;
                   $record = PublicAssessmentProQuestion::where('id', $id)->first();
                   if (!$record) {
                       return Reply::error('Record not found.', 'error', ['Record not found']);
                   }
                   $record->delete();

                   return Reply::successWithData(__('publicassessmentpro::app.message.recordDeleted'), ['redirectUrl' => route('publicassessmentpro.config.home')]);
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


    /**
     * Display a listing of the resource. //ToDo : 
     */
    public function validateAssessmentLimit()
    {
        abort_403(!in_array('admin', user_roles()) || !in_array(user()->permission('view_assessment'), ['all', 'added', 'owned', 'both']));

        $assessmentCount = PublicAssessmentProAssessment::count();
        $alocatedCount = PublicAssessmentProSetting::where('package_id', company()->package_id)->value('assessment_limit');

        if ($assessmentCount >= $alocatedCount) {
            return response()->json([
                'status' => 'error',
                'message' => 'You had already created the allowed number of Assessments.'
            ], 403);
        }

    }


}
