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

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the Server Manager module
        $module = Module::where('module_name', 'servermanager')->first();

        if (!$module) {
            return;
        }

        // Define ServerType manage permission
        $servertypePermission = [
            'name' => 'manage_servertype',
            'display_name' => 'Manage Server Type',
            'allowed_permissions' => json_encode(['all' => 4, 'none' => 5]),
            'is_custom' => 1,
        ];

        // Create or update permission
        $permission = Permission::updateOrCreate(
            [
                'name' => $servertypePermission['name'],
                'module_id' => $module->id,
            ],
            [
                'display_name' => $servertypePermission['display_name'],
                'allowed_permissions' => $servertypePermission['allowed_permissions'],
                'is_custom' => $servertypePermission['is_custom'],
            ]
        );

        // Get all companies
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            // Assign 'all' permission to admin role
            $adminRole = Role::where('name', 'admin')
                ->where('company_id', $company->id)
                ->first();

            if ($adminRole) {
                $permissionRole = PermissionRole::where('permission_id', $permission->id)
                    ->where('role_id', $adminRole->id)
                    ->first();

                $permissionRole = $permissionRole ?: new PermissionRole();
                $permissionRole->permission_id = $permission->id;
                $permissionRole->role_id = $adminRole->id;
                $permissionRole->permission_type_id = 4; // All
                $permissionRole->save();
            }

            // Assign 'none' permission to other roles (employee, client, etc.)
            $otherRoles = Role::where('company_id', $company->id)
                ->where('name', '!=', 'admin')
                ->get();

            foreach ($otherRoles as $role) {
                $permissionRole = PermissionRole::where('permission_id', $permission->id)
                    ->where('role_id', $role->id)
                    ->first();

                $permissionRole = $permissionRole ?: new PermissionRole();
                $permissionRole->permission_id = $permission->id;
                $permissionRole->role_id = $role->id;
                $permissionRole->permission_type_id = 5; // None
                $permissionRole->save();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get the Server Manager module
        $module = Module::where('module_name', 'servermanager')->first();

        if (!$module) {
            return;
        }

        // Get manage_servertype permission
        $permission = Permission::where('module_id', $module->id)
            ->where('name', 'manage_servertype')
            ->first();

        if ($permission) {
            // Delete all related permission roles
            PermissionRole::where('permission_id', $permission->id)->delete();

            // Delete all related user permissions
            UserPermission::where('permission_id', $permission->id)->delete();

            // Delete the permission itself
            $permission->delete();
        }
    }
};

