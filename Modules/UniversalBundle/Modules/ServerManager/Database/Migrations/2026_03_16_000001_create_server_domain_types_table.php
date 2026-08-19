<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\ServerManager\Listeners\CompanyCreatedListener;
use App\Models\Company;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_domain_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('type', 50);
            $table->string('label', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_domain_types');
    }
};
