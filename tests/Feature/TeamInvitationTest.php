<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery as M;
use Spatie\Permission\Models\Permission;

// Lightweight in-memory user for actingAs and notifications, without loading App\Models\User
final class FakeUser implements Illuminate\Contracts\Auth\Authenticatable
{
    use Illuminate\Auth\Authenticatable, \Illuminate\Notifications\Notifiable;

    public $id = 1;

    public $email = 'owner@example.com';

    public $name = 'Owner';

    public $currentCompany;

    public function can($ability, $arguments = [])
    {
        return true;
    }
}

uses(RefreshDatabase::class);

beforeEach(function () {
    // Disable middleware to avoid DB-dependent company access checks
    $this->withoutMiddleware();

    // Allow any gate checks in controller
    Gate::before(fn () => true);

    // Ensure permissions referenced by Spatie's Gate::before exist to avoid exceptions
    Permission::firstOrCreate(['name' => 'inviteTeamMember', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'removeTeamMember', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'updateTeamMember', 'guard_name' => 'web']);
});

test('team owner can view the invitation form', function () {
    // Prepare owner and company without DB
    $owner = new FakeUser();
    $company = new Company(['id' => 1, 'name' => 'Test Co', 'currency' => 'USD']);
    $company = M::mock($company)->makePartial();

    // Stub company relations used in controller
    $fakeRelation = M::mock(BelongsToMany::class);
    $fakeRelation->shouldReceive('with')->andReturnSelf();
    $fakeRelation->shouldReceive('withPivot')->andReturnSelf();
    $fakeRelation->shouldReceive('orderBy')->andReturnSelf();
    $fakeRelation->shouldReceive('where')->andReturnSelf();
    $fakeRelation->shouldReceive('wherePivot')->andReturnSelf();
    $fakeRelation->shouldReceive('get')->andReturn(collect([]));
    $fakeRelation->shouldReceive('exists')->andReturn(false);

    $company->id = 1;
    $company->name = 'Test Co';
    $company->shouldReceive('users')->andReturn($fakeRelation);
    $fakeInvitations = M::mock(HasMany::class);
    $fakeInvitations->shouldReceive('where')->andReturnSelf();
    $fakeInvitations->shouldReceive('get')->andReturn(collect([]));
    $company->shouldReceive('invitations')->andReturn($fakeInvitations);

    // Attach company to user in-memory
    $owner->currentCompany = $company;

    $this->actingAs($owner);

    $response = $this->get(route('team.index'));
    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('team/Index')
        ->has('userPermissions', fn (Assert $page) => $page
            ->where('canInviteUsers', true)
            ->etc()
        )
    );
});

test('team owner can send invitation and email is sent', function () {
    // Seed the plan feature rules
    $this->seed(\Database\Seeders\PlanFeatureRulesSeeder::class);
    
    Notification::fake();

    // Real owner and company using DB
    $owner = User::factory()->create();
    $company = Company::factory()->create(['subscription_plan' => 'pro']); // Use pro plan
    $owner->forceFill(['current_company_id' => $company->id])->save();
    $owner->companies()->attach($company->id, [
        'is_owner' => true,
        'joined_at' => now(),
    ]);

    $this->actingAs($owner);

    $response = $this->post(route('team.invite'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'editor',
    ]);

    $response->assertRedirect(route('team.index'));
    $response->assertSessionHas('success', 'Invitation has been sent successfully.');

    Notification::assertSentTo(
        [new Illuminate\Notifications\AnonymousNotifiable],
        TeamInvitationNotification::class,
        function ($notification, $channels, $notifiable) {
            return ($notifiable->routes['mail'] ?? null) === 'john@example.com';
        }
    );
});

test('existing user receives invitation email', function () {
    // Seed the plan feature rules
    $this->seed(\Database\Seeders\PlanFeatureRulesSeeder::class);
    
    Notification::fake();

    // Real owner/company and existing user in DB
    $owner = User::factory()->create();
    $company = Company::factory()->create(['subscription_plan' => 'pro']); // Use pro plan
    $owner->forceFill(['current_company_id' => $company->id])->save();
    $owner->companies()->attach($company->id, [
        'is_owner' => true,
        'joined_at' => now(),
    ]);

    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($owner);

    $response = $this->post(route('team.invite'), [
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'role' => 'editor',
    ]);

    $response->assertRedirect(route('team.index'));

    Notification::assertSentTo(
        $existingUser,
        TeamInvitationNotification::class
    );
});

test('user cannot be invited to a company they already belong to', function () {
    // Seed the plan feature rules
    $this->seed(\Database\Seeders\PlanFeatureRulesSeeder::class);
    
    // Real owner/company and existing member already attached
    $owner = User::factory()->create();
    $company = Company::factory()->create(['subscription_plan' => 'pro']); // Use pro plan
    $owner->forceFill(['current_company_id' => $company->id])->save();
    $owner->companies()->attach($company->id, [
        'is_owner' => true,
        'joined_at' => now(),
    ]);

    $existingMember = User::factory()->create(['email' => 'member@example.com', 'name' => 'Member']);
    $company->users()->attach($existingMember->id, [
        'is_owner' => false,
        'joined_at' => now(),
    ]);

    $this->actingAs($owner);

    $response = $this->post(route('team.invite'), [
        'name' => 'Member',
        'email' => 'member@example.com',
        'role' => 'editor',
    ]);

    $response->assertRedirect(route('team.index'));
    $response->assertSessionHas('error', 'This user is already a member of your team.');
});
