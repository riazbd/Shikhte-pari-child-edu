<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    public function answers(){
        return $this->hasMany(\App\Models\QuizQuestionAnswer::class);
    }

    public function chosenAnswers(){
        return $this->hasMany(\App\Models\QuizChosenAnswer::class);
    }

    public function quiz(){
        return $this->belongsTo(\App\Models\Quiz::class);
    }
}
