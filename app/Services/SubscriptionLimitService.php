<?php

namespace App\Services;

use App\Models\Company;
use App\Services\Billing\PlanGate;

class SubscriptionLimitService
{
    /**
     * Check if company can create a new billboard
     */
    public function canCreateBillboard(Company $company): bool
    {
        $planId = $company->subscription_plan ?? 'free';
        $limit = PlanGate::limit($planId, 'billboards.max');
        
        if ($limit === null) {
            return true; // Unlimited
        }
        
        $current = $company->billboards()->count();
        return $current < $limit;
    }

    /**
     * Check if company can create a new contract
     */
    public function canCreateContract(Company $company): bool
    {
        $planId = $company->subscription_plan ?? 'free';
        $limit = PlanGate::limit($planId, 'contracts.max');
        
        if ($limit === null) {
            return true; // Unlimited
        }
        
        $current = $company->contracts()->where('status', 'active')->count();
        return $current < $limit;
    }

    /**
     * Check if company can invite team members
     */
    public function canInviteTeamMember(Company $company): bool
    {
        $planId = $company->subscription_plan ?? 'free';
        
        // Check if team invitations are allowed
        if (!PlanGate::allows($planId, 'team.invitations')) {
            return false;
        }
        
        $limit = PlanGate::limit($planId, 'team.members.max');
        
        if ($limit === null) {
            return true; // Unlimited
        }
        
        $current = $company->users()->count();
        return $current < $limit;
    }

    /**
     * Check if company can create contract templates
     */
    public function canCreateTemplate(Company $company): bool
    {
        $planId = $company->subscription_plan ?? 'free';
        $limit = PlanGate::limit($planId, 'templates.max');
        
        if ($limit === null) {
            return true; // Unlimited
        }
        
        $current = $company->contractTemplates()->count();
        return $current < $limit;
    }

    /**
     * Get usage summary for a company
     */
    public function getUsageSummary(Company $company): array
    {
        $planId = $company->subscription_plan ?? 'free';
        
        return [
            'plan' => $planId,
            'billboards' => [
                'current' => $company->billboards()->count(),
                'limit' => PlanGate::limit($planId, 'billboards.max'),
                'can_create' => $this->canCreateBillboard($company),
            ],
            'contracts' => [
                'current' => $company->contracts()->where('status', 'active')->count(),
                'limit' => PlanGate::limit($planId, 'contracts.max'),
                'can_create' => $this->canCreateContract($company),
            ],
            'team_members' => [
                'current' => $company->users()->count(),
                'limit' => PlanGate::limit($planId, 'team.members.max'),
                'can_invite' => $this->canInviteTeamMember($company),
            ],
            'templates' => [
                'current' => $company->contractTemplates()->count(),
                'limit' => PlanGate::limit($planId, 'templates.max'),
                'can_create' => $this->canCreateTemplate($company),
            ],
            'features' => [
                'team_invitations' => PlanGate::allows($planId, 'team.invitations'),
                'advanced_analytics' => PlanGate::allows($planId, 'analytics.advanced'),
                'insights' => PlanGate::allows($planId, 'analytics.insights'),
                'email_notifications' => PlanGate::allows($planId, 'notifications.email'),
                'sms_notifications' => PlanGate::allows($planId, 'notifications.sms'),
                'realtime_notifications' => PlanGate::allows($planId, 'notifications.realtime'),
                'api_access' => PlanGate::allows($planId, 'api.access'),
                'export_enabled' => PlanGate::allows($planId, 'export.enabled'),
                'bulk_operations' => PlanGate::allows($planId, 'bulk.operations'),
                'priority_support' => PlanGate::allows($planId, 'support.priority'),
                'phone_support' => PlanGate::allows($planId, 'support.phone'),
            ]
        ];
    }

    /**
     * Get remaining quota for a specific resource
     */
    public function getRemainingQuota(Company $company, string $resource): ?int
    {
        $planId = $company->subscription_plan ?? 'free';
        $limit = PlanGate::limit($planId, "{$resource}.max");
        
        if ($limit === null) {
            return null; // Unlimited
        }
        
        $current = match($resource) {
            'billboards' => $company->billboards()->count(),
            'contracts' => $company->contracts()->where('status', 'active')->count(),
            'team.members' => $company->users()->count(),
            'templates' => $company->contractTemplates()->count(),
            default => 0
        };
        
        return max(0, $limit - $current);
    }
}
