<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maze extends Model
{
    use HasFactory;
    protected $fillable = [
        'entrance',
        'gridSize',
        'walls',
        'user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
