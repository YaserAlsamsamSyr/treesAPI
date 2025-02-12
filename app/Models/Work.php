<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Image;
use App\Models\Volunteer;

class Work extends Model
{
    protected $fillable=[
        'name',
        'address',
        'desc',
        'status',
        'isDone',
        'mac',
        'volunteer_id'
    ];
    public function images(){
        return $this->hasMany(Image::class);
    }
    public function volunteer(){
        return $this->belongsTo(Volunteer::class);
    }
}
