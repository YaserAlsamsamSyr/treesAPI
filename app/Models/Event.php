<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Image;
use App\Models\Admin;

class Event extends Model
{
    protected $fillable=[
        'title',
        'address',
        'desc',
        'startDate',
        'endDate',
        'orgName',
        'admin_id'
    ];
    //relation
    public function admin(){
        return $this->belongsTo(Admin::class);
    }
    public function images(){
        return $this->hasMany(Image::class);
    }
}
