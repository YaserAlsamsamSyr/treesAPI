<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Article;
use App\Models\Advertisement;
use App\Models\Admin;
use App\Models\Event;
use App\Models\Work;
use App\Models\Planstore;

class Image extends Model
{
    protected $fillable=[
        'img',
        'article_id',
        'advertisement_id',
        'admin_id',
        'event_id',
        'work_id',
        'planstore_id'
    ];
    //relation
    public function article(){
        return $this->belongsTo(Article::class);
    }
    public function advertisement(){
        return $this->belongsTo(Advertisement::class);
    }
    public function admin(){
        return $this->belongsTo(Admin::class);
    }
    public function event(){
        return $this->belongsTo(Event::class);
    }
    public function work(){
        return $this->belongsTo(Work::class);
    }
    public function planstore(){
        return $this->belongsTo(Planstore::class);
    }
}
