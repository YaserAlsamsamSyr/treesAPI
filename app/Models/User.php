<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Planstore;
use App\Models\Volunteer;
use App\Models\Admin;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'userName',
        'password',
        'role',
        'logo',
        'user_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // relation
    public function planstore(){
        return $this->hasOne(planstore::class);
    }
    public function volunteer(){
        return $this->hasOne(Volunteer::class);
    }
    public function admin(){
        return $this->hasOne(Admin::class);
    }
    // self join
    public function getparent(){
        return $this->belongTo(self::class,'user_id');
    }
    public function getchilds(){
        return $this->hasMany(self::class);
    }
    
}
