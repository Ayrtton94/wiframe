<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code_product',
        'name_product',
        'fabric_type',
        'color',
        'proveedor',
        'kilos',
        'metros',
        'minimum_stock',
        'price',
        'public_price',
        'wholesale_price',
        'price_roll',
        'special_price',
        'location',
        'description',
    ];

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }
}
