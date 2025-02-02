<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prize extends Model
{

    protected $guarded = ['id'];




    public  static function nextPrize()
    {
        // TODO: Implement nextPrize() logic here.

        $prizes = Prize::all();
        $totalProbability = $prizes->sum('probability');

        $randomNumber = mt_rand(1, $totalProbability * 100) / 100; 
        $cumulativeProbability = 0;

        foreach ($prizes as $prize) {
            $cumulativeProbability += $prize->probability;
            if ($randomNumber <= $cumulativeProbability) {
                return $prize;
            }
        }
        return $prizes->last(); 
    }

}
