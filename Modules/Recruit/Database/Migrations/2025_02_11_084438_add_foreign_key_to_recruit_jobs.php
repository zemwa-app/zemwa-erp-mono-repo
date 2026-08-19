<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Before adding the foreign key, set recruiter_id to null where not present in users table
        DB::table('recruit_jobs')
            ->whereNotNull('recruiter_id')
            ->whereNotIn('recruiter_id', function ($query) {
                $query->select('id')->from('users');
            })
            ->update(['recruiter_id' => null]);

        $foreignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'recruit_jobs')
            ->where('CONSTRAINT_NAME', 'recruit_jobs_recruiter_id_foreign')
            ->exists();

        Schema::table('recruit_jobs', function (Blueprint $table) use ($foreignKeyExists) {
            $table->unsignedInteger('recruiter_id')->nullable()->change();

            // Add constraint only if not exists (for idempotency in repeated migrations)
            if (!$foreignKeyExists) {
                $table->foreign('recruiter_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruit_jobs', function (Blueprint $table) {
            $table->dropForeign(['recruiter_id']);
            $table->unsignedInteger('recruiter_id')->nullable(false)->change();
        });
    }
};
