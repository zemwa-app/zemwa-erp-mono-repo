<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Modules\ServerManager\Entities\ServerDomainType;

return new class extends Migration
{
    public function up(): void
    {
        // Get the Server Manager module
        $module = Module::where('module_name', 'servermanager')->first();

        if (! $module) {
            return;
        }

        // Define ServerType manage permission
        $domainTypePermission = [
            'name' => 'manage_domain_type',
            'display_name' => 'Manage Domain Type',
            'allowed_permissions' => json_encode(['all' => 4, 'none' => 5]),
            'is_custom' => 1,
        ];

        // Create or update the manage_domain_type permission
        $permission = Permission::updateOrCreate(
            [
                'name' => $domainTypePermission['name'],
                'module_id' => $module->id,
            ],
            [
                'display_name' => $domainTypePermission['display_name'],
                'allowed_permissions' => $domainTypePermission['allowed_permissions'],
                'is_custom' => $domainTypePermission['is_custom'],
            ]
        );

        // Get all companies
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {


            ServerDomainType::ensureDefaultsForCompany($company->id);

            // Assign 'all' permission to admin role
            $adminRole = Role::where('name', 'admin')
                ->where('company_id', $company->id)
                ->first();

            if ($adminRole) {
                PermissionRole::updateOrCreate(
                    [
                        'permission_id' => $permission->id,
                        'role_id' => $adminRole->id,
                    ],
                    [
                        'permission_type_id' => 4, // All
                    ]
                );
            }

            // Assign 'none' permission to other roles (employee, client, etc.)
            $otherRoles = Role::where('company_id', $company->id)
                ->where('name', '!=', 'admin')
                ->get();

            foreach ($otherRoles as $role) {
                PermissionRole::updateOrCreate(
                    [
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                    ],
                    [
                        'permission_type_id' => 5, // None
                    ]
                );
            }
        }

        // Assign permission to all admin users
        $adminUsers = User::allAdmins();

        foreach ($adminUsers as $adminUser) {
            $userPermission = UserPermission::where('user_id', $adminUser->id)
                ->where('permission_id', $permission->id)
                ->first() ?: new UserPermission();

            $userPermission->user_id = $adminUser->id;
            $userPermission->permission_id = $permission->id;
            $userPermission->permission_type_id = 4; // All
            $userPermission->save();
        }

        // Assign 'none' permission to all non-admin users
        $nonAdminUsers = User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'admin');
        })->get();

        foreach ($nonAdminUsers as $user) {
            // Check if user doesn't already have admin role in any company
            $isAdmin = $user->roles()->where('name', 'admin')->exists();

            if (!$isAdmin) {
                $userPermission = UserPermission::where('user_id', $user->id)
                    ->where('permission_id', $permission->id)
                    ->first() ?: new UserPermission();

                $userPermission->user_id = $user->id;
                $userPermission->permission_id = $permission->id;
                $userPermission->permission_type_id = 5; // None
                $userPermission->save();
            }
        }
    }

    public function down(): void
    {
        $module = Module::where('module_name', 'servermanager')->first();

        if (! $module) {
            return;
        }

        $permission = Permission::where('module_id', $module->id)
            ->where('name', 'manage_domain_type')
            ->first();

        if ($permission) {
            PermissionRole::where('permission_id', $permission->id)->delete();
            UserPermission::where('permission_id', $permission->id)->delete();
            $permission->delete();
        }
    }
};
