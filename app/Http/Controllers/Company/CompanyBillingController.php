<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BillingPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class CompanyBillingController extends Controller
{
    public function index(): Response
    {
        $company = Auth::user()->currentCompany;
        $this->authorize('viewBilling', $company);

        $plans = BillingPlan::query()->where('active', true)->get()->map(function ($p) {
            $dbFeatures = DB::table('plan_feature_rules')
                ->where('plan_id', $p->key)
                ->pluck('value', 'key')
                ->toArray();
            $featuresList = [];
            if (! empty($dbFeatures)) {
                $labels = [
                    'billboards.max' => fn ($v) => ($v === 'unlimited' ? 'Unlimited billboards' : ($v.' billboards')),
                    'contracts.max' => fn ($v) => ($v === 'unlimited' ? 'Unlimited contracts' : ($v.' contracts')),
                    'team.members.max' => fn ($v) => ($v === 'unlimited' ? 'Unlimited team members' : ('Up to '.$v.' team members')),
                    'analytics.access' => fn ($v) => (in_array(mb_strtolower($v), ['1', 'true', 'yes', 'on'], true) ? 'Analytics access' : null),
                    'export.enabled' => fn ($v) => (in_array(mb_strtolower($v), ['1', 'true', 'yes', 'on'], true) ? 'Export data' : null),
                    'priority.support' => fn ($v) => (in_array(mb_strtolower($v), ['1', 'true', 'yes', 'on'], true) ? 'Priority support' : null),
                ];
                foreach ($labels as $k => $fmt) {
                    if (array_key_exists($k, $dbFeatures)) {
                        $label = $fmt($dbFeatures[$k]);
                        if ($label) {
                            $featuresList[] = $label;
                        }
                    }
                }
            } else {
                $featuresList = $p->features ?? [];
            }

            return [
                'id' => $p->key,
                'name' => $p->name,
                'price' => $p->price,
                'currency' => $p->currency,
                'interval' => $p->interval,
                'interval_count' => $p->interval_count,
                'features' => $featuresList,
            ];
        });

        $transactions = $company->transactions()->latest('occurred_at')->limit(20)->get();

        return Inertia::render('companies/settings/Billing', [
            'company' => $company,
            'plans' => $plans,
            'currentPlan' => $company->subscription_plan ?? 'free',
            'transactions' => $transactions,
            'can' => [
                'manage_billing' => Auth::user()->can('companies.manage_billing'),
            ],
        ]);
    }
}
