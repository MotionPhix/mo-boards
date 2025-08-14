<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'industry' => fake()->word(),
            'size' => fake()->randomElement(['1-10', '11-50', '51-200', '200+']),
            'address' => fake()->address(),
            'subscription_plan' => fake()->randomElement(['free', 'pro', 'business']),
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
