<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TelegramConversation extends Model
{
    protected $table = 'telegram_conversations';
    
    protected $fillable = ['type', 'name', 'avatar', 'company_id','created_by' ];

    protected $casts = [
        'type' => 'string',
    ];

  

   public function creator()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}

  public function company()
{
    return $this->belongsTo(\App\Models\Company::class, 'company_id');
}
    public function users()
{
    return $this->belongsToMany(
        \App\Models\User::class,
        'telegram_conversation_user',
        'telegram_conversation_id',
        'user_id'
    )->withPivot('last_read_at')->withTimestamps();
}

public function messages()
{
    return $this->hasMany(\App\Models\TelegramMessage::class, 'telegram_conversation_id');
}

public function lastMessage()
{
    return $this->hasOne(\App\Models\TelegramMessage::class, 'telegram_conversation_id')->latestOfMany();
}
}