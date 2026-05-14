<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected static function booted(): void
    {
        static::deleting(function (UploadedPhoto $photo): void {
            $disk = Storage::disk($photo->disk);

            if ($photo->path && $disk->exists($photo->path)) {
                $disk->delete($photo->path);
            }

            $thumb = 'photos/thumbs/'.$photo->stored_name;
            if ($disk->exists($thumb)) {
                $disk->delete($thumb);
            }
        });
    }
}
