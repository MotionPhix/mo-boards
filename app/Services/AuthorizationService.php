<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Services\Billing\PlanGate;
use App\Services\SubscriptionLimitService;

class AuthorizationService
{
  public function __construct(
    private SubscriptionLimitService $subscriptionLimitService
  ) {}

  /**
   * Get comprehensive user abilities for the current company
   */
  public function getUserAbilities(User $user): array
  {
    $currentCompany = $user->currentCompany;

    if (!$currentCompany) {
      return $this->getEmptyAbilities();
    }

    $planId = $currentCompany->subscription_plan ?? 'free';

    return [
      // Company Management
      'can_view_companies' => $user->can('companies.view_any'),
      'can_view_company' => $user->can('companies.view'),
      'can_create_companies' => $user->can('companies.create') && $this->canCreateMoreCompanies($user, $planId),
      'can_update_companies' => $user->can('companies.update'),
      'can_delete_companies' => $user->can('companies.delete'),
      'can_switch_companies' => $user->can('companies.switch'),
      'can_manage_company_settings' => $user->can('companies.manage_settings'),
      'can_view_company_billing' => $user->can('companies.view_billing'),
      'can_manage_company_billing' => $user->can('companies.manage_billing'),

      // Billboard Management (with subscription limits)
      'can_view_billboards' => $user->can('billboards.view_any'),
      'can_view_billboard' => $user->can('billboards.view'),
      'can_create_billboards' => $user->can('billboards.create') && $this->subscriptionLimitService->canCreateBillboard($currentCompany),
      'can_update_billboards' => $user->can('billboards.update'),
      'can_delete_billboards' => $user->can('billboards.delete'),
      'can_duplicate_billboards' => $user->can('billboards.duplicate') && $this->subscriptionLimitService->canCreateBillboard($currentCompany),
      'can_bulk_update_billboards' => $user->can('billboards.bulk_update') && PlanGate::allows($planId, 'bulk.operations', false),
      'can_upload_billboard_media' => $user->can('billboards.upload_media'),
      'can_manage_billboard_media' => $user->can('billboards.manage_media'),
      'can_view_billboard_analytics' => $user->can('billboards.view_analytics'),
      'can_export_billboard_data' => $user->can('billboards.export_data') && PlanGate::allows($planId, 'export.enabled', false),

      // Contract Management (with subscription limits)
      'can_view_contracts' => $user->can('contracts.view_any'),
      'can_view_contract' => $user->can('contracts.view'),
      'can_create_contracts' => $user->can('contracts.create') && $this->subscriptionLimitService->canCreateContract($currentCompany),
      'can_update_contracts' => $user->can('contracts.update'),
      'can_delete_contracts' => $user->can('contracts.delete'),
      'can_approve_contracts' => $user->can('contracts.approve'),
      'can_cancel_contracts' => $user->can('contracts.cancel'),
      'can_manage_contract_payments' => $user->can('contracts.manage_payments'),
      'can_view_contract_financial' => $user->can('contracts.view_financial'),

      // Contract Template Management (with subscription limits)
      'can_view_contract_templates' => $user->can('contract_templates.view_any'),
      'can_view_contract_template' => $user->can('contract_templates.view'),
      'can_create_contract_templates' => $user->can('contract_templates.create') && $this->subscriptionLimitService->canCreateTemplate($currentCompany),
      'can_update_contract_templates' => $user->can('contract_templates.update'),
      'can_delete_contract_templates' => $user->can('contract_templates.delete'),
      'can_duplicate_contract_templates' => $user->can('contract_templates.duplicate') && $this->subscriptionLimitService->canCreateTemplate($currentCompany),
      'can_create_premium_templates' => $user->can('contract_templates.create_premium'),
      'can_manage_template_categories' => $user->can('contract_templates.manage_categories'),

      // Team Management (with subscription limits)
      'can_view_team' => $user->can('team.view_any'),
      'can_view_team_member' => $user->can('team.view'),
      'can_invite_team_members' => $this->canInviteTeamMembers($user, $currentCompany),
      'can_update_team_roles' => $user->can('team.update_roles'),
      'can_update_team_permissions' => $user->can('team.update_permissions'),
      'can_remove_team_members' => $user->can('team.remove'),
      'can_manage_invitations' => $user->can('team.manage_invitations'),
      'can_view_team_activity' => $user->can('team.view_activity'),

      // User Management
      'can_update_own_profile' => $user->can('users.update_own_profile'),
      'can_update_any_profile' => $user->can('users.update_any_profile'),
      'can_change_password' => $user->can('users.change_password'),
      'can_manage_sessions' => $user->can('users.manage_sessions'),
      'can_view_user_activity' => $user->can('users.view_activity'),

      // Analytics & Reporting (with plan features)
      'can_view_analytics_dashboard' => $user->can('analytics.view_dashboard'),
      'can_view_billboard_analytics' => $user->can('analytics.view_billboard'),
      'can_view_contract_analytics' => $user->can('analytics.view_contract'),
      'can_view_financial_analytics' => $user->can('analytics.view_financial'),
      'can_export_reports' => $user->can('analytics.export_reports') && PlanGate::allows($planId, 'export.enabled', false),
      'can_view_advanced_analytics' => $user->can('analytics.view_advanced') && PlanGate::allows($planId, 'analytics.advanced', false),

      // Financial Management
      'can_view_revenue' => $user->can('finance.view_revenue'),
      'can_view_expenses' => $user->can('finance.view_expenses'),
      'can_manage_payments' => $user->can('finance.manage_payments'),
      'can_view_invoices' => $user->can('finance.view_invoices'),
      'can_create_invoices' => $user->can('finance.create_invoices'),
      'can_export_financial_data' => $user->can('finance.export_financial') && PlanGate::allows($planId, 'export.enabled', false),

      // Admin (for super admins only)
      'can_access_admin_panel' => $user->can('admin.access_panel'),
      'can_manage_system' => $user->can('admin.manage_system'),
      'can_view_system_logs' => $user->can('admin.view_logs'),
      'can_manage_permissions' => $user->can('admin.manage_permissions'),
      'can_manage_roles' => $user->can('admin.manage_roles'),

      // Role-based flags for UI logic
      'is_super_admin' => $user->hasRole('super_admin'),
      'is_company_owner' => $user->hasRole('company_owner'),
      'is_manager' => $user->hasRole('manager'),
      'is_editor' => $user->hasRole('editor'),
      'is_viewer' => $user->hasRole('viewer'),

      // Subscription plan features
      'has_api_access' => PlanGate::allows($planId, 'api.access', false),
      'has_priority_support' => PlanGate::allows($planId, 'support.priority', false),
      'has_phone_support' => PlanGate::allows($planId, 'support.phone', false),
      'has_email_notifications' => PlanGate::allows($planId, 'notifications.email', false),
      'has_sms_notifications' => PlanGate::allows($planId, 'notifications.sms', false),
      'has_realtime_notifications' => PlanGate::allows($planId, 'notifications.realtime', false),
      'has_bulk_operations' => PlanGate::allows($planId, 'bulk.operations', false),
      'has_export_enabled' => PlanGate::allows($planId, 'export.enabled', false),

      // Current plan info for UI
      'current_plan' => $planId,
    ];
  }

