<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = ['user_id', 'filename', 'filepath', 'text_content', 'pdf_filepath' , 'detected_type',];
}