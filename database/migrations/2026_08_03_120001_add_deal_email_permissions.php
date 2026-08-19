<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::where('module_name', 'leads')->first();

        if (!$module) {
            return;
        }

        $permissions = [
            [
                'name' => 'view_deal_email',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom' => 1,
            ],
            [
                'name' => 'send_deal_email',
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                'is_custom' => 1,
            ],
            [
                'name' => 'manage_deal_email_template',
                'allowed_permissions' => Permission::ALL_ADDED_NONE,
                'is_custom' => 1,
            ],
        ];

        $viewDealsPermission = Permission::where('name', 'view_deals')
            ->where('module_id', $module->id)
            ->first();

        foreach ($permissions as $permissionData) {
            $permission = Permission::updateOrCreate(
                [
                    'name' => $permissionData['name'],
                    'module_id' => $module->id,
                ],
                [
                    'display_name' => ucwords(str_replace('_', ' ', $permissionData['name'])),
                    'is_custom' => $permissionData['is_custom'],
                    'allowed_permissions' => $permissionData['allowed_permissions'],
                ]
            );

            $companies = Company::select('id')->get();

            foreach ($companies as $company) {
                $adminRole = Role::where('name', 'admin')
                    ->where('company_id', $company->id)
                    ->first();

                if ($adminRole) {
                    $permissionRole = PermissionRole::where('permission_id', $permission->id)
                        ->where('role_id', $adminRole->id)
                        ->first() ?: new PermissionRole();

                    $permissionRole->permission_id = $permission->id;
                    $permissionRole->role_id = $adminRole->id;
                    $permissionRole->permission_type_id = 4;
                    $permissionRole->save();
                }

                if ($permission->name !== 'manage_deal_email_template' && $viewDealsPermission) {
                    $employeeRole = Role::where('name', 'employee')
                        ->where('company_id', $company->id)
                        ->first();

                    if ($employeeRole) {
                        $viewDealsRolePerm = PermissionRole::where('permission_id', $viewDealsPermission->id)
                            ->where('role_id', $employeeRole->id)
                            ->first();

                        if ($viewDealsRolePerm && $viewDealsRolePerm->permission_type_id != 5) {
                            $permissionRole = PermissionRole::where('permission_id', $permission->id)
                                ->where('role_id', $employeeRole->id)
                                ->first() ?: new PermissionRole();

                            $permissionRole->permission_id = $permission->id;
                            $permissionRole->role_id = $employeeRole->id;
                            $permissionRole->permission_type_id = $viewDealsRolePerm->permission_type_id;
                            $permissionRole->save();
                        }
                    }
                }
            }

            $adminUsers = User::allAdmins();

            foreach ($adminUsers as $adminUser) {
                $userPermission = UserPermission::where('user_id', $adminUser->id)
                    ->where('permission_id', $permission->id)
                    ->first() ?: new UserPermission();

                $userPermission->user_id = $adminUser->id;
                $userPermission->permission_id = $permission->id;
                $userPermission->permission_type_id = 4;
                $userPermission->save();
            }
        }
    }

    public function down(): void
    {
        $module = Module::where('module_name', 'leads')->first();

        if (!$module) {
            return;
        }

        $permissionNames = ['view_deal_email', 'send_deal_email', 'manage_deal_email_template'];

        foreach ($permissionNames as $permissionName) {
            $permission = Permission::where('name', $permissionName)
                ->where('module_id', $module->id)
                ->first();

            if ($permission) {
                PermissionRole::where('permission_id', $permission->id)->delete();
                UserPermission::where('permission_id', $permission->id)->delete();
                $permission->delete();
            }
        }
    }
};
