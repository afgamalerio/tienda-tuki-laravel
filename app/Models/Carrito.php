<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrito extends Model
{
    protected $fillable = [
        'session_id',
        'estado',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CarritoItem::class);
    }
}
