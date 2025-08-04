<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
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
  public function version(Request $request): string|null
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
   * @param \App\Models\User $user
   * @return array<string, bool>
   */
  private function getUserAbilities($user): array
  {
    $currentCompany = $user->currentCompany;

    if (!$currentCompany) {
      return [];
    }

    return [
      // Billboard abilities
      'can_view_billboards' => $user->can('billboards.view_any'),
      'can_create_billboards' => $user->can('billboards.create'),
      'can_update_billboards' => $user->can('billboards.update'),
      'can_delete_billboards' => $user->can('billboards.delete'),
      'can_duplicate_billboards' => $user->can('billboards.duplicate'),
      'can_export_billboard_data' => $user->can('billboards.export_data'),

      // Contract abilities
      'can_view_contracts' => $user->can('contracts.view_any'),
      'can_create_contracts' => $user->can('contracts.create'),
      'can_update_contracts' => $user->can('contracts.update'),
      'can_delete_contracts' => $user->can('contracts.delete'),
      'can_approve_contracts' => $user->can('contracts.approve'),
      'can_cancel_contracts' => $user->can('contracts.cancel'),

      // Team abilities
      'can_view_team' => $user->can('team.view_any'),
      'can_invite_team_members' => $user->can('team.invite'),
      'can_update_team_roles' => $user->can('team.update_roles'),
      'can_remove_team_members' => $user->can('team.remove'),
      'can_manage_invitations' => $user->can('team.manage_invitations'),

      // Company abilities
      'can_view_companies' => $user->can('companies.view_any'),
      'can_create_companies' => $user->can('companies.create'),
      'can_update_companies' => $user->can('companies.update'),
      'can_delete_companies' => $user->can('companies.delete'),
      'can_manage_company_settings' => $user->can('companies.manage_settings'),
      'can_switch_companies' => $user->can('companies.switch'),

      // Analytics abilities
      'can_view_analytics' => $user->can('analytics.view_dashboard'),
      'can_export_reports' => $user->can('analytics.export_reports'),

      // Financial abilities
      'can_view_financial_data' => $user->can('finance.view_revenue'),
      'can_manage_payments' => $user->can('finance.manage_payments'),
    ];
  }
}
