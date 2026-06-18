<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $fillable = ['application_id','name','email','phone','department','ssc_gpa','father_name','mother_name','address','blood_group','status'];
}