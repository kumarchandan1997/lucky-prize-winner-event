<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\ActualReward;
use App\Http\Requests\PrizeRequest;
use Illuminate\Http\Request;
use App\Models\Prize;



class PrizesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $prizes = Prize::all();
        $actualReward = ActualReward::all();
        $numberOfPeople = $actualReward->isNotEmpty() ? $actualReward->first()->number_of_people : 100;

        return view('prizes.index', ['prizes' => $prizes,'actualReward' => $actualReward , 'numberOfPeople' => $numberOfPeople]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('prizes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PrizeRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PrizeRequest $request)
    {
        // Validate probability before saving
        $validationResponse = $this->validateProbability($request->input('probability'));
        if ($validationResponse) {
            return $validationResponse;
        }

        $prize = new Prize;
        $prize->title = $request->input('title');
        $prize->probability = floatval($request->input('probability'));
        $prize->save();

        return to_route('prizes.index');
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $prize = Prize::findOrFail($id);
        return view('prizes.edit', ['prize' => $prize]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PrizeRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PrizeRequest $request, $id)
    {
        // Validate probability before updating
        $validationResponse = $this->validateProbability($request->input('probability'), $id);
        if ($validationResponse) {
            return $validationResponse;
        }

        $prize = Prize::findOrFail($id);
        $prize->title = $request->input('title');
        $prize->probability = floatval($request->input('probability'));
        $prize->save();

        return to_route('prizes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $prize = Prize::findOrFail($id);
        $prize->delete();

        return to_route('prizes.index');
    }


    public function simulate(Request $request)
    {
        $request->validate([
            'number_of_prizes' => 'required|integer|min:1',
        ]);

        $numberOfPrizes = $request->number_of_prizes;

        ActualReward::truncate();

        $actualRewards = [];

        for ($i = 0; $i < $numberOfPrizes ?? 10; $i++) {

            $selectedPrize = Prize::nextPrize();

            if (!isset($actualRewards[$selectedPrize->title])) {
                $actualRewards[$selectedPrize->title] = 0;
            }
            $actualRewards[$selectedPrize->title]++;
        }

        foreach ($actualRewards as $title => $count) {
            $totalCount = $numberOfPrizes;
            $probability = Prize::where('title', $title)->first()->probability;
            
            $actualPercentage = ($count / $totalCount) * 100;
            $numberOfPeople = round(($actualPercentage / 100) * $totalCount);
            
            // Store in actual_rewards table
            ActualReward::create([
                'title' => $title,
                'probability' => $probability,
                'actual_percentage' => $actualPercentage,
                'number_of_people' => $numberOfPrizes,
            ]);

            // Update the awarded_people column in the prizes table
            $prize = Prize::where('title', $title)->first();
            if ($prize) {
                $prize->awarded_people = $numberOfPeople;
                $prize->save();
            }

        }

        return to_route('prizes.index');
    }

    public function reset()
    {
        // TODO : Write logic here
        ActualReward::truncate();
        return to_route('prizes.index');
    }

    private function validateProbability($newProbability, $excludeId = null)
    {
    $query = Prize::query();
    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }
    
    $current_probability = floatval($query->sum('probability'));
    $total_probability = $current_probability + floatval($newProbability);

    if ($total_probability > 100) {
        $exceed_amount = $total_probability - 100;

        return redirect()->back()->withErrors([
            "The Probability field must not be greater than $current_probability%."
        ])->withInput();
    }

    return null; 
 }
}
