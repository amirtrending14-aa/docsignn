<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentWorkflow extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'step_order',
        'status'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
