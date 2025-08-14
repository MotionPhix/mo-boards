<?php

namespace Database\Factories;

use App\Models\Billboard;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillboardFactory extends Factory
{
    protected $model = Billboard::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->streetName(),
            'code' => strtoupper($this->faker->unique()->bothify('BB-####-??')),
            'location' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'width' => $this->faker->randomFloat(2, 5, 50),
            'height' => $this->faker->randomFloat(2, 5, 50),
            'monthly_rate' => $this->faker->randomFloat(2, 100, 5000),
            'status' => $this->faker->randomElement(['active', 'available', 'maintenance', 'removed']),
            'description' => $this->faker->sentence(),
        ];
    }
}
