<?php

namespace Modules\PublicAssessmentPro\Entities;

use App\Models\BaseModel;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;


class PublicAssessmentAnswer extends BaseModel
{
 

  
	protected $table = 'public_assessment_answers';

	protected $guarded = ['id'];

	/**
	 * The attributes that are mass assignable.
	 */
    protected $fillable = [
        'public_assessment_id',
        'question_id',
        'answer_code'
    ];
    public function pAssessment()
	{
		return $this->belongsTo(PublicAssessment::class);
	}


}
    

