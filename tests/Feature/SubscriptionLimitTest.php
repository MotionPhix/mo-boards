<?php

use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
use App\Services\SubscriptionLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('free plan has correct billboard limits', function () {
    Gate::before(fn() => true);

    // Seed the plan feature rules
    $this->seed(\Database\Seeders\PlanFeatureRulesSeeder::class);

    $company = Company::factory()->create(['subscription_plan' => 'free']);
    $service = new SubscriptionLimitService();

    // Free plan should allow up to 5 billboards
    expect($service->canCreateBillboard($company))->toBeTrue();

    // Create 5 billboards
    Billboard::factory()->count(5)->create(['company_id' => $company->id]);

    // Should not be able to create more
    expect($service->canCreateBillboard($company))->toBeFalse();

    $usage = $service->getUsageSummary($company);
    expect($usage['billboards']['current'])->toBe(5);
    expect($usage['billboards']['limit'])->toBe(5);
    expect($usage['billboards']['can_create'])->toBeFalse();
});

it('pro plan has correct billboard limits', function () {
    Gate::before(fn() => true);

    // Seed the plan feature rules
    $this->seed(\Database\Seeders\PlanFeatureRulesSeeder::class);

    $company = Company::factory()->create(['subscription_plan' => 'pro']);
    $service = new SubscriptionLimitService();

    // Pro plan should allow up to 25 billboards
    expect($service->canCreateBillboard($company))->toBeTrue();

    // Create 25 billboards
    Billboard::factory()->count(25)->create(['company_id' => $company->id]);

    // Should not be able to create more
    expect($service->canCreateBillboard($company))->toBeFalse();

    $usage = $service->getUsageSummary($company);
    expect($usage['billboards']['current'])->toBe(25);
    expect($usage['billboards']['limit'])->toBe(25);
    expect($usage['billboards']['can_create'])->toBeFalse();
});

it('business plan has unlimited billboards', function () {
    Gate::before(fn() => true);

    // Seed the plan feature rules
    $this->seed(\Database\Seeders\PlanFeatureRulesSeeder::class);

    $company = Company::factory()->create(['subscription_plan' => 'business']);
    $service = new SubscriptionLimitService();

    // Business plan should have unlimited billboards
    expect($service->canCreateBillboard($company))->toBeTrue();

    // Create 100 billboards (way more than other plans)
    Billboard::factory()->count(100)->create(['company_id' => $company->id]);

    // Should still be able to create more
    expect($service->canCreateBillboard($company))->toBeTrue();

    $usage = $service->getUsageSummary($company);
    expect($usage['billboards']['current'])->toBe(100);
    expect($usage['billboards']['limit'])->toBeNull(); // Unlimited
    expect($usage['billboards']['can_create'])->toBeTrue();
});

it('free plan team invitation restrictions work', function () {
    Gate::before(fn() => true);

    $company = Company::factory()->create(['subscription_plan' => 'free']);
    $user = User::factory()->create(['current_company_id' => $company->id]);
    $user->companies()->attach($company->id, ['is_owner' => true, 'joined_at' => now()]);

    $service = new SubscriptionLimitService();

    // Free plan should not allow team invitations
    expect($service->canInviteTeamMember($company))->toBeFalse();

    $usage = $service->getUsageSummary($company);
    expect($usage['features']['team_invitations'])->toBeFalse();
    expect($usage['team_members']['can_invite'])->toBeFalse();
});
