<?php

namespace App\Services;

use App\Enums\SubscriptionFeature;
use App\Models\Company;
use App\Models\BillingPlan;
use Illuminate\Support\Collection;

class SubscriptionFeatureService
{
    /**
     * Check if a company has access to a specific feature
     */
    public function hasFeature(Company $company, SubscriptionFeature $feature): bool
    {
        $planFeatures = $this->getCompanyFeatures($company);
        
        return $planFeatures->contains($feature->value);
    }

    /**
     * Check if a company has reached the limit for a specific feature
     */
    public function hasReachedLimit(Company $company, SubscriptionFeature $limitFeature, int $currentCount): bool
    {
        if (!$this->hasFeature($company, $limitFeature)) {
            return false; // No limit if feature not applicable
        }

        $limit = $limitFeature->getLimit();
        
        return $limit !== null && $currentCount >= $limit;
    }

    /**
     * Get the limit for a specific feature
     */
    public function getFeatureLimit(Company $company, SubscriptionFeature $limitFeature): ?int
    {
        if (!$this->hasFeature($company, $limitFeature)) {
            return null;
        }

        return $limitFeature->getLimit();
    }

    /**
     * Get all features available to a company
     */
    public function getCompanyFeatures(Company $company): Collection
    {
        // Get current subscription plan
        $currentSubscription = $company->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();

        if (!$currentSubscription) {
            // Fall back to company subscription_plan field or default to free
            $planKey = $company->subscription_plan ?? 'free';
        } else {
            $planKey = $currentSubscription->plan_name;
        }

        return $this->getPlanFeatures($planKey);
    }

    /**
     * Get features for a specific plan
     */
    public function getPlanFeatures(string $planKey): Collection
    {
        $features = match($planKey) {
            'free' => [
                SubscriptionFeature::BASIC_BILLBOARDS->value,
                SubscriptionFeature::BASIC_CONTRACTS->value,
                SubscriptionFeature::OWNER_ONLY->value,
                SubscriptionFeature::BASIC_TEMPLATES->value,
                SubscriptionFeature::BASIC_REPORTING->value,
                SubscriptionFeature::EMAIL_SUPPORT->value,
                SubscriptionFeature::MAX_BILLBOARDS_5->value,
                SubscriptionFeature::MAX_CONTRACTS_2->value,
            ],
            'pro' => [
                SubscriptionFeature::BASIC_BILLBOARDS->value,
                SubscriptionFeature::BASIC_CONTRACTS->value,
                SubscriptionFeature::TEAM_INVITATIONS->value,
                SubscriptionFeature::BASIC_TEMPLATES->value,
                SubscriptionFeature::ADVANCED_ANALYTICS->value,
                SubscriptionFeature::PRIORITY_SUPPORT->value,
                SubscriptionFeature::EMAIL_NOTIFICATIONS->value,
                SubscriptionFeature::MAX_BILLBOARDS_25->value,
                SubscriptionFeature::MAX_CONTRACTS_15->value,
                SubscriptionFeature::MAX_TEAM_MEMBERS_3->value,
                SubscriptionFeature::MAX_TEMPLATES_5->value,
            ],
            'business' => [
                SubscriptionFeature::UNLIMITED_BILLBOARDS->value,
                SubscriptionFeature::UNLIMITED_CONTRACTS->value,
                SubscriptionFeature::UNLIMITED_TEAM_MEMBERS->value,
                SubscriptionFeature::UNLIMITED_TEMPLATES->value,
                SubscriptionFeature::ADVANCED_ANALYTICS->value,
                SubscriptionFeature::ADVANCED_INSIGHTS->value,
                SubscriptionFeature::PHONE_SUPPORT->value,
                SubscriptionFeature::EMAIL_NOTIFICATIONS->value,
                SubscriptionFeature::SMS_NOTIFICATIONS->value,
                SubscriptionFeature::REAL_TIME_NOTIFICATIONS->value,
                SubscriptionFeature::API_ACCESS->value,
                SubscriptionFeature::EXPORT_FUNCTIONALITY->value,
                SubscriptionFeature::BULK_OPERATIONS->value,
            ],
            default => [
                SubscriptionFeature::BASIC_BILLBOARDS->value,
                SubscriptionFeature::BASIC_CONTRACTS->value,
                SubscriptionFeature::OWNER_ONLY->value,
                SubscriptionFeature::MAX_BILLBOARDS_5->value,
                SubscriptionFeature::MAX_CONTRACTS_2->value,
            ]
        };

        return collect($features);
    }

    /**
     * Check billboard count limits
     */
    public function canCreateBillboard(Company $company): bool
    {
        $currentCount = $company->billboards()->count();

        // Check for specific limits
        if ($this->hasReachedLimit($company, SubscriptionFeature::MAX_BILLBOARDS_5, $currentCount)) {
            return false;
        }

        if ($this->hasReachedLimit($company, SubscriptionFeature::MAX_BILLBOARDS_25, $currentCount)) {
            return false;
        }

        // Business plan has unlimited billboards
        return $this->hasFeature($company, SubscriptionFeature::UNLIMITED_BILLBOARDS) || 
               $this->hasFeature($company, SubscriptionFeature::BASIC_BILLBOARDS);
    }

