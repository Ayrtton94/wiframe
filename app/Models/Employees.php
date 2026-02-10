<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'dni',
        'name',
        'area',
        'phone',
        'foto',
    ];
}
