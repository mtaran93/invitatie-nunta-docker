<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrittenGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'answer',
        'persons',
        'children',
    ];
}
