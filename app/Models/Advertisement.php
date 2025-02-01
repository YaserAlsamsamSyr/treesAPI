<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\planstore;
use App\Models\volunteer;
use App\Models\Image;

class Advertisement extends Model
{
    protected $fillable=[
        'name',
        'desc',
        'status',
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
