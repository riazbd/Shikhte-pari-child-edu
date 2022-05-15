<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CourseContent;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\CourseEnrollment;
use App\Models\CoursePurchase;

class Course extends Model
{
    use HasFactory;

    public function contents(){
        return $this->hasMany(CourseContent::class);
    }

    public function wishlist(){
        return $this->hasMany(Wishlist::class);
    }

    public function enrollments(){
        return $this->hasMany(CourseEnrollment::class);
    }

    public function purchases(){
        return $this->hasMany(CoursePurchase::class);
    }

    public function instructor(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function category(){
        return $this->belongsTo(\App\Models\Category::class);
    }
}
