<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use App\Models\User;
use App\Models\Image;
use App\Models\Advertisement;

class Planstore extends Model
{
    protected $fillable=[
        'mac',
        'address',
        'phone',
        'ownerName',
        'desc',
        'openTime',
        'closeTime',
        'isApproved',
        'rejectDesc',
        'adminApproved',
        'user_id'
    ];
    // relation
    public function persone(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function images(){
        return $this->hasMany(Image::class);
    }
    public function advertisements(){
        return $this->hasMany(Advertisement::class);
    }
    
}
