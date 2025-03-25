<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Planstore;
use App\Models\Image;
use App\Models\Volunteer;

class Advertisement extends Model
{
    protected $fillable=[
        'name',
        'desc',
        'status',
        'isDone',
        'plantsStoreName',
        'planstore_id',
        'volunteer_id'
    ];
    // relation
    public function planstore(){
        return $this->belongsTo(Planstore::class);
    }
    public function volunteer(){
        return $this->belongsTo(Volunteer::class);
    }
    public function images(){
        return $this->hasMany(Image::class);
    }
}
