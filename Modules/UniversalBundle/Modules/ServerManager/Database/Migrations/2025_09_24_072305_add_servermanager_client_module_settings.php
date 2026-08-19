<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use App\Models\Company;
use App\Models\ModuleSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all companies to add module settings for each
        $companies = Company::all();

        foreach ($companies as $company) {
            // Check if servermanager module setting already exists for this company
            $existingSetting = ModuleSetting::where('company_id', $company->id)
                ->where('module_name', 'servermanager')
                ->where('type', 'client')
                ->first();

            // Only insert if it doesn't exist
            if (!$existingSetting) {
                DB::table('module_settings')->insert([
                    'company_id' => $company->id,
                    'module_name' => 'servermanager',
                    'status' => 'active',
                    'type' => 'client',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Update permission table for roles connected to servermanager module
        $servermanagerModule = Module::where('module_name', 'servermanager')->first();
        if ($servermanagerModule) {

            $permissions = DB::table('permissions')
                ->where('module_id', $servermanagerModule->id)
                ->get();
            // dd($permissions);
            // Update permissions where allowed_permissions is ['all'=>4,'added'=>1,'none'=>5]
            $targetAllowed = json_encode(['all' => 4, 'added' => 1, 'none' => 5]);
            $newAllowed = json_encode(['all' => 4, 'added' => 1, 'owned' => 2, 'both' => 3, 'none' => 5]);

            foreach ($permissions as $permission) {
                if ($permission->allowed_permissions === $targetAllowed) {
                    DB::table('permissions')
                        ->where('id', $permission->id)
                        ->update(['allowed_permissions' => $newAllowed]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove servermanager client module settings
        DB::table('module_settings')
            ->where('module_name', 'servermanager')
            ->where('type', 'client')
            ->delete();
    }
};
