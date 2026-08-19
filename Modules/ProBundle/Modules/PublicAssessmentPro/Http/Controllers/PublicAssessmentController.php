<?php

namespace Modules\PublicAssessmentPro\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Scopes\ModuleCompanyScope;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Modules\PublicAssessmentPro\Entities\PublicAssessment;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentAnswer;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProAssessment;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProQuestCategory;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProQuestion;


class PublicAssessmentController extends Controller
{
    public function showPublicAssessment($id)
    {
        $this->id = Crypt::decrypt($id);
        $this->pageTitle = "Public Assessment";
        
        //TODO :  SPLIT FORM, FORM validation with responsive field error message
      
        $this->pap = null;
		$query = "SELECT p.*, sum(q.score) as totalscore FROM public_assessment_pro_assessments p LEFT JOIN public_assessment_pro_questions q ON p.id = q.assessment_id WHERE p.status=1 AND p.id = ?";
		$results = DB::select($query, [$this->id]);
		if (!empty($results)) {
			$this->pap = (object)$results[0];
		} else {
			return redirect('https://zemwa.com/');
		}
        if ($this->pap->product_id) {
			$this->product = Product::select('name')->where('id', $this->pap->product_id)->first();
		} else {
            //TODO : rectify this option later
			$this->product= 0 ;
		}
        $this->totalScore=$this->pap->totalscore;
        $this->vCount=$this->pap->view_count+1;
        //  Updates view_count 
        // $publicAssessment = PublicAssessmentProAssessment::withoutGlobalScope(ModuleCompanyScope::class)
        // -> find($request->assessmentId);
        // $publicAssessment->view_count = $request->viewCount;
        // $publicAssessment->save();  //fix later : Attempt to read property "is_superadmin" on null'. Wrote  DB::Raw instead
        DB::table('public_assessment_pro_assessments')
        ->where('id', $this->id)
        ->update(['view_count' => $this->vCount]);

        $this->styled='1';
        //grouped questions by QCategory
        $questionCats  = DB::select("SELECT q.*, c.category_name AS category 
                            FROM public_assessment_pro_questions q
                            LEFT JOIN public_assessment_pro_quest_categories c ON c.id = q.quest_cat_id 
                            WHERE q.assessment_id = ?", [$this->id]);
        
        $this->groupedQuestions = collect($questionCats)->groupBy('category');

        $questionans  = DB::select("SELECT q.id, a.question_id, a.id as anwser_id, a.ans_code, a.answer
                             FROM public_assessment_pro_questions q
                             LEFT JOIN public_assessment_pro_answers a ON q.id = a.question_id
                             WHERE q.assessment_id = ?", [$this->id]);
        $this->questionAns = (object)$questionans;


        return view('publicassessmentpro::public-assessment', $this->data);
    }

    public function storePublicAssessment(Request $request)
    {
 
        
        //check Assessment Submission Limit
        $assessmentCount = PublicAssessment::where('assessment_id', $request->assessmentId)->count();
        $query = "SELECT p.*, sum(q.score) as totalscore FROM public_assessment_pro_assessments p LEFT JOIN public_assessment_pro_questions q ON p.id = q.assessment_id WHERE p.id = ?";
		$results = DB::select($query, [$request->assessmentId]);
        $allowedCount = (object)  $results[0];
        if($allowedCount->submission_limit>0 && $assessmentCount>0)
        {
            if ($assessmentCount >= $allowedCount->submission_limit) {
                return response()->json([
                            'status' => 'error',
                            'message' => 'Sorry!. This assessment has reached maximum number of submisstion.'
                        ], 403);
            }
        }

        $assessType =   $allowedCount->assessment_type;
        $totalScore =   $request->totalScore;
        $maxScore   =   $allowedCount->max_score;
        $minScore   =   $allowedCount->min_score;
        $totalMark = 0;
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'participantName' => 'required',
                'participantPhone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'], // Improved phone validation
                'participantEmail' => 'required|email',
                'answers' => 'required|array', 
            ], [
                'participantName.required' => 'Please enter your name.',
                'participantPhone.required' => 'Please enter your phone number.',
                'participantPhone.regex' => 'Please enter a valid phone number (numbers, spaces, hyphens, plus signs, and parentheses are allowed).', 
                'participantPhone.min' => 'Your phone number must be at least 10 characters long.',
                'participantEmail.required' => 'Please enter your email address.',
                'participantEmail.email' => 'Please enter a valid email address.',
                'answers.required' => 'Answer for all questions are required', 
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422);
            }
            //check the participant 
            $phone = $request->participantPhone;
            $email = $request->participantEmail;
            $assessmentParticipant = PublicAssessment::where('assessment_id', $request->assessmentId)
            ->where(function ($query) use ($email, $phone) {
                $query->where('participant_email', $email)
                    ->orWhere('participant_phone', $phone);
            })
            ->exists();
            if ($assessmentParticipant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sorry!. You already submitted for this assessment.'
                ], 403);
            }
            
            else
            {
                $assessment = PublicAssessment::create([
                    'company_id' => $request->company_id,
                    'assessment_id' => $request->assessmentId,
                    'participant_name' => $request->participantName,
                    'participant_phone' => $request->participantPhone,
                    'participant_email' => $request->participantEmail,
                    'submitted_on' => now(),
                ]);
            
                //  adding answers and score
                foreach ($request->answers as $questionId => $answer) {
                    //check the assessment type and calculate score
                    if($assessType==0)
                    {
                        $questionById = DB::select("SELECT q.score
                                        FROM public_assessment_pro_questions q
                                        WHERE q.id = ? AND q.correct_answer = ?", [$questionId,$answer]);
                        if ($questionById) {
                            $totalMark += $questionById[0]->score; // Increment totalMark if correct answer
                        }
                    }
                    PublicAssessmentAnswer::create([
                        'public_assessment_id' => $assessment->id,
                        'question_id' => $questionId,
                        'answer_code' => $answer,
                    ]);
                }
                if($assessType==0)
                {
                    
                $scorePercentage = round(($totalMark / $totalScore) * 100);
                $grade='';
                //dd($maxScore.'-'.$minScore.'-'.$totalScore.'-'.$scorePercentage);
                if($minScore<= $totalMark) 
                    {   
                        //TODO - change later - now showing percentage instead grade.
                        if($scorePercentage>60)
                        {
                            $grade= 'A';
                        }


                    }
                $assessmentResult = PublicAssessment::find($assessment->id);
                $assessmentResult->update(['total_score' => $totalScore,'scored_mark'=> $totalMark,'grade'=> $scorePercentage]);
                }
            }
            $finalResult = PublicAssessment::find($assessment->id);
            //TODO - send json with result data to show them in card
            return response()->json(['status' => 'success','message' => 'Assessment submitted successfully','data' => $finalResult], 201);


                
    }
}
