<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\ProBundle\Entities\ProBundleSetting;

return new class extends Migration {

    public function up()
    {
        if (!Schema::hasTable('pro_bundle_settings')) {
            Schema::create('pro_bundle_settings', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_code')->nullable();
                $table->timestamp('supported_until')->nullable();
                $table->timestamps();
            });

            ProBundleSetting::create([
                'purchase_code' => 'my-custom-code-123',
                'supported_until' => now()->addYears(10)
            ]);
        }

        if (!Schema::hasTable('pro_module_installs')) {
            Schema::create('pro_module_installs', function (Blueprint $table) {
                $table->id();
                $table->string('module_name');
                $table->string('version')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pro_bundle_settings');
        Schema::dropIfExists('pro_module_installs');
    }

};
