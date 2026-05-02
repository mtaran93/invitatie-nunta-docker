<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitat extends Model
{
    protected $table = 'invitati';

    protected $fillable = [
        'name',
        'person_number',
        'kid_number',
        'confirmed',
        'accommodation',
        'wedding_table_id',
    ];

    protected $casts = [
        'confirmed' => 'bool',
        'accommodation' => 'bool',
    ];

    public function weddingTable(): BelongsTo
    {
        return $this->belongsTo(WeddingTable::class);
    }
}
