<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    use HasFactory;

    public function quiz(){
        return $this->belongsTo(\App\Models\Quiz::class);
    }

    public function user(){
        return $this->belongsTo(\App\Models\User::class);
    }
}
