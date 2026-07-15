<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_id', 'user_id', 'version', 'file_path',
        'original_name', 'extension', 'file_size', 'change_summary'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
