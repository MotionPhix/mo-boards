<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
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
        // Only company owner and admins can invite team members
        return $user->id === $company->owner_id || 
               ($user->belongsToCompany($company) && in_array($user->getRoleInCompany($company), ['admin']));
    }

    /**
     * Determine if the user can remove team members.
     */
    public function removeTeamMember(User $user, Company $company): bool
    {
        // Only company owner and admins can remove team members
        return $user->id === $company->owner_id || 
               ($user->belongsToCompany($company) && in_array($user->getRoleInCompany($company), ['admin']));
    }

    /**
     * Determine if the user can update team member roles.
     */
    public function updateTeamMember(User $user, Company $company): bool
    {
        // Only company owner and admins can update team member roles
        return $user->id === $company->owner_id || 
               ($user->belongsToCompany($company) && in_array($user->getRoleInCompany($company), ['admin']));
    }
}
