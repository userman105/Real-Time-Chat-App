<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat;

class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true), // "Dev Team" / "Support Group"
            'is_group' => $this->faker->boolean(70), // 70% chance group chat
        ];
    }
}
