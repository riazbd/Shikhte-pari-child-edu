<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestionAnswer extends Model
{
    use HasFactory;

    public function question(){
        return $this->belongsTo(\App\Models\QuizQuestion::class);
    }

    public function user(){
        return $this->belongsTo(\App\Models\User::class);
    }
}
