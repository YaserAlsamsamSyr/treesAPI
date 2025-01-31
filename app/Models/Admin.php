<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\planstore;
use App\Models\Image;
use App\Models\Volunteer;
use App\Models\Event;
use App\Models\Category;

class Admin extends Model
{
   protected $fillable=[
           'orgName',
           'desc',
           'address',
           'phone',
           'user_id'
    ];
    //relation
    public function person(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function planstores(){
        return $this->hasMany(Planstore::class);
    }    
    public function images(){
        return $this->hasMany(Image::class);
    }      
    public function events(){
        return $this->hasMany(Event::class);
    }    
    public function categories(){
        return $this->hasMany(Category::class);
    }
}
