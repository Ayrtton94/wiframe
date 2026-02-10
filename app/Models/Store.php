<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

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
        'description'
    ];
}
