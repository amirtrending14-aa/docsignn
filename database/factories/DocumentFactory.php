<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['order', 'contract', 'statement']),
            'status' => 'active',
            'created_by' => \App\Models\User::inRandomOrder()->first()->id,
             'receiver_id' => \App\Models\User::inRandomOrder()->first()->id,
            'deadline' => now()->addDays(7),
        ];
    }
}
