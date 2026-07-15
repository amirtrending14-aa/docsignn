<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'body', 'is_read'];

    // Сообщение принадлежит отправителю
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Сообщение принадлежит получателю
    public function receiver() {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
