<?php

use App\Http\Controllers\BillboardController;
use App\Http\Resources\BillboardResource;
use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response as InertiaResponse;
use Mockery as M;

it('create redirects when no current company', function () {
    $user = M::mock(User::class)->makePartial();
    $user->shouldReceive('can')->with('create', Billboard::class)->andReturn(true);
    $user->currentCompany = null;

    Auth::shouldReceive('user')->andReturn($user);

    $controller = app(BillboardController::class);
    $result = $controller->create();

    expect($result)->toBeInstanceOf(RedirectResponse::class);
});

it('create returns inertia when company exists', function () {
    $company = new Company(['id' => 1, 'name' => 'Acme']);

    $user = M::mock(User::class)->makePartial();
    $user->shouldReceive('can')->with('create', Billboard::class)->andReturn(true);
    $user->currentCompany = $company;

    Auth::shouldReceive('user')->andReturn($user);

    $controller = app(BillboardController::class);
    $result = $controller->create();

    expect($result)->toBeInstanceOf(InertiaResponse::class);
});

it('edit returns inertia with resource and markers', function () {
    $company = new Company(['id' => 1, 'name' => 'Acme']);
    // Real instance of Billboard to satisfy type-hint
    $billboard = new Billboard(['id' => 10]);
    $billboard->setRelation('company', $company);

    $controller = app(BillboardController::class);
    $result = $controller->edit($billboard);

    expect($result)->toBeInstanceOf(InertiaResponse::class);
});
