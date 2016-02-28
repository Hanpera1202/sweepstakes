<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Sweepstakes;
use App\Libs\Crypt;
use Carbon\Carbon;

class UserController extends Controller
{
    public function postCreate() {
        $user_unique_id = User::regist();
        return response()->json(array("user_id" => $user_unique_id));
    }

    public function postUpdate(Request $request, $user_unique_id) {
        $mail_address = Crypt::mc_decrypt($request->input('mail_address'));
        $user = User::where('unique_id', '=', $user_unique_id)->firstOrFail();
        $user->mail_address = $mail_address;
        return response()->json(array("result" => $user->save()));
    }

    public function postEntry(Request $request, $user_unique_id) {
        $decoded_entry_data = Crypt::mc_decrypt($request->input('entry_data'));
        parse_str($decoded_entry_data, $entry_data);
        if(count($entry_data) < 2 || !is_numeric($entry_data["sweepstakes_id"])){
            $result = array("result" => false,
                            "reason" => "FAILED",
                            "sweepstakes_id" => NULL);
            return response()->json($result);
        }
        $now_time = Carbon::now();
        if($entry_data["timestamp"] + 10 < $now_time->timestamp){
            $result = array("result" => false,
                            "reason" => "FAILED",
                            "sweepstakes_id" => $entry_data["sweepstakes_id"]);
            return response()->json($result);
        }
        $result = Sweepstakes::entry($user_unique_id, $entry_data["sweepstakes_id"]);
        return response()->json($result);
    }

    public function getResults($user_unique_id) {
        $results = Sweepstakes::getResults($user_unique_id);
        return response()->json(array("results" => $results));
    }

    public function getResult($user_unique_id, $sweepstakes_id) {
        $result = Sweepstakes::getResult($user_unique_id, $sweepstakes_id);
        return response()->json($result);
    }

}
