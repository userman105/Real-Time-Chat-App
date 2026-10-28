<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // private, group
    ];

    // A chat has many users
    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_user')
            ->using(ChatUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }


    //  A chat has many messages
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
