<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suppliers extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ruc',
        'company_name',
        'category',
        'phone',
        'email',
        'name',
        'address',
        'city',
        'country',
        'account',
        'cod_swift',
        'bank_name',
        'bank_address',
        'bank_city',
        'bank_country',
        'bank_cod_swift',
        'bank_name2',
        'bank_address2',
        'bank_cod_swift2',
        'others',
    ];
}
