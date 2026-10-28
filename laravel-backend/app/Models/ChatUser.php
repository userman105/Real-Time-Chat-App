<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $role
 */
class ChatUser extends Pivot
{
    protected $table = 'chat_user';

    protected $fillable = [
        'chat_id',
        'user_id',
        'role',
    ];

    /*
    |--------------------------------------------------------------------------
    | Booted Event Listeners
    |--------------------------------------------------------------------------
    |
    | Handles logic when a user leaves a chat:
    | - If an admin leaves and no admins remain:
    |   → Promote the earliest member to admin, OR
    |   → Delete the chat if no members left.
    |
    */
    protected static function booted()
    {
        static::deleting(function (ChatUser $pivot) {
            $chat = $pivot->chat;

            // Only run if the leaving user is an admin
            if ($pivot->role === 'admin') {
                $remainingAdmins = $chat->users()->wherePivot('role', 'admin')->count();

                if ($remainingAdmins === 0) {
                    $nextMember = $chat->users()
                        ->wherePivot('role', 'member')
                        ->orderBy('chat_user.created_at') // earliest join
                        ->first();

                    if ($nextMember) {
                        $chat->users()->updateExistingPivot($nextMember->id, ['role' => 'admin']);
                    } else {
                        $chat->delete();
                    }
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }
}
