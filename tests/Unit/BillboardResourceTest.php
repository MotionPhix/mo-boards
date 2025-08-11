<?php

use App\Http\Resources\BillboardResource;
use App\Models\Billboard;
use App\Models\Company;
use Illuminate\Support\Collection;

uses(Tests\TestCase::class);

it('does not break delete permission when active_contracts_count is missing', function () {
    // In-memory models (no DB): billboard without eager-loaded count
    $company = new Company(['id' => 1, 'name' => 'Acme', 'currency' => 'USD']);
    $billboard = new Billboard([
        'id' => 123,
        'company_id' => 1,
        'name' => 'Main St',
        'code' => 'BB-0001',
        'status' => 'available',
        'monthly_rate' => 1000,
    ]);
    $billboard->setRelation('company', $company);
    // Simulate no contracts relation loaded
    $billboard->setRelation('contracts', new Collection());

    $resource = new BillboardResource($billboard);
    $arr = $resource->toArray(request());

    expect($arr['actions']['can_delete'])->toBeTrue();
});

it('respects active_contracts_count alias when present', function () {
    $company = new Company(['id' => 2, 'name' => 'Beta', 'currency' => 'USD']);
    $billboard = new Billboard([
        'id' => 456,
        'company_id' => 2,
        'name' => 'Broadway',
        'code' => 'BB-0002',
        'status' => 'available',
        'monthly_rate' => 1500,
    ]);
    $billboard->setRelation('company', $company);
    // Emulate preloaded alias via attribute
    $billboard->setRawAttributes(array_merge($billboard->getAttributes(), [
        'active_contracts_count' => 0,
    ]));
    $billboard->setRelation('contracts', new Collection());

    $resource = new BillboardResource($billboard);
    $arr = $resource->toArray(request());

    expect($arr['contracts']['active_count'])->toBeInt();
    expect($arr['actions']['can_delete'])->toBeTrue();
});
