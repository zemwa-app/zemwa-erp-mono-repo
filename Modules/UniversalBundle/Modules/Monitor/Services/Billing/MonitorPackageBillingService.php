<?php

namespace Modules\Monitor\Services\Billing;

use App\Models\Company;
use App\Models\SuperAdmin\Package;
use Carbon\Carbon;
use Modules\Monitor\Support\EmployeeMonitoring;

class MonitorPackageBillingService
{
    public function __construct(
        private readonly MonitorBillableSeatService $billableSeats,
    ) {
    }

    public static function apply(Company $company, Carbon $monthStart, Carbon $monthEnd, \stdClass $payment): void
    {
        app(self::class)->appendToPackagePayment($company, $monthStart, $monthEnd, $payment);
    }

    public function appendToPackagePayment(Company $company, Carbon $monthStart, Carbon $monthEnd, \stdClass $payment): void
    {
        $payment->monitorSeatCount = 0;
        $payment->monitorPerSeatPrice = 0.0;
        $payment->monitorAmount = 0.0;

        if (! EmployeeMonitoring::moduleActive() || ! $this->companyPackageIncludesMonitor($company)) {
            return;
        }

        $package = $payment->package ?? Package::find($company->package_id);

        if (! $package) {
            return;
        }

        $perSeatPrice = (float) ($package->monitor_per_seat_price ?? 0);

        if ($perSeatPrice <= 0) {
            return;
        }

        $seatCount = $this->billableSeats->countBillableSeatsForMonth(
            $company->id,
            $monthStart->copy()->startOfMonth(),
            $monthEnd->copy()->endOfMonth()
        );

        $monitorAmount = round($seatCount * $perSeatPrice, 2);

        $payment->monitorSeatCount = $seatCount;
        $payment->monitorPerSeatPrice = $perSeatPrice;
        $payment->monitorAmount = $monitorAmount;
        $payment->paymentWithoutTax = ($payment->paymentWithoutTax ?? 0) + $monitorAmount;
    }

    public function companyPackageIncludesMonitor(Company $company): bool
    {
        $package = $company->package;

        if (! $package?->module_in_package) {
            return false;
        }

        $modules = json_decode($package->module_in_package, true);

        return is_array($modules) && in_array('monitor', $modules, true);
    }
}