  /**
   * Get subscription and usage data
   */
  public function getSubscriptionData(User $user): ?array
  {
    $company = $user->currentCompany;
    if (!$company) {
      return null;
    }

    $planId = $company->subscription_plan ?? 'free';

    // Get usage summary from subscription service
    $usageSummary = $this->subscriptionLimitService->getUsageSummary($company);

    // Get team limits and calculations
    $teamLimit = PlanGate::limit($planId, 'team.members.max');
    $teamCount = $company->users()->count();
    $canInviteTeam = PlanGate::allows($planId, 'team.invitations', false);
    $canInviteMore = $canInviteTeam && ($teamLimit === null || $teamCount < (int) $teamLimit);

    // Get company creation limits
    $companiesOwnedCount = $user->companies()->wherePivot('is_owner', true)->count();
    $companyLimit = PlanGate::limit($planId, 'companies.max');

    return [
      'plan' => $planId,
      'usage' => $usageSummary,

      // Team-specific data for UI
      'team' => [
        'current' => $teamCount,
        'limit' => $teamLimit,
        'can_invite_more' => $canInviteMore,
        'can_invite' => $canInviteTeam,
      ],

      // Company creation data
      'companies' => [
        'current' => $companiesOwnedCount,
        'limit' => $companyLimit === null ? 'unlimited' : (string) $companyLimit,
        'can_create_more' => $this->canCreateMoreCompanies($user, $planId),
      ],

      // Feature flags for quick UI checks
      'features' => $usageSummary['features'] ?? [],
    ];
  }

