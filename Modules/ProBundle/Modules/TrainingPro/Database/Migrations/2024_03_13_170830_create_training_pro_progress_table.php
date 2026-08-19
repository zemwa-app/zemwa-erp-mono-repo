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
		if (!Schema::hasTable('training_pro_progress')) {
			Schema::create('training_pro_progress', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');

				$table->unsignedInteger('user_id')->unsigned();
				$table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('programme_id')->unsigned()->nullable();
				$table->foreign('programme_id')->references('id')->on('training_pro_programmes')->onDelete('restrict')->onUpdate('cascade');

				$table->timestamp('entry_at');
				$table->timestamp('exit_at');
				$table->integer('spent_time')->default(0);

				$table->timestamps();
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_pro_progress');
    }
};
