<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    use HasFactory;

    protected $table = 'messages_read'; // explicitly set since it's not plural

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];

    /**
     * A read receipt belongs to a message.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * A read receipt belongs to a user (the reader).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
