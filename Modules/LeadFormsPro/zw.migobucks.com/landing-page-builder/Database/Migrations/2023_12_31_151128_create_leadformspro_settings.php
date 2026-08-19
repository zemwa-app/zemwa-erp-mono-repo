<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\LeadFormsPro\Entities\LeadFormsProSetting;
use App\Models\Module;
use App\Models\Permission;

return new class extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\App\Models\Module::validateVersion(LeadFormsProSetting::MODULE_NAME);

		if (!Schema::hasTable('lead_forms_pro_settings')) {
			Schema::create('lead_forms_pro_settings', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('package_id');
				$table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade')->onUpdate('cascade');
				$table->integer('form_limit');
				$table->integer('category_limit');
				$table->integer('added_by')->default(1);
				$table->tinyInteger('status')->default(0);
				$table->timestamps();
			});
		}

		$module = Module::firstOrCreate(
			[
				'module_name' => 'leadformspro',
				'description' => 'Create multiple lead forms',
				'is_superadmin' => 0,
			]);

		Permission::insert(
			[
				['name' => 'add_leadform', 'display_name' => 'Add Lead Form', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'view_leadform', 'display_name' => 'View Lead Form', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'edit_leadform', 'display_name' => 'Edit Lead Form', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'delete_leadform', 'display_name' => 'Delete Lead Form', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'add_leadformcategory', 'display_name' => 'Add Lead Form Category', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'view_leadformcategory', 'display_name' => 'View Lead Form Category', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'edit_leadformcategory', 'display_name' => 'Edit Lead Form Category', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
				['name' => 'delete_leadformcategory', 'display_name' => 'Delete Lead Form Category', 'module_id' => $module->id, 'allowed_permissions' => '{"all":4, "added":1, "owned":2,"both":3, "none":5}'],
			]);

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('lead_forms_pro_settings');

		// Find the module by module_name
		$module = Module::where('module_name', 'leadformspro')->first();
		// Check if the module exists before attempting to delete
		if ($module) {
			// Delete the related permissions
			$module->permissions()->delete();
			// Delete the module
			$module->delete();
		}
	}
};
