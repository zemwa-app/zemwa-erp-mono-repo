<?php

namespace Modules\PublicAssessmentPro\Entities;




use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;

class PublicAssessmentProQuestCategory extends BaseModel
{


    protected $table = 'public_assessment_pro_quest_categories';

    protected $guarded = ['id'];

    const MODULE_NAME = 'publicassessmentpro';

    protected static function boot()
    {
        parent::boot();

        if (!user()->is_superadmin) {
            static::addGlobalScope(new ModuleCompanyScope());
        }
    }

    public function  paQuestion()
    {
        return $this->belongsTo(PublicAssessmentProQuestion::class);
    }
    
    public function scopeUserPermission($query, $permission, $userId)
    {
        if ($permission === 'owned') {
            $query->where('user_id', $userId);
        } else if ($permission === 'none') {
            // No results for 'none' permission - return empty array
            $query->selectRaw('')->whereRaw('1 = 0');
        }
        return $query;
    }
}
