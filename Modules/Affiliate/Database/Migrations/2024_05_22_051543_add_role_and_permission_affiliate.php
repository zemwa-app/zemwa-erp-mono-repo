<?php

use App\Models\Role;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Modules\Affiliate\Entities\AffiliateGlobalSetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $module = Module::withoutGlobalScopes()->firstOrCreate([
            'module_name' => AffiliateGlobalSetting::MODULE_NAME,
            'is_superadmin' => 1
        ]);

        $permissions = [
            ['name' => 'add_affiliates', 'display_name' => 'Add Affiliates', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_affiliates', 'display_name' => 'View Affiliates', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'edit_affiliates', 'display_name' => 'Edit Affiliates', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'delete_affiliates', 'display_name' => 'Delete Affiliates', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'manage_affiliate_status', 'display_name' => 'Manage Affiliates Status', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'manage_affiliate_settings', 'display_name' => 'Manage Affiliate Settings', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],

            ['name' => 'view_affiliate_dashboard', 'display_name' => 'View Affiliates Dashboard', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],

            ['name' => 'add_referrals', 'display_name' => 'Add Referrals', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_referrals', 'display_name' => 'View Referrals', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],

            ['name' => 'add_payouts', 'display_name' => 'Add Payouts', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_payouts', 'display_name' => 'View Payouts', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'edit_payouts', 'display_name' => 'Edit Payouts', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'delete_payouts', 'display_name' => 'Delete Payouts', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'manage_payout_status', 'display_name' => 'Manage Payout Status', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
        ];

        $role = Role::where('name', 'superadmin')->first();
        $superadmins = User::withoutGlobalScopes()->whereNull('company_id')->where('is_superadmin', 1)->get();

        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate($permission);

            if ($role) {
                $permissionData = PermissionRole::where('permission_id', $perm->id)
                    ->where('role_id', $role->id)->where('permission_type_id', 4)->first();

                if (is_null($permissionData)) {
                    $permissionRole = new PermissionRole();
                    $permissionRole->permission_id = $perm->id;
                    $permissionRole->role_id = $role->id;
                    $permissionRole->permission_type_id = 4;
                    $permissionRole->save();
                }
            }
        }

        $modulePermissions = Permission::where('module_id', $module->id)->get();

        foreach ($superadmins as $superadmin) {
            foreach ($modulePermissions as $permission) {
                UserPermission::firstOrCreate([
                    'user_id' => $superadmin->id,
                    'permission_id' => $permission->id,
                    'permission_type_id' => 4
                ]);
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
