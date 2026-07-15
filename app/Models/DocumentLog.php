<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'description',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public static function write($documentId, $action, $description = null)
    {
        return self::create([
            'document_id' => $documentId,
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $description,
        ]);
    }
}
