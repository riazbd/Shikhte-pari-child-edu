<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    public function questions(){
        return $this->hasMany(\App\Models\QuizQuestion::class);
    }

    public function results(){
        return $this->hasMany(\App\Models\QuizResult::class);
    }

    public function courseModule(){
        return $this->belongsTo(\App\Models\CourseContent::class);
    }
    
}
