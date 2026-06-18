<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password','department','student_id','semester','session','phone','guardian','blood_group','address','admission_date','role','avatar'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime','password'=>'hashed','admission_date'=>'date'];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function courseResults()
    {
        return $this->hasMany(CourseResult::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}