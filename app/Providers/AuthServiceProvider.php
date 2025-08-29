<?php

namespace App\Providers;

use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
use App\Policies\BillboardPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\CompanyTeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The model to policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    Billboard::class => BillboardPolicy::class,
    Company::class => CompanyPolicy::class,
  ];

  /**
   * Register any authentication / authorization services.
   */
  public function boot(): void
  {
    $this->registerPolicies();

    // Register team-related gates manually since they don't map to a single model
    Gate::define('team-view-any', [CompanyTeamPolicy::class, 'viewAny']);
    Gate::define('team-view', [CompanyTeamPolicy::class, 'view']);
    Gate::define('team-update', [CompanyTeamPolicy::class, 'update']);
    Gate::define('team-delete', [CompanyTeamPolicy::class, 'delete']);
    Gate::define('team-invite', [CompanyTeamPolicy::class, 'invite']);
    Gate::define('team-manage-invitations', [CompanyTeamPolicy::class, 'manageInvitations']);

    // Or, if you prefer method-based gates, you can define them like this:
    Gate::define('viewTeamMembers', function (User $user, Company $company) {
      return $user->canAccessCompany($company);
    });

    Gate::define('inviteTeamMember', function (User $user, Company $company) {
      if (!$user->canAccessCompany($company)) {
        return false;
      }

      $hasRolePermission = $user->isOwnerOf($company) ||
        in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);

      if (!$hasRolePermission) {
        return false;
      }

      // Check subscription plan allows team invitations
      $planId = $company->subscription_plan ?? 'free';
      return \App\Services\Billing\PlanGate::allows($planId, 'team.invitations', false);
    });

    Gate::define('removeTeamMember', function (User $user, Company $company) {
      if (!$user->canAccessCompany($company)) {
        return false;
      }

      return $user->isOwnerOf($company) ||
        in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);
    });
  }
}
