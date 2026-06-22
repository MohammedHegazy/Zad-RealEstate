<?php

namespace Database\Factories;

use App\Models\Notifications;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notifications>
 */
class NotificationsFactory extends Factory
{
    protected $model = Notifications::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => fake()->sentence(),
            'is_read' => false,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => ['is_read' => true]);
    }
}
