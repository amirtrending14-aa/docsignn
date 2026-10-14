<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    protected $fillable = ['user_id', 'title', 'doc_type'];

    public function messages()
    {
        return $this->hasMany(AiMessage::class);
    }
}