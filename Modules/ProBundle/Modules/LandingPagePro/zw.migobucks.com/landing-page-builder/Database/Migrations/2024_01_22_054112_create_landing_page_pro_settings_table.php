<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\LandingPagePro\Entities\LandingPageProSetting;
use App\Models\Module;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Module::validateVersion(LandingPageProSetting::MODULE_NAME);

		if (!Schema::hasTable('landing_page_pro_settings')) {
			Schema::create('landing_page_pro_settings', function (Blueprint $table) {
				$table->id();
				$table->integer('package_id');
				$table->integer('page_limit');
				$table->integer('category_limit');
				$table->integer('added_by')->default(1);
				$table->tinyInteger('status')->default(0);
				$table->timestamps();
			});
		};

		$module = Module::firstOrCreate(
			[
				'module_name' => 'landingpagepro',
				'description' => 'Create multiple landing page using pre-build templates',
				'is_superadmin' => 0,
			]);


		Permission::insert(
			[
				['name' => 'add_landingpage', 'display_name' => 'Add Landing Page', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'view_landingpage', 'display_name' => 'View Landing Page', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'edit_landingpage', 'display_name' => 'Edit Landing Page', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'delete_landingpage', 'display_name' => 'Delete Landing Page', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'add_landingpagecategory', 'display_name' => 'Add Landing Page Category', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'view_landingpagecategory', 'display_name' => 'View Landing Page Category', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'edit_landingpagecategory', 'display_name' => 'Edit Landing Page Category', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'delete_landingpagecategory', 'display_name' => 'Delete Landing Page Category', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_pro_settings');

		// Find the module by module_name
		$module = Module::where('module_name', 'landingpagepro')->first();
		// Check if the module exists before attempting to delete
		if ($module) {
			// Delete the related permissions
			$module->permissions()->delete();
			// Delete the module
			$module->delete();
		}
    }
};
