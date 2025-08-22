<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Services\Billing\PlanGate;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the team members.
     */
    public function viewTeamMembers(User $user, Company $company): bool
    {
        // All users with access to a company can view the team members
        return $user->belongsToCompany($company);
    }

    /**
     * Determine if the user can invite team members.
     */
    public function inviteTeamMember(User $user, Company $company): bool
    {
        // First check if user has permission based on role
        $hasRolePermission = $user->isOwnerOf($company) ||
                            in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);

        if (!$hasRolePermission) {
            return false;
        }

        // Then check subscription plan allows team invitations
        $planId = $company->subscription_plan ?? 'free';
        return PlanGate::allows($planId, 'team.invitations', false);
    }

    /**
     * Determine if the user can remove team members.
     */
    public function removeTeamMember(User $user, Company $company): bool
    {
        // Only company owner and managers can remove team members
        return $user->isOwnerOf($company) ||
               in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);
    }

    /**
     * Determine if the user can update team member roles.
     */
    public function updateTeamMember(User $user, Company $company): bool
    {
        // Only company owner and managers can update team member roles
        return $user->isOwnerOf($company) ||
               in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);
    }

    /**
     * Determine if the user can manage team invitations.
     */
    public function manageInvitations(User $user, Company $company): bool
    {
        // Only company owner and managers can manage invitations
        return $user->isOwnerOf($company) ||
               in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);
    }
}
