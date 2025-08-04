<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyTeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can invite team members to the company
     */
    public function inviteTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.invite') && $user->canAccessCompany($company);
    }

    /**
     * Determine if the user can update team member roles
     */
    public function updateTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.update_roles') && $user->canAccessCompany($company);
    }

    /**
     * Determine if the user can update team member permissions
     */
    public function updateTeamPermissions(User $user, Company $company): bool
    {
        return $user->can('team.update_permissions') && $user->canAccessCompany($company);
    }

    /**
     * Determine if the user can remove team members
     */
    public function removeTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.remove') && $user->canAccessCompany($company);
    }

    /**
     * Determine if the user can view team members
     */
    public function viewTeamMembers(User $user, Company $company): bool
    {
        return $user->can('team.view_any') && $user->canAccessCompany($company);
    }

    /**
     * Determine if the user can view specific team member details
     */
    public function viewTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.view') && $user->canAccessCompany($company);
    }

    /**
     * Determine if the user can manage team invitations
     */
    public function manageInvitations(User $user, Company $company): bool
    {
        return $user->can('team.manage_invitations') && $user->canAccessCompany($company);
    }

    /**
     * Determine if the user can view team activity
     */
    public function viewTeamActivity(User $user, Company $company): bool
    {
        return $user->can('team.view_activity') && $user->canAccessCompany($company);
    }
}
