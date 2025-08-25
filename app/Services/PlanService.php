<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionFeature;
use App\Enums\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PlanService
{
    private const CACHE_KEY = 'subscription_plans';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Generate human-readable feature text from key and value.
     */
    private function generateFeatureText(string $key, string $value): string
    {
        // Clean up the key for display
        $text = Str::of($key)
            ->replace('.', ' ')
            ->replace('_', ' ')
            ->title()
            ->toString();

        // Remove redundant words
        $text = str_replace(' Max', '', $text);

        // Handle different value types
        if ($value === '1') {
            return $text;
        }

        if ($value === '0') {
            return "No $text";
        }

        if (is_numeric($value)) {
            return "$value $text";
        }

        if ($value === 'unlimited') {
            return "Unlimited $text";
        }

        return "$text: " . Str::title($value);
    }

    /**
     * Get all available subscription plans with their features.
     */
    public function getPlans(): array
    {
        // Load rules grouped by plan id
        $rules = DB::table('plan_feature_rules')
            ->select('plan_id', 'key', 'value')
            ->get()
            ->groupBy('plan_id');

        // Load billing plans for names/prices
        $billingPlans = DB::table('billing_plans')
            ->whereIn('key', array_keys($rules->toArray()))
            ->orWhereIn('key', ['free', 'pro', 'business'])
            ->get()
            ->keyBy('key');

        $plans = [];
        foreach ($rules as $planId => $planRules) {
            $displayFeatures = [];

            foreach ($planRules as $rule) {
                $displayFeatures[] = $this->generateFeatureText($rule->key, $rule->value);
            }

            $bp = $billingPlans->get($planId);
            $plans[] = [
                'name' => $planId,
                'displayName' => $bp->name ?? Str::title($planId),
                'price' => isset($bp->price) ? (float) $bp->price : 0.0,
                'currency' => $bp->currency ?? 'USD',
                'interval' => $bp->interval ?? 'month',
                'interval_count' => isset($bp->interval_count) ? (int) $bp->interval_count : 1,
                'features' => array_values(array_unique($displayFeatures)),
                'recommended' => $planId === 'pro',
            ];
        }

        // Ensure all three plans exist even if no rules yet
        foreach (['free' => 'Starter', 'pro' => 'Professional', 'business' => 'Enterprise'] as $key => $fallbackName) {
            if (!collect($plans)->firstWhere('name', $key)) {
                $bp = $billingPlans->get($key);
                $plans[] = [
                    'name' => $key,
                    'displayName' => $bp->name ?? $fallbackName,
                    'price' => isset($bp->price) ? (float) $bp->price : 0.0,
                    'currency' => $bp->currency ?? 'USD',
                    'interval' => $bp->interval ?? 'month',
                    'interval_count' => isset($bp->interval_count) ? (int) $bp->interval_count : 1,
                    'features' => [],
                    'recommended' => $key === 'pro',
                ];
            }
        }

        // Order plans
        usort($plans, function ($a, $b) {
            $order = ['free' => 0, 'pro' => 1, 'business' => 2];
            return ($order[$a['name']] ?? 99) <=> ($order[$b['name']] ?? 99);
        });

        return $plans;
    }

    /**
     * Get features for a specific plan.
     */
    public function getPlanFeatures(SubscriptionPlan $plan): array
    {
        $plans = $this->getPlans();
        $planFeatures = collect($plans)->firstWhere('name', $plan->value);
        return $planFeatures['features'] ?? [];
    }

    /**
     * Check if a plan has a specific feature.
     */
    public function hasPlanFeature(SubscriptionPlan $plan, SubscriptionFeature $feature): bool
    {
        $rules = DB::table('plan_feature_rules')
            ->where('plan_id', $plan->value)
            ->where('key', 'like', $feature->value . '%')
            ->first();

        return match ($feature) {
            SubscriptionFeature::BASIC_BILLBOARDS => true,
            SubscriptionFeature::UNLIMITED_BILLBOARDS => $rules?->value === 'unlimited',
            default => $rules?->value === '1' || $rules?->value === 'true',
        };
    }

    /**
     * Get the maximum number of billboards allowed for a plan.
     */
    public function getMaxBillboards(SubscriptionPlan $plan): int|string
    {
        $rule = DB::table('plan_feature_rules')
            ->where('plan_id', $plan->value)
            ->where('key', 'billboards.max')
            ->first();

        if (!$rule) {
            return 0;
        }

        return $rule->value === 'unlimited' ? 'unlimited' : (int) $rule->value;
    }

    /**
     * Get the maximum number of contracts allowed for a plan.
     */
    public function getMaxContracts(SubscriptionPlan $plan): int|string
    {
        $rule = DB::table('plan_feature_rules')
            ->where('plan_id', $plan->value)
            ->where('key', 'contracts.max')
            ->first();

        if (!$rule) {
            return 0;
        }

        return $rule->value === 'unlimited' ? 'unlimited' : (int) $rule->value;
    }

    /**
     * Get the maximum number of team members allowed for a plan.
     */
    public function getMaxTeamMembers(SubscriptionPlan $plan): int|string
    {
        $rule = DB::table('plan_feature_rules')
            ->where('plan_id', $plan->value)
            ->where('key', 'team.members.max')
            ->first();

        if (!$rule) {
            return 1; // Default to 1 team member
        }

        return $rule->value === 'unlimited' ? 'unlimited' : (int) $rule->value;
    }
}
