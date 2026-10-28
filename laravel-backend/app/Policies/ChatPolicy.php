<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatPolicy
{
    public function sendMessage(User $user, Chat $chat): bool
    {
        return $chat->users()->where('user_id', $user->id)->exists();
    }
    public function join(User $user, Chat $chat): bool
    {
        // Example: allow joining public group chats only
        return $chat->type === 'group';
    }



    public function view(User $user, Chat $chat): bool
    {
        return $chat->users()->where('user_id', $user->id)->exists();
    }
    public function addUser(User $user, Chat $chat): bool
    {
        return $chat->users()
            ->wherePivot('role', 'admin')
            ->where('user_id', $user->id)
            ->exists();
    }


}
