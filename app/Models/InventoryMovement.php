<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
   protected $table = 'inventory_movements';

    protected $fillable = [
        'warehouse_id',
        'store_id',
        'type',
        'unit',
        'quantity',
        'reason',
        'reference_type',
        'reference_id',
        'reference_code',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
