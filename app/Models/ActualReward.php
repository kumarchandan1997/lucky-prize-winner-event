<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualReward extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'probability', 'actual_percentage','number_of_people'];
}
