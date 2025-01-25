<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use App\Models\Article;

class Category extends Model
{
    //
    protected $fillable=[
        'id','name','admin_id'
    ];
    public function admin(){
        return $this->belongsTo(Admin::class);
    }
    public function articles(){
        return $this->hasMany(Article::class);
    }
}
