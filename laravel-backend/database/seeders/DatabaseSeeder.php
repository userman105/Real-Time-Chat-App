<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create 10 users
        $users = User::factory(10)->create();

        // Create 5 chats
        $chats = Chat::factory(5)->create();

        // Attach users to chats
        foreach ($chats as $chat) {
            $members = $users->random(rand(2, 6)); //ي 2–6 members per chat
            foreach ($members as $member) {
                $chat->users()->attach($member->id, [
                    'role' => $member->id === $members->first()->id ? 'admin' : 'member'
                ]);
            }

            // Add 10 messages per chat
            Message::factory(10)->create([
                'chat_id' => $chat->id,
                'user_id' => $members->random()->id,
            ]);
        }
    }
}
