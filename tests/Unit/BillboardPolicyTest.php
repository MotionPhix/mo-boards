<?php

use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
// No mocks needed; use a typed subclass to satisfy the policy signature

it('allows delete when user has permission and can access company', function () {
    $user = new class extends User {
        public bool $allow = true;
        public bool $access = true;
        public function can($ability, $arguments = []) { return $ability === 'billboards.delete' ? $this->allow : parent::can($ability, $arguments); }
        public function canAccessCompany($company): bool { return $this->access; }
    };

    $company = new Company(['id' => 1, 'name' => 'Acme']);
    $billboard = new Billboard(['company_id' => 1]);
    $billboard->setRelation('company', $company);

    $policy = app(\App\Policies\BillboardPolicy::class);
    expect($policy->delete($user, $billboard))->toBeTrue();
});

it('denies delete when user cannot access company even with permission', function () {
    $user = new class extends User {
        public bool $allow = true;
        public bool $access = false;
        public function can($ability, $arguments = []) { return $ability === 'billboards.delete' ? $this->allow : parent::can($ability, $arguments); }
        public function canAccessCompany($company): bool { return $this->access; }
    };

    $company = new Company(['id' => 2, 'name' => 'Other Co']);
    $billboard = new Billboard(['company_id' => 2]);
    $billboard->setRelation('company', $company);

    $policy = app(\App\Policies\BillboardPolicy::class);
    expect($policy->delete($user, $billboard))->toBeFalse();
});
