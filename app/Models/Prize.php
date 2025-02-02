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
        
        $prizeChoices = [];
        
        foreach ($prizes as $prize) {
            // Add the prize to the choices array based on its probability
            $prizeChoices = array_merge($prizeChoices, array_fill(0, round($prize->probability), $prize->id));
        }

        $selectedPrizeId = $prizeChoices[array_rand($prizeChoices)];
        
        return Prize::find($selectedPrizeId);
    }

}