  /**
   * Check if user can invite team members (combining role permission + subscription limits)
   */
  private function canInviteTeamMembers(User $user, Company $company): bool
  {
    // First check role permission
    if (!$user->can('team.invite')) {
      return false;
    }

    // Then check subscription limits
    return $this->subscriptionLimitService->canInviteTeamMember($company);
  }

  /**
   * Check if user can create more companies
   */
  private function canCreateMoreCompanies(User $user, string $planId): bool
  {
    $currentCompanyCount = $user->companies()->wherePivot('is_owner', true)->count();
    $companyLimit = PlanGate::limit($planId, 'companies.max');

    if ($companyLimit === '0' || $companyLimit === 0) {
      return false;
    }

    if ($companyLimit === null) { // null means unlimited
      return true;
    }

    return $currentCompanyCount < (int) $companyLimit;
  }

  /**
   * Get empty abilities array for users without a current company
   */
  private function getEmptyAbilities(): array
  {
    return array_fill_keys([
      'can_view_companies', 'can_view_company', 'can_create_companies', 'can_update_companies',
      'can_delete_companies', 'can_switch_companies', 'can_manage_company_settings',
      'can_view_company_billing', 'can_manage_company_billing',
      'can_view_billboards', 'can_view_billboard', 'can_create_billboards', 'can_update_billboards',
      'can_delete_billboards', 'can_duplicate_billboards', 'can_bulk_update_billboards',
      'can_upload_billboard_media', 'can_manage_billboard_media', 'can_view_billboard_analytics',
      'can_export_billboard_data', 'can_view_contracts', 'can_view_contract', 'can_create_contracts',
      'can_update_contracts', 'can_delete_contracts', 'can_approve_contracts', 'can_cancel_contracts',
      'can_manage_contract_payments', 'can_view_contract_financial', 'can_view_contract_templates',
      'can_view_contract_template', 'can_create_contract_templates', 'can_update_contract_templates',
      'can_delete_contract_templates', 'can_duplicate_contract_templates', 'can_create_premium_templates',
      'can_manage_template_categories', 'can_view_team', 'can_view_team_member', 'can_invite_team_members',
      'can_update_team_roles', 'can_update_team_permissions', 'can_remove_team_members',
      'can_manage_invitations', 'can_view_team_activity', 'can_update_own_profile', 'can_update_any_profile',
      'can_change_password', 'can_manage_sessions', 'can_view_user_activity', 'can_view_analytics_dashboard',
      'can_view_billboard_analytics', 'can_view_contract_analytics', 'can_view_financial_analytics',
      'can_export_reports', 'can_view_advanced_analytics', 'can_view_revenue', 'can_view_expenses',
      'can_manage_payments', 'can_view_invoices', 'can_create_invoices', 'can_export_financial_data',
      'can_access_admin_panel', 'can_manage_system', 'can_view_system_logs', 'can_manage_permissions',
      'can_manage_roles', 'is_super_admin', 'is_company_owner', 'is_manager', 'is_editor', 'is_viewer',
      'has_api_access', 'has_priority_support', 'has_phone_support', 'has_email_notifications',
      'has_sms_notifications', 'has_realtime_notifications', 'has_bulk_operations', 'has_export_enabled'
    ], false);
  }
}
