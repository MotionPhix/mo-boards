<?php

declare(strict_types=1);

use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery as M;

uses(RefreshDatabase::class);

// Test case is already configured for the Feature suite via tests/Pest.php

beforeEach(function () {
    Gate::before(fn () => true);
});

it('GET /billboards/create redirects when no current company', function () {
    $this->withoutMiddleware();

    $user = User::factory()->make();
    $this->actingAs($user);

    $this->get(route('billboards.create'))
        ->assertRedirect(route('companies.index'));
});

it('GET /billboards/create returns Inertia when company is set', function () {
    $this->withoutMiddleware();

    $user = User::factory()->make();
    $company = new Company(['id' => 1, 'name' => 'Acme', 'currency' => 'USD']);
    $user->setRelation('currentCompany', $company);

    $this->actingAs($user)
        ->get(route('billboards.create'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('billboards/Create')
            ->where('company.id', 1)
            ->where('company.name', 'Acme')
        );
});

it('GET /billboards/{billboard:uuid}/edit returns Inertia with resource and markers', function () {
    Gate::before(fn() => true);

    // Use factories to create real models in the test database
    $company = Company::factory()->create(['name' => 'Beta Co', 'currency' => 'USD']);
    $billboard = Billboard::factory()->create([
        'company_id' => $company->id,
        'uuid' => '11111111-1111-1111-1111-111111111111'
    ]);

    $user = User::factory()->create(['current_company_id' => $company->id]);
    $user->companies()->attach($company->id, ['is_owner' => true, 'joined_at' => now()]);

    $this->actingAs($user)
        ->get(route('billboards.edit', ['billboard' => $billboard->uuid]))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('billboards/Edit')
            ->has('billboard')
            ->has('nearby_markers')
        );
});
