<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\Permission;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('public_assessment_pro_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('package_id');
			$table->integer('assessment_limit');
			$table->integer('added_by');
			$table->tinyInteger('status')->default(0);
            $table->timestamps();
        });


	$module = Module::firstOrCreate(
			[
				'module_name' => 'publicassessmentpro',
				'description' => 'Create Assessment program for public with sharable link.',
				'is_superadmin' => 0,
			]);

		Permission::where('module_id', $module->id)->delete();

		$permissions = [
			['name' => 'add_assessment', 'display_name' => 'Add Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_assessment', 'display_name' => 'View Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_assessment', 'display_name' => 'Edit Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_assessment', 'display_name' => 'Delete Assessment', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

			['name' => 'add_category', 'display_name' => 'Add Question Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_category', 'display_name' => 'View Question Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_category', 'display_name' => 'Edit Question Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_category', 'display_name' => 'Delete Question Category', 'module_id' => $module->id, 'is_custom' => 0, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

			['name' => 'add_question', 'display_name' => 'Add Question', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_question', 'display_name' => 'View Question', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_question', 'display_name' => 'Edit Question', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_question', 'display_name' => 'Delete Question', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

			['name' => 'add_answer', 'display_name' => 'Add Answer', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'view_answer', 'display_name' => 'View Answer', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'edit_answer', 'display_name' => 'Edit Answer', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			['name' => 'delete_answer', 'display_name' => 'Delete Answer', 'module_id' => $module->id, 'is_custom' => 1, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],

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
        Schema::dropIfExists('public_assessment_pro_settings');
		// Find the module by module_name
		$module = Module::where('module_name', 'publicassessmentpro')->first();
		// Check if the module exists before attempting to delete
		if ($module) {
			// Delete the related permissions
			$module->permissions()->delete();
			// Delete the module
			$module->delete();
		}
    }
};
