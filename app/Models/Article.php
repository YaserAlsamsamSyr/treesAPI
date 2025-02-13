<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Image;

class Article extends Model
{
    protected $fillable=[
        'title',
        'desc',
        'category_id'
    ];
    //relation
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function images(){
        return $this->hasMany(Image::class);
    }
}
