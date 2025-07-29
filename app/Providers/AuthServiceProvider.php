<?php

namespace App\Providers;

use App\Models\Billboard;
use App\Models\Company;
use App\Policies\BillboardPolicy;
use App\Policies\CompanyTeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Billboard::class => BillboardPolicy::class,
        Company::class => CompanyTeamPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
