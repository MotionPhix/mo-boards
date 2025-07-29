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
    public function inviteTeamMember(User $user, Company $company)
    {
        // Super admin or company owner can invite team members
        if ($user->hasRole('super_admin') || $user->isOwnerOf($company)) {
            return true;
        }
        
        // Managers can invite team members
        if ($user->getRoleInCompany($company) === 'manager' && $user->hasPermissionTo('invite users')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine if the user can update team member roles
     */
    public function updateTeamMember(User $user, Company $company)
    {
        // Only super admin or company owner can update team member roles
        return $user->hasRole('super_admin') || $user->isOwnerOf($company);
    }
    
    /**
     * Determine if the user can remove team members
     */
    public function removeTeamMember(User $user, Company $company)
    {
        // Only super admin or company owner can remove team members
        if ($user->hasRole('super_admin') || $user->isOwnerOf($company)) {
            return true;
        }
        
        // Managers can remove team members except owners
        if ($user->getRoleInCompany($company) === 'manager' && $user->hasPermissionTo('remove users')) {
            return true;
        }
        
        return false;
    }
}
