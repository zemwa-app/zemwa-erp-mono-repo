<?php

namespace Modules\Monitor\Services;

use App\Models\EmployeeDetails;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MonitorPermissionScope
{
    public function permission(): string
    {
        if (isRunningInConsoleOrSeeding() || !user()) {
            return 'all';
        }

        return user()->permission('view_monitor');
    }

    public function canView(): bool
    {
        return $this->permission() !== 'none';
    }

    public function authorizeView(): void
    {
        abort_403(!$this->canView());
    }

    public function canViewEmployee(int $targetUserId, ?int $companyId = null): bool
    {
        $permission = $this->permission();

        if ($permission === 'none') {
            return false;
        }

        if ($permission === 'all') {
            return $this->employeeBelongsToCompany($targetUserId, $companyId);
        }

        $userId = user()->id;

        if ($permission === 'owned') {
            return $targetUserId === $userId;
        }

        if ($permission === 'added') {
            return $this->wasAddedByCurrentUser($targetUserId, $companyId);
        }

        if ($permission === 'both') {
            return $targetUserId === $userId || $this->wasAddedByCurrentUser($targetUserId, $companyId);
        }

        return false;
    }

    public function authorizeEmployee(int $targetUserId, ?int $companyId = null): void
    {
        abort_403(!$this->canViewEmployee($targetUserId, $companyId));
    }

    /**
     * @return Builder<User>
     */
    public function scopedEmployeeQuery(int $companyId): Builder
    {
        $query = User::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee'));

        return $this->applyToQuery($query);
    }

    /**
     * @return Collection<int, User>
     */
    public function getEmployees(int $companyId, ?int $departmentId = null, array $columns = ['*']): Collection
    {
        $query = $this->scopedEmployeeQuery($companyId);

        if ($departmentId) {
            $query->whereHas('employeeDetail', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($columns !== ['*']) {
            $query->select($columns);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * @return array<int, int>
     */
    public function visibleEmployeeIds(int $companyId, ?int $departmentId = null): array
    {
        return $this->getEmployees($companyId, $departmentId, ['id'])->pluck('id')->all();
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function applyAgentDataScope(Builder $query, int $companyId, string $column = 'user_id'): Builder
    {
        $permission = $this->permission();

        if ($permission === 'all') {
            return $query;
        }

        if ($permission === 'none') {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $this->scopedEmployeeQuery($companyId)->select('users.id'));
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function applyToQuery(Builder $query): Builder
    {
        $permission = $this->permission();

        if ($permission === 'all') {
            return $query;
        }

        if ($permission === 'none') {
            return $query->whereRaw('1 = 0');
        }

        $userId = user()->id;

        if ($permission === 'owned') {
            return $query->where('users.id', $userId);
        }

        if ($permission === 'added') {
            return $query->whereHas('employeeDetail', fn ($q) => $q->where('added_by', $userId));
        }

        if ($permission === 'both') {
            return $query->where(function ($q) use ($userId) {
                $q->where('users.id', $userId)
                    ->orWhereHas('employeeDetail', fn ($eq) => $eq->where('added_by', $userId));
            });
        }

        return $query->whereRaw('1 = 0');
    }

    private function wasAddedByCurrentUser(int $targetUserId, ?int $companyId): bool
    {
        $query = EmployeeDetails::query()
            ->where('user_id', $targetUserId)
            ->where('added_by', user()->id);

        if ($companyId !== null) {
            $query->whereHas('user', fn ($q) => $q->where('company_id', $companyId));
        }

        return $query->exists();
    }

    private function employeeBelongsToCompany(int $targetUserId, ?int $companyId): bool
    {
        if ($companyId === null) {
            return true;
        }

        return User::query()
            ->where('id', $targetUserId)
            ->where('company_id', $companyId)
            ->exists();
    }
}
