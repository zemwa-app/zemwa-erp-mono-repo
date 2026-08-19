<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('productivity_rules')) {
            Schema::create('productivity_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->enum('type', ['url', 'app']);
                $table->string('pattern', 255);
                $table->enum('category', ['productive', 'neutral', 'unproductive']);
                $table->string('subcategory', 64);
                $table->unsignedSmallInteger('priority')->default(10);
                $table->unsignedInteger('match_count')->default(0);
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->unique(['company_id', 'type', 'pattern']);
                $table->index(['company_id', 'type', 'priority']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('productivity_rules');
    }
};
