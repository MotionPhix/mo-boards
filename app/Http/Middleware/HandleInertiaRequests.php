<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone ?? null,
                    'avatar' => $request->user()->avatar ?? null,
                    'current_company_id' => $request->user()->current_company_id ?? null,
                    'last_active_at' => $request->user()->last_active_at ?? null,
                    'theme_preference' => $request->user()->theme_preference ?? 'system',
                    'companies' => $request->user()->companies()->get()->map(function ($company) {
                        return [ // ->with('pivot')
                            'id' => $company->id,
                            'name' => $company->name,
                            'slug' => $company->slug,
                            'industry' => $company->industry,
                            'size' => $company->size,
                            'subscription_plan' => $company->subscription_plan,
                            'is_active' => $company->is_active,
                            'pivot' => [
                                'is_owner' => $company->pivot->is_owner,
                                'joined_at' => $company->pivot->joined_at,
                            ],
                        ];
                    }),
                    'current_company' => $request->user()->currentCompany ? [
                        'id' => $request->user()->currentCompany->id,
                        'name' => $request->user()->currentCompany->name,
                        'slug' => $request->user()->currentCompany->slug,
                        'industry' => $request->user()->currentCompany->industry,
                        'size' => $request->user()->currentCompany->size,
                        'subscription_plan' => $request->user()->currentCompany->subscription_plan,
                        'subscription_expires_at' => $request->user()->currentCompany->subscription_expires_at,
                        'is_active' => $request->user()->currentCompany->is_active,
                    ] : null,
                    'abilities' => $request->user() ? $this->getUserAbilities($request->user()) : [],
                ] : null,
            ],
            'theme' => [
            'current' => $request->cookie('theme', 'system'),
            'user_preference' => $request->user()?->theme_preference ?? null,
        ],
            'flash' => [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'warning' => $request->session()->get('warning'),
            'info' => $request->session()->get('info'),
        ],
            'ziggy' => [
            ...(new Ziggy)->toArray(),
            'location' => $request->url(),
        ],
        ];
    }

    /**
     * Get user abilities based on their permissions
     *
     * @param  \App\Models\User  $user
     * @return array<string, bool>
     */
    private function getUserAbilities($user): array
    {
        $currentCompany = $user->currentCompany;

        if (! $currentCompany) {
            return [];
        }

        return [
            // Company management abilities
            'can_view_companies' => $user->can('companies.view_any'),
            'can_view_company' => $user->can('companies.view'),
            'can_create_companies' => $user->can('companies.create'),
            'can_update_companies' => $user->can('companies.update'),
            'can_delete_companies' => $user->can('companies.delete'),
            'can_switch_companies' => $user->can('companies.switch'),
            'can_manage_company_settings' => $user->can('companies.manage_settings'),
            'can_view_company_billing' => $user->can('companies.view_billing'),
            'can_manage_company_billing' => $user->can('companies.manage_billing'),

            // Billboard management abilities
            'can_view_billboards' => $user->can('billboards.view_any'),
            'can_view_billboard' => $user->can('billboards.view'),
            'can_create_billboards' => $user->can('billboards.create'),
            'can_update_billboards' => $user->can('billboards.update'),
            'can_delete_billboards' => $user->can('billboards.delete'),
            'can_duplicate_billboards' => $user->can('billboards.duplicate'),
            'can_bulk_update_billboards' => $user->can('billboards.bulk_update'),
            'can_upload_billboard_media' => $user->can('billboards.upload_media'),
            'can_manage_billboard_media' => $user->can('billboards.manage_media'),
            'can_view_billboard_analytics' => $user->can('billboards.view_analytics'),
            'can_export_billboard_data' => $user->can('billboards.export_data'),

            // Contract management abilities
            'can_view_contracts' => $user->can('contracts.view_any'),
            'can_view_contract' => $user->can('contracts.view'),
            'can_create_contracts' => $user->can('contracts.create'),
            'can_update_contracts' => $user->can('contracts.update'),
            'can_delete_contracts' => $user->can('contracts.delete'),
            'can_approve_contracts' => $user->can('contracts.approve'),
            'can_cancel_contracts' => $user->can('contracts.cancel'),
            'can_manage_contract_payments' => $user->can('contracts.manage_payments'),
            'can_view_contract_financial' => $user->can('contracts.view_financial'),

            // Team/User management abilities
            'can_view_team' => $user->can('team.view_any'),
            'can_view_team_member' => $user->can('team.view'),
            'can_invite_team_members' => $user->can('team.invite'),
            'can_update_team_roles' => $user->can('team.update_roles'),
            'can_update_team_permissions' => $user->can('team.update_permissions'),
            'can_remove_team_members' => $user->can('team.remove'),
            'can_manage_invitations' => $user->can('team.manage_invitations'),
            'can_view_team_activity' => $user->can('team.view_activity'),

            // User profile abilities
            'can_update_own_profile' => $user->can('users.update_own_profile'),
            'can_update_any_profile' => $user->can('users.update_any_profile'),
            'can_change_password' => $user->can('users.change_password'),
            'can_manage_sessions' => $user->can('users.manage_sessions'),
            'can_view_user_activity' => $user->can('users.view_activity'),

            // Analytics and reporting abilities
            'can_view_analytics_dashboard' => $user->can('analytics.view_dashboard'),
            'can_view_billboard_analytics' => $user->can('analytics.view_billboard'),
            'can_view_contract_analytics' => $user->can('analytics.view_contract'),
            'can_view_financial_analytics' => $user->can('analytics.view_financial'),
            'can_export_reports' => $user->can('analytics.export_reports'),
            'can_view_advanced_analytics' => $user->can('analytics.view_advanced'),

            // Financial management abilities
            'can_view_revenue' => $user->can('finance.view_revenue'),
            'can_view_expenses' => $user->can('finance.view_expenses'),
            'can_manage_payments' => $user->can('finance.manage_payments'),
            'can_view_invoices' => $user->can('finance.view_invoices'),
            'can_create_invoices' => $user->can('finance.create_invoices'),
            'can_export_financial_data' => $user->can('finance.export_financial'),

            // System administration abilities (for super admins)
            'can_access_admin_panel' => $user->can('admin.access_panel'),
            'can_manage_system' => $user->can('admin.manage_system'),
            'can_view_system_logs' => $user->can('admin.view_logs'),
            'can_manage_permissions' => $user->can('admin.manage_permissions'),
            'can_manage_roles' => $user->can('admin.manage_roles'),

            // Contract template abilities
            'can_view_contract_templates' => $user->can('contract_templates.view_any'),
            'can_view_contract_template' => $user->can('contract_templates.view'),
            'can_create_contract_templates' => $user->can('contract_templates.create'),
            'can_update_contract_templates' => $user->can('contract_templates.update'),
            'can_delete_contract_templates' => $user->can('contract_templates.delete'),
            'can_duplicate_contract_templates' => $user->can('contract_templates.duplicate'),
            'can_create_premium_templates' => $user->can('contract_templates.create_premium'),
            'can_manage_template_categories' => $user->can('contract_templates.manage_categories'),

            // Role-based abilities for UI logic
            'is_super_admin' => $user->hasRole('super_admin'),
            'is_company_owner' => $user->hasRole('company_owner'),
            'is_manager' => $user->hasRole('manager'),
            'is_editor' => $user->hasRole('editor'),
            'is_viewer' => $user->hasRole('viewer'),
        ];
    }
}
