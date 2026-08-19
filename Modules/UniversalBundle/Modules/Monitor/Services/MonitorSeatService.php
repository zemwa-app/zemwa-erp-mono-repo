<?php

namespace Modules\Monitor\Services;

use App\Models\EmployeeDetails;
use App\Models\User;
use Illuminate\Support\Collection;

class MonitorSeatService
{
    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getSeatRows(int $companyId): Collection
    {
        return $this->permissionScope
            ->scopedEmployeeQuery($companyId)
            ->with(['employeeDetail:id,user_id,monitoring_enabled'])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'monitoring_enabled' => (bool) ($user->employeeDetail?->monitoring_enabled ?? false),
            ]);
    }

    public function enabledSeatCount(int $companyId): int
    {
        return EmployeeDetails::query()
            ->where('company_id', $companyId)
            ->where('monitoring_enabled', true)
            ->whereHas('user', fn ($query) => $query->onlyEmployee())
            ->count();
    }

    public function setMonitoringEnabled(int $companyId, int $userId, bool $enabled): EmployeeDetails
    {
        $this->permissionScope->authorizeEmployee($userId, $companyId);

        $user = User::query()
            ->where('company_id', $companyId)
            ->onlyEmployee()
            ->findOrFail($userId);

        $detail = $user->employeeDetail;

        abort_unless($detail, 404);

        $detail->monitoring_enabled = $enabled;
        $detail->save();

        return $detail->fresh();
    }
}
