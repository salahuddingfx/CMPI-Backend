<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password','department','student_id','semester','session','phone','guardian','blood_group','address','admission_date','role','avatar','status','sub_role'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime','password'=>'hashed','admission_date'=>'date'];

    public function hasPermission(string $module): bool
    {
        if ($this->role !== 'admin') {
            return false;
        }

        // If sub_role is empty or 'super_admin', they have all permissions
        if (empty($this->sub_role) || $this->sub_role === 'super_admin') {
            return true;
        }

        switch ($this->sub_role) {
            case 'academic_editor':
                return in_array($module, ['subjects', 'routines', 'results', 'departments', 'academic_calendar']);
            case 'content_manager':
                return in_array($module, ['notices', 'events', 'blogs', 'hero_slides', 'social_links', 'feedbacks']);
            case 'admission_officer':
                return in_array($module, ['admissions', 'faculty']);
            case 'accountant':
                return in_array($module, ['bills', 'reports']);
            default:
                return false;
        }
    }

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