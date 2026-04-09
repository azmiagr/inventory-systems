<?php

namespace Database\Factories;

use App\Constants\RoleConstants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'role_id' => RoleConstants::STAFF,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role_id' => RoleConstants::ADMIN]);
    }

    public function staff(): static
    {
        return $this->state(['role_id' => RoleConstants::STAFF]);
    }

    public function viewer(): static
    {
        return $this->state(['role_id' => RoleConstants::VIEWER]);
    }
}
