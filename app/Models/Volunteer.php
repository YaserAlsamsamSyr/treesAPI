<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Admin;
use App\Models\User;
use App\Models\Advertisement;
use App\Models\Work;
class Volunteer extends Model
{
   protected $fillable=[
    'mac',
    'desc',
    'address',
    'phone',
    'isApproved',
    'rejectDesc',
    'adminApproved',
    'user_id'
   ];
   // relation
   public function user(){
    return $this->belongsTo(User::class);
   }
   public function advertisements(){
    return $this->hasMany(Advertisement::class);
   }
   public function works(){
    return $this->hasMany(Work::class);
   }
}
