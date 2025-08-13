<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlanGate
{
    /**
     * Check a boolean feature flag for the given plan.
     */
    public static function allows(string $planId, string $key, bool $default = false): bool
    {
        $value = self::get($planId, $key);
        if ($value === null) return $default;
        return in_array(strtolower($value), ['1','true','yes','on'], true);
    }

    /**
     * Check a numeric limit for the given plan.
     */
    public static function limit(string $planId, string $key, ?int $default = null): ?int
    {
        $value = self::get($planId, $key);
        if ($value === null) return $default;
        if (strtolower($value) === 'unlimited') return null;
        return (int) $value;
    }

    /**
     * Get raw string value for a feature key.
     */
    public static function get(string $planId, string $key): ?string
    {
        $cacheKey = "plan_feature_rules:{$planId}:{$key}";
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($planId, $key) {
            return DB::table('plan_feature_rules')
                ->where('plan_id', $planId)
                ->where('key', $key)
                ->value('value');
        });
    }
}
