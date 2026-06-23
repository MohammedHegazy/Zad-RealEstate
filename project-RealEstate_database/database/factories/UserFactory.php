<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'fname' => fake()->firstName(),
            'lname' => fake()->lastName(),
            'status' => 'active',
            'type' => 'buyer',
            'is_verified' => false,
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->numerify('##########'),
            'country_code_phone' => '+966',
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['type' => 'admin']);
    }

    public function owner(): static
    {
        return $this->state(fn () => ['type' => 'owner']);
    }

    public function company(): static
    {
        return $this->state(fn () => ['type' => 'company']);
    }

    public function agent(): static
    {
        return $this->state(fn () => ['type' => 'agent']);
    }

    public function buyer(): static
    {
        return $this->state(fn () => ['type' => 'buyer']);
    }

    public function verified(): static
    {
        return $this->state(fn () => ['is_verified' => true]);
    }
}
