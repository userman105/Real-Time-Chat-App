<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ChatUser;
use App\Models\User;
use App\Models\Chat;

class ChatUserFactory extends Factory
{
    protected $model = ChatUser::class;

    public function definition()
    {
        return [
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
            'role' => $this->faker->randomElement(['admin', 'member']),
        ];
    }
}
