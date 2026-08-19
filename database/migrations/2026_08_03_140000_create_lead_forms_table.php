<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_forms') && !Schema::hasColumn('lead_custom_forms', 'lead_form_id')) {
            Schema::dropIfExists('lead_forms');
        }

        if (!Schema::hasTable('lead_forms')) {
            Schema::create('lead_forms', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('company_id')->index();
                $table->string('name');
                $table->string('slug');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->boolean('is_default')->default(false);
                $table->unsignedBigInteger('lead_pipeline_id')->nullable()->index();
                $table->unsignedInteger('pipeline_stage_id')->nullable()->index();
                $table->unsignedInteger('category_id')->nullable()->index();
                $table->unsignedInteger('lead_source_id')->nullable()->index();
                $table->unsignedInteger('added_by')->nullable()->index();
                $table->unsignedInteger('last_updated_by')->nullable()->index();
                $table->timestamps();

                $table->unique(['company_id', 'slug']);
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('set null')->onUpdate('cascade');
                $table->foreign('pipeline_stage_id')->references('id')->on('pipeline_stages')->onDelete('set null')->onUpdate('cascade');
                $table->foreign('category_id')->references('id')->on('lead_category')->onDelete('set null')->onUpdate('cascade');
                $table->foreign('lead_source_id')->references('id')->on('lead_sources')->onDelete('set null')->onUpdate('cascade');
            });
        }

        if (!Schema::hasColumn('lead_custom_forms', 'lead_form_id')) {
            Schema::table('lead_custom_forms', function (Blueprint $table) {
                $table->unsignedInteger('lead_form_id')->nullable()->after('company_id')->index();
            });
        }

        if (!Schema::hasColumn('deals', 'lead_form_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->unsignedInteger('lead_form_id')->nullable()->after('company_id')->index();
            });
        }

        $this->backfillDefaultForms();

        if (Schema::hasColumn('lead_custom_forms', 'lead_form_id')) {
            Schema::table('lead_custom_forms', function (Blueprint $table) {
                if (!$this->foreignKeyExists('lead_custom_forms', 'lead_custom_forms_lead_form_id_foreign')) {
                    $table->foreign('lead_form_id')->references('id')->on('lead_forms')->onDelete('cascade')->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasColumn('deals', 'lead_form_id')) {
            Schema::table('deals', function (Blueprint $table) {
                if (!$this->foreignKeyExists('deals', 'deals_lead_form_id_foreign')) {
                    $table->foreign('lead_form_id')->references('id')->on('lead_forms')->onDelete('set null')->onUpdate('cascade');
                }
            });
        }
    }

    private function backfillDefaultForms(): void
    {
        $companyIds = DB::table('lead_custom_forms')
            ->select('company_id')
            ->distinct()
            ->pluck('company_id');

        foreach ($companyIds as $companyId) {
            if (DB::table('lead_forms')->where('company_id', $companyId)->exists()) {
                DB::table('lead_custom_forms')
                    ->where('company_id', $companyId)
                    ->whereNull('lead_form_id')
                    ->update(['lead_form_id' => DB::table('lead_forms')->where('company_id', $companyId)->value('id')]);

                continue;
            }

            $pipeline = DB::table('lead_pipelines')
                ->where('company_id', $companyId)
                ->where('default', 1)
                ->first();

            $stage = null;

            if ($pipeline) {
                $stage = DB::table('pipeline_stages')
                    ->where('lead_pipeline_id', $pipeline->id)
                    ->where('company_id', $companyId)
                    ->where('default', 1)
                    ->first();
            }

            $category = DB::table('lead_category')
                ->where('company_id', $companyId)
                ->where('is_default', 1)
                ->first();

            $formId = DB::table('lead_forms')->insertGetId([
                'company_id' => $companyId,
                'name' => 'Default',
                'slug' => 'default',
                'status' => 'active',
                'is_default' => 1,
                'lead_pipeline_id' => $pipeline?->id,
                'pipeline_stage_id' => $stage?->id,
                'category_id' => $category?->id,
                'lead_source_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lead_custom_forms')
                ->where('company_id', $companyId)
                ->whereNull('lead_form_id')
                ->update(['lead_form_id' => $formId]);
        }
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$database, $table, $foreignKey, 'FOREIGN KEY']
        );

        return !empty($result);
    }

    public function down(): void
    {
        if (Schema::hasColumn('deals', 'lead_form_id')) {
            Schema::table('deals', function (Blueprint $table) {
                if ($this->foreignKeyExists('deals', 'deals_lead_form_id_foreign')) {
                    $table->dropForeign(['lead_form_id']);
                }
                $table->dropColumn('lead_form_id');
            });
        }

        if (Schema::hasColumn('lead_custom_forms', 'lead_form_id')) {
            Schema::table('lead_custom_forms', function (Blueprint $table) {
                if ($this->foreignKeyExists('lead_custom_forms', 'lead_custom_forms_lead_form_id_foreign')) {
                    $table->dropForeign(['lead_form_id']);
                }
                $table->dropColumn('lead_form_id');
            });
        }

        Schema::dropIfExists('lead_forms');
    }
};
