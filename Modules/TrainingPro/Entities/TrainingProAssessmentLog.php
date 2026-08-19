<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Models\User;
use App\Scopes\ModuleCompanyScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class TrainingProAssessmentLog extends BaseModel
{
	protected $table = 'training_pro_assessment_logs';

	protected $guarded = ['id'];

	protected $fillable = [
		'company_id',
		'user_id',
		'assessment_id',
		'assessment_data',
		'started_at',
		'finished_at',
		'duration_took',
		'score',
		'assessment_status'
	];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();

		if (!user()->is_superadmin) {
			static::addGlobalScope(new ModuleCompanyScope());
		}

		static::deleting(function ($assessment) {
			$assessment->questions()->delete();
		});
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function assessment()
	{
		return $this->belongsTo(TrainingProAssessment::class, 'assessment_id');
	}

	public static function assessmentLog(int $assessmentId = null, int $assessmentKey = null, string $assessmentData = null, bool $isAssessmentDone = false)//: self
	{
		// Ensure either assessmentId or assessmentKey is provided
		if ($assessmentId === null && $assessmentKey === null) {
			throw new \InvalidArgumentException('Either Assessment Id or Assessment Key is required.');
		}

		$userId = user()->employeeDetail->user_id;
		$companyId = user()->employeeDetail->company_id;
		$currentTime = now();

		// Check if a record already exists for this user, and assessment
		$query = self::where('user_id', $userId);
		// Dynamically build query based on provided parameters
		if ($assessmentId !== null) {
			$query->where('assessment_id', $assessmentId);
		} else {
			$query->where('id', $assessmentKey);
		}

		$existingLog = $query->first();
		// If no existing log, create a new one
		if (!$existingLog) {
			return self::create([
				'user_id' => $userId,
				'assessment_id' => $assessmentId,
				'company_id' => $companyId,
				'started_at' => $currentTime,
			]);
		} else {
			if ($assessmentData !== null) {
				$existingLog->assessment_data = json_encode($assessmentData);
			}
			if ($isAssessmentDone === true) {
				$existingLog->assessment_status = 1;
			}
			// Convert the timestamps to Carbon instances
			$startedAt = Carbon::parse($existingLog->started_at);
			$updatedAt = Carbon::parse($existingLog->updated_at);
			//return $startedAt . '---' . $updatedAt;
			$duration = $startedAt->diffInMinutes($updatedAt);
//			if ($existingLog->duration_took !== null) {
//				$duration += $existingLog->duration_took;
//			}
			$existingLog->duration_took = $duration;
			$existingLog->updated_at = $currentTime;
			$existingLog->save();
		}
		// Existing log found, return the existing instance (for potential updates later)
		return $existingLog;
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
