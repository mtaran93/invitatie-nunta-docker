<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeddingTable extends Model
{
    protected $fillable = ['number', 'finished'];

    protected $casts = [
        'number' => 'integer',
        'finished' => 'boolean',
    ];

    public function guests(): HasMany
    {
        return $this->hasMany(Invitat::class);
    }
}
