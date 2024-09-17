<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = ['user', 'score', 'level', 'max_score', 'type'];

    public function user(){
        return $this->hasMany(User::class, 'id', 'user');
    }
}
