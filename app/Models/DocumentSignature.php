<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSignature extends Model
{
    use HasFactory;
    protected $fillable = [
        'document_id', 'user_id', 'signature', 'signed_at', 'expires_at',  'verification_code', //
    ];

    protected $casts = [
        'signed_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($signature) {
            if (!$signature->expires_at && $signature->document) {
                $signature->expires_at = $signature->document->deadline;
            }
        });
    }

    public function document() { return $this->belongsTo(Document::class); }
    public function user() { return $this->belongsTo(User::class); }
}
