<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrainingProProgress extends BaseModel
{
	protected $table = 'training_pro_progress';

	protected $guarded = ['id'];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();

		if (!user()->is_superadmin) {
			static::addGlobalScope(new ModuleCompanyScope());
		}
	}

	public static function updateOrCreateProgress(int $programmeId, string $event = 'entry'): self
	{
		$existingProgress = self::withoutGlobalScope(ModuleCompanyScope::class)
			->where('programme_id', $programmeId)
			->where('user_id', user()->employeeDetail->user_id)
			->first();

		if (!$existingProgress) {
			$spentTime = 0;
			$entryAt = now();

			return self::create([
				'company_id' => company()->id,
				'user_id' => user()->employeeDetail->user_id,
				'programme_id' => $programmeId,
				'entry_at' => $entryAt,
				'spent_time' => $spentTime,
			]);
		}

		if ($event === 'entry') {
			$entryAt = now();
			$existingProgress->entry_at = $entryAt;
			$existingProgress->save();
		}
		$differenceInSeconds = '';
		if ($event === 'exit') {
			$exitAt = now();
			$differenceInSeconds = now()->diffInSeconds($existingProgress->entry_at);
			$differenceInSeconds = $differenceInSeconds !== false ? $differenceInSeconds : 0;
			$existingProgress->exit_at = $exitAt;
			$existingProgress->spent_time += $differenceInSeconds;
			$existingProgress->save();
		}

		return $existingProgress;
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
