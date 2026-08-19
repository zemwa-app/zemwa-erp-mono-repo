<?php

namespace App\Providers;

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        /* 'App\Models\Model' => 'App\Policies\ModelPolicy', */
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Auth uses UserAuth; roles/is_superadmin live on User
        Gate::define('viewLogViewer', function ($user) {
            if (!$user) {
                return false;
            }

            return User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
                ->where('user_auth_id', $user->id)
                ->where('is_superadmin', 1)
                ->exists();
        });
    }

}
