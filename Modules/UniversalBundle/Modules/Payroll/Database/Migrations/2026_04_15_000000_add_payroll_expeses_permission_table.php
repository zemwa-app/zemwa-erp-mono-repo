<?php

use App\Models\Company;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Payroll\Entities\PayrollSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\Module::validateVersion(PayrollSetting::MODULE_NAME);
        $payrollModule = \App\Models\Module::firstOrCreate(['module_name' => 'payroll']);


        $count = Company::count();

        try {

            foreach ($tables as $table) {

                if (! Schema::hasColumn($table, 'company_id')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->unsignedBigInteger('company_id')->nullable()->after('id');
                        $table->foreign('company_id')->references('id')
                            ->on('companies')->onDelete('cascade')->onUpdate('cascade');
                    });
                }

                if (Schema::hasColumn($table, 'company_id') && $count === 1) {
                    DB::table($table)->update(['company_id' => 1]);
                }
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $companies = Company::all();

        // We will insert these for the new company from event listener
        $payrollModule = \App\Models\Module::firstOrCreate(['module_name' => 'payroll']);
        $this->addAdminPermissions($payrollModule);

        foreach ($companies as $company) {
            PayrollSetting::addModuleSetting($company);
        }

    }

    private function addAdminPermissions($payrollModule)
    {

        $permissions = [
            [
                'name' => 'view_payroll_expenses',
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_NONE,
            ],

            [
                'name' => 'view_overtime_requests',
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_NONE,
            ]
        ];

        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate([
                'name' => $permission['name'],
                'is_custom' => $permission['is_custom'],
                'module_id' => $payrollModule->id,
                'allowed_permissions' => $permission['allowed_permissions'],
            ]);

            // To prevent duplicate
            $perm->display_name = ucwords(str_replace('_', ' ', $permission['name']));
            $perm->saveQuietly();
        }

    }
};
