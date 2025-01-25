<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traffic extends Model
{
    protected $fillable=[
        'mac',
        'isFirst',
        'year',
        'month',
        'day'
    ];
}
