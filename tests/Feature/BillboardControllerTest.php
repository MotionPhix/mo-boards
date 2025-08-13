<?php

use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('create redirects when no current company', function () {
  Gate::before(fn() => true);

  $user = User::factory()->create(['current_company_id' => null]);

  $response = $this->actingAs($user)->get(route('billboards.create'));

  $response->assertRedirect(route('companies.index'));
});

it('create returns inertia when company exists', function () {
  Gate::before(fn() => true);

  $company = Company::factory()->create();
  $user = User::factory()->create(['current_company_id' => $company->id]);
  $user->companies()->attach($company->id, ['is_owner' => true, 'joined_at' => now()]);

  $response = $this->actingAs($user)->get(route('billboards.create'));

  $response->assertInertia(fn($page) => $page->component('billboards/Create'));
});

it('edit returns inertia with resource and markers', function () {
  Gate::before(fn() => true);

  $company = Company::factory()->create();
  $user = User::factory()->create(['current_company_id' => $company->id]);
  $user->companies()->attach($company->id, ['is_owner' => true, 'joined_at' => now()]);

  $billboard = Billboard::factory()->create(['company_id' => $company->id]);

  $response = $this->actingAs($user)->get(route('billboards.edit', $billboard));

  $response->assertInertia(fn($page) => $page->component('billboards/Edit'));
});