    /**
     * Check contract count limits
     */
    public function canCreateContract(Company $company): bool
    {
        $currentCount = $company->contracts()->where('status', 'active')->count();

        // Check for specific limits
        if ($this->hasReachedLimit($company, SubscriptionFeature::MAX_CONTRACTS_2, $currentCount)) {
            return false;
        }

        if ($this->hasReachedLimit($company, SubscriptionFeature::MAX_CONTRACTS_15, $currentCount)) {
            return false;
        }

        // Business plan has unlimited contracts
        return $this->hasFeature($company, SubscriptionFeature::UNLIMITED_CONTRACTS) || 
               $this->hasFeature($company, SubscriptionFeature::BASIC_CONTRACTS);
    }

    /**
     * Check team member limits
     */
    public function canInviteTeamMember(Company $company): bool
    {
        if (!$this->hasFeature($company, SubscriptionFeature::TEAM_INVITATIONS) && 
            !$this->hasFeature($company, SubscriptionFeature::UNLIMITED_TEAM_MEMBERS)) {
            return false; // Free plan is owner-only
        }

        $currentCount = $company->users()->count();

        // Check team member limit for Pro plan
        if ($this->hasReachedLimit($company, SubscriptionFeature::MAX_TEAM_MEMBERS_3, $currentCount)) {
            return false;
        }

        return true;
    }

    /**
     * Check contract template limits
     */
    public function canCreateTemplate(Company $company): bool
    {
        $currentCount = $company->contractTemplates()->count();

        // Check template limit for Pro plan
        if ($this->hasReachedLimit($company, SubscriptionFeature::MAX_TEMPLATES_5, $currentCount)) {
            return false;
        }

        // All plans can create templates, but with different limits
        return $this->hasFeature($company, SubscriptionFeature::BASIC_TEMPLATES) ||
               $this->hasFeature($company, SubscriptionFeature::UNLIMITED_TEMPLATES);
    }

    /**
     * Get usage summary for a company
     */
    public function getUsageSummary(Company $company): array
    {
        $features = $this->getCompanyFeatures($company);
        $billboardCount = $company->billboards()->count();
        $contractCount = $company->contracts()->where('status', 'active')->count();
        $teamMemberCount = $company->users()->count();
        $templateCount = $company->contractTemplates()->count();

        return [
            'plan' => $company->subscription_plan ?? 'free',
            'features' => $features->toArray(),
            'usage' => [
                'billboards' => [
                    'current' => $billboardCount,
                    'limit' => $this->getBillboardLimit($company),
                    'can_create' => $this->canCreateBillboard($company),
                ],
                'contracts' => [
                    'current' => $contractCount,
                    'limit' => $this->getContractLimit($company),
                    'can_create' => $this->canCreateContract($company),
                ],
                'team_members' => [
                    'current' => $teamMemberCount,
                    'limit' => $this->getTeamMemberLimit($company),
                    'can_invite' => $this->canInviteTeamMember($company),
                ],
                'templates' => [
                    'current' => $templateCount,
                    'limit' => $this->getTemplateLimit($company),
                    'can_create' => $this->canCreateTemplate($company),
                ],
            ]
        ];
    }

    private function getBillboardLimit(Company $company): ?int
    {
        if ($this->hasFeature($company, SubscriptionFeature::UNLIMITED_BILLBOARDS)) {
            return null; // Unlimited
        }
        
        return $this->getFeatureLimit($company, SubscriptionFeature::MAX_BILLBOARDS_25) ?? 
               $this->getFeatureLimit($company, SubscriptionFeature::MAX_BILLBOARDS_5);
    }

    private function getContractLimit(Company $company): ?int
    {
        if ($this->hasFeature($company, SubscriptionFeature::UNLIMITED_CONTRACTS)) {
            return null; // Unlimited
        }
        
        return $this->getFeatureLimit($company, SubscriptionFeature::MAX_CONTRACTS_15) ?? 
               $this->getFeatureLimit($company, SubscriptionFeature::MAX_CONTRACTS_2);
    }

    private function getTeamMemberLimit(Company $company): ?int
    {
        if ($this->hasFeature($company, SubscriptionFeature::UNLIMITED_TEAM_MEMBERS)) {
            return null; // Unlimited
        }
        
        return $this->getFeatureLimit($company, SubscriptionFeature::MAX_TEAM_MEMBERS_3) ?? 1; // Owner only
    }

    private function getTemplateLimit(Company $company): ?int
    {
        if ($this->hasFeature($company, SubscriptionFeature::UNLIMITED_TEMPLATES)) {
            return null; // Unlimited
        }
        
        return $this->getFeatureLimit($company, SubscriptionFeature::MAX_TEMPLATES_5);
    }
}e App\Services;

class SubscriptionFeatureService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
