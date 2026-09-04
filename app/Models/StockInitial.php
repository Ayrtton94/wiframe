<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInitial extends Model
{
    protected $fillable = [
        'store_id',
        'warehouse_id',
        'kilos_initial',
        'metros_initial',
    ];

    protected $casts = [
        'kilos_initial' => 'decimal:3',
        'metros_initial' => 'decimal:3',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
