<?php

namespace App\Enums;

enum SubscriptionFeature: string
{
    // Core Features
    case BASIC_BILLBOARDS = 'basic_billboards';
    case UNLIMITED_BILLBOARDS = 'unlimited_billboards';
    case BASIC_CONTRACTS = 'basic_contracts';
    case UNLIMITED_CONTRACTS = 'unlimited_contracts';
    
    // User Management
    case OWNER_ONLY = 'owner_only';
    case TEAM_INVITATIONS = 'team_invitations';
    case UNLIMITED_TEAM_MEMBERS = 'unlimited_team_members';
    
    // Contract Templates
    case BASIC_TEMPLATES = 'basic_templates';
    case UNLIMITED_TEMPLATES = 'unlimited_templates';
    
    // Reporting & Analytics
    case BASIC_REPORTING = 'basic_reporting';
    case ADVANCED_ANALYTICS = 'advanced_analytics';
    case ADVANCED_INSIGHTS = 'advanced_insights';
    
    // Support
    case EMAIL_SUPPORT = 'email_support';
    case PRIORITY_SUPPORT = 'priority_support';
    case PHONE_SUPPORT = 'phone_support';
    
    // Notifications
    case EMAIL_NOTIFICATIONS = 'email_notifications';
    case SMS_NOTIFICATIONS = 'sms_notifications';
    case REAL_TIME_NOTIFICATIONS = 'real_time_notifications';
    
    // Advanced Features
    case API_ACCESS = 'api_access';
    case EXPORT_FUNCTIONALITY = 'export_functionality';
    case BULK_OPERATIONS = 'bulk_operations';
    
    // Plan-specific limits
    case MAX_BILLBOARDS_5 = 'max_billboards_5';
    case MAX_BILLBOARDS_25 = 'max_billboards_25';
    case MAX_CONTRACTS_2 = 'max_contracts_2';
    case MAX_CONTRACTS_15 = 'max_contracts_15';
    case MAX_TEAM_MEMBERS_3 = 'max_team_members_3';
    case MAX_TEMPLATES_5 = 'max_templates_5';

    public function getDescription(): string
    {
        return match($this) {
            self::BASIC_BILLBOARDS => 'Basic billboard management',
            self::UNLIMITED_BILLBOARDS => 'Unlimited billboards',
            self::BASIC_CONTRACTS => 'Basic contract management',
            self::UNLIMITED_CONTRACTS => 'Unlimited contracts',
            
            self::OWNER_ONLY => 'Owner-only access',
            self::TEAM_INVITATIONS => 'Team member invitations',
            self::UNLIMITED_TEAM_MEMBERS => 'Unlimited team members',
            
            self::BASIC_TEMPLATES => 'Basic contract templates',
            self::UNLIMITED_TEMPLATES => 'Unlimited contract templates',
            
            self::BASIC_REPORTING => 'Basic reporting',
            self::ADVANCED_ANALYTICS => 'Advanced analytics',
            self::ADVANCED_INSIGHTS => 'Advanced business insights',
            
            self::EMAIL_SUPPORT => 'Email support',
            self::PRIORITY_SUPPORT => 'Priority support',
            self::PHONE_SUPPORT => 'Phone support',
            
            self::EMAIL_NOTIFICATIONS => 'Email notifications',
            self::SMS_NOTIFICATIONS => 'SMS notifications',
            self::REAL_TIME_NOTIFICATIONS => 'Real-time notifications',
            
            self::API_ACCESS => 'API access',
            self::EXPORT_FUNCTIONALITY => 'Data export functionality',
            self::BULK_OPERATIONS => 'Bulk operations',
            
            self::MAX_BILLBOARDS_5 => 'Maximum 5 billboards',
            self::MAX_BILLBOARDS_25 => 'Maximum 25 billboards',
            self::MAX_CONTRACTS_2 => 'Maximum 2 active contracts',
            self::MAX_CONTRACTS_15 => 'Maximum 15 active contracts',
            self::MAX_TEAM_MEMBERS_3 => 'Maximum 3 team members',
            self::MAX_TEMPLATES_5 => 'Maximum 5 contract templates',
        };
    }

    public function getLimit(): ?int
    {
        return match($this) {
            self::MAX_BILLBOARDS_5 => 5,
            self::MAX_BILLBOARDS_25 => 25,
            self::MAX_CONTRACTS_2 => 2,
            self::MAX_CONTRACTS_15 => 15,
            self::MAX_TEAM_MEMBERS_3 => 3,
            self::MAX_TEMPLATES_5 => 5,
            default => null,
        };
    }
}amespace App\Enums;

enum SubscriptionFeature
{
    //
}
