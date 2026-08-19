<?php

namespace Modules\LandingPagePro\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserPermissionScope implements Scope
{
	protected $permission;
	protected $scope;

	public function __construct($permission, $scope = null)
	{
		$this->permission = $permission;
		$this->scope = $scope;
	}

	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $builder
	 * @param mixed $value
	 * @return void
	 */
	public function apply(Builder $builder, Model $model): void
	{
		if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'user_id')) {
			$permission = $this->permission;
			// Check for permission levels that skip user_id check
			if (in_array($permission, ['all', 'added', 'both'])) {
				return; // Skip checking user_id if permission allows full access
			}
			// Check for 'owned' permission where user_id check applies
			if ($permission === 'owned') {
				$builder->where('user_id', user()->employeeDetail->user_id);
			} else if ($permission === 'none') {
				// No results for 'none' permission - return empty array
				$builder->selectRaw('')->whereRaw('1 = 0');
			}
		}
	}
}
