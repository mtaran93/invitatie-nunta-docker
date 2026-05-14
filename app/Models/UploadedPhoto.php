<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedPhoto extends Model
{
    protected $fillable = [
        'original_name',
        'stored_name',
        'disk',
        'path',
        'mime',
        'size',
        'width',
        'height',
        'ip',
        'user_agent',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'status' => 'string',
        ];
    }
}
