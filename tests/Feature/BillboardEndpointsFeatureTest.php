<?php

use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery as M;

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
    $this->withoutMiddleware();

    // Mock the billboards() relation chain to avoid DB
    $fakeRelation = M::mock(HasMany::class);
    $fakeRelation->shouldReceive('whereNotNull')->andReturnSelf();
    $fakeRelation->shouldReceive('where')->andReturnSelf();
    $fakeRelation->shouldReceive('select')->andReturnSelf();
    $fakeRelation->shouldReceive('limit')->andReturnSelf();
    $fakeRelation->shouldReceive('get')->andReturn(collect([]));

    $company = M::mock(Company::class)->makePartial();
    $company->id = 10;
    $company->name = 'Beta Co';
    $company->currency = 'USD';
    $company->shouldReceive('billboards')->andReturn($fakeRelation);

    $billboard = new class(['id' => 99, 'uuid' => '11111111-1111-1111-1111-111111111111', 'company_id' => 10]) extends Billboard {
        public function loadMissing($relations) { return $this; }
        public function loadCount($relations) { return $this; }
    };
    $billboard->setRelation('company', $company);

    // Bind route model to return our prepared instance
    app('router')->bind('billboard', fn () => $billboard);

    $user = User::factory()->make();
    $this->actingAs($user)
        ->get(route('billboards.edit', ['billboard' => $billboard->uuid]))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('billboards/Edit')
            ->has('billboard')
            ->has('nearby_markers')
        );
});
