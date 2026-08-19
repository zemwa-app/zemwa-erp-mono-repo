<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\Permission;
use Modules\TrainingPro\Entities\TrainingProSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Module::validateVersion(TrainingProSetting::MODULE_NAME);
        Schema::create('training_pro_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
		$module = Module::firstOrCreate(
			[
				'module_name' => 'trainingpro',
				'description' => 'Create training program for employees and members.',
				'is_superadmin' => 0,
			]);

		$permissions = [
			['name' => 'add_category', 'display_name' => 'Add Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_category', 'display_name' => 'View Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_category', 'display_name' => 'Edit Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_category', 'display_name' => 'Delete Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

			['name' => 'add_program', 'display_name' => 'Add Program', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_program', 'display_name' => 'View Program', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_program', 'display_name' => 'Edit Program', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_program', 'display_name' => 'Delete Program', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

			['name' => 'add_topic', 'display_name' => 'Add Topic', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_topic', 'display_name' => 'View Topic', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_topic', 'display_name' => 'Edit Topic', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_topic', 'display_name' => 'Delete Topic', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

			['name' => 'add_assessment', 'display_name' => 'Add Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_assessment', 'display_name' => 'View Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_assessment', 'display_name' => 'Edit Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_assessment', 'display_name' => 'Delete Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

			['name' => 'add_result', 'display_name' => 'Add Result', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_result', 'display_name' => 'View Result', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_result', 'display_name' => 'Edit Result', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_result', 'display_name' => 'Delete Result', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
		];

		foreach ($permissions as $perm) {
			Permission::firstOrCreate(['name' => $perm['name']], $perm);
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_pro_settings');

		// Find the module by module_name
		$module = Module::where('module_name', 'trainingpro')->first();
		// Check if the module exists before attempting to delete
		if ($module) {
			// Delete the related permissions
			$module->permissions()->delete();
			// Delete the module
			$module->delete();
		}
    }
};
