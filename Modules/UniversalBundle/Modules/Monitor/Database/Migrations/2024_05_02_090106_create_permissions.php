<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use App\Scopes\CompanyScope;
use App\Models\ModuleSetting;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up()
    {
        $monitorModule = \App\Models\Module::firstOrCreate(['module_name' => 'monitor']);

        $permissionTypes = [
            ['name' => 'view_monitor', 'display_name' => 'View Monitor', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
        ];

        $companies = Company::get();

        foreach ($companies as $company) {
            $roles = ['employee', 'admin'];
            ModuleSetting::createRoleSettingEntry('monitor', $roles, $company);
        }

        foreach ($permissionTypes as $key => $permissionType) {

            $permission = Permission::firstOrCreate([
                'name' => $permissionType['name'],
                'display_name' => $permissionType['display_name'],
                'is_custom' => $permissionType['is_custom'],
                'module_id' => $monitorModule->id,
                'allowed_permissions' => $permissionType['allowed_permissions'],
            ]);


            foreach ($companies as $company) {

                $role = Role::where('name', 'admin')
                    ->where('company_id', $company->id)
                    ->first();

                if ($role) {
                    $permissionData = PermissionRole::where('permission_id', $permission->id)
                        ->where('role_id', $role->id)->where('permission_type_id', 4)->first();

                    if (is_null($permissionData)) {
                        $permissionRole = new PermissionRole();
                        $permissionRole->permission_id = $permission->id;
                        $permissionRole->role_id = $role->id;
                        $permissionRole->permission_type_id = 4;
                        $permissionRole->save();
                    }
                }


                $admins = User::allAdmins($company->id);

                foreach ($admins as $admin) {
                    UserPermission::firstOrCreate(
                        [
                            'user_id' => $admin->id,
                            'permission_id' => $permission->id,
                            'permission_type_id' => 4,
                        ]
                    );
                }

            }
        }



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }

};
