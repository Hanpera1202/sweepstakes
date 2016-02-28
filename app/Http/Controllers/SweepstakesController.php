<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sweepstakes;

class SweepstakesController extends Controller
{
    public function getIndex(Request $request) {
        $sweepstakes = Sweepstakes::getActive($request->input("user_unique_id"));
        return response()->json(array("sweepstakes" => $sweepstakes));
    }

}
