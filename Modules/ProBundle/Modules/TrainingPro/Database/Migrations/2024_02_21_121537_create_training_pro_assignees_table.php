<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		if (!Schema::hasTable('training_pro_assignees')) {
			Schema::create('training_pro_assignees', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');

				$table->unsignedInteger('user_id')->unsigned()->nullable();
				$table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedInteger('role_id')->unsigned()->nullable();
				$table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('designation_id')->unsigned()->nullable();
				$table->foreign('designation_id')->references('id')->on('designations')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedInteger('department_id')->unsigned()->nullable();
				$table->foreign('department_id')->references('id')->on('teams')->onDelete('restrict')->onUpdate('cascade');

				$table->unsignedBigInteger('category_id')->unsigned();
				$table->foreign('category_id')->references('id')->on('training_pro_categories')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('programme_id')->unsigned()->nullable();
				$table->foreign('programme_id')->references('id')->on('training_pro_programmes')->onDelete('restrict')->onUpdate('cascade');

				$table->integer('order')->default(0);
				$table->integer('added_by')->default(1);
				$table->integer('updated_by')->nullable();
				$table->tinyInteger('is_enabled')->default(0);

				$table->timestamps();
			});
		};
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_pro_assignees');
    }
};
