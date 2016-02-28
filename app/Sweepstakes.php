<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Sweepstakes extends Model
{

    protected $table = 'sweepstakes';

    public static function getActive($user_unique_id) {
        $user = User::where('unique_id', '=', $user_unique_id)->first();
        if(!isset($user->id)){
            return false;
        }
        $user_id = $user->id;
        $now_time = Carbon::now();
        $sweepstakes = 
            DB::table('sweepstakes')
                ->select(DB::raw("sweepstakes.id,items.name,items.image_url,".
                                 "sweepstakes.win_num,sweepstakes.end_date,".
                                 "sweepstakes.entry_num as total_entry_num,".
                                 "ifnull(entries.entry_num, 0) as entry_num"))
                ->join('items', 'sweepstakes.item_id', '=', 'items.id')
                ->leftJoin('entries', function($leftJoin) use ($user_id)
                {
                    $leftJoin->on('sweepstakes.id', '=', 'entries.sweepstakes_id')
                             ->where('entries.user_id', '=', $user_id);
                })
                ->where('sweepstakes.start_date', '<=', $now_time->toDateTimeString())
                ->where('sweepstakes.end_date', '>=', $now_time->toDateTimeString())
                ->get();
                //->toSql();

        return $sweepstakes;
    }

    public static function getData($sweepstakes_id){
        if(!is_numeric($sweepstakes_id)){
            return false;
        }
        $sweepstakes = 
            DB::table('sweepstakes')
                ->select(DB::raw("sweepstakes.id,items.name,items.image_url,".
                                 "sweepstakes.win_num,sweepstakes.start_date,".
                                 "sweepstakes.end_date,".
                                 "sweepstakes.entry_num as total_entry_num"))
                ->join('items', 'sweepstakes.item_id', '=', 'items.id')
                ->where('sweepstakes.id', '=', $sweepstakes_id)
                ->get();
        if(count($sweepstakes) > 0){
            return $sweepstakes[0]; 
        }
        return false;
    }
    
    public static function entry($user_unique_id, $sweepstakes_id) {
        $user = User::where('unique_id', '=', $user_unique_id)->first();
        if(!isset($user->id)){
            return array("result" => false,
                         "reason" => "FAILED",
                         "sweepstakes_id" => $sweepstakes_id);
        }
        $user_id = $user->id;
        $now_time = Carbon::now();
        $sweepstakes = self::getData($sweepstakes_id);
        if($sweepstakes == false ||
           $sweepstakes->start_date > $now_time->toDateTimeString()){
            return array("result" => false,
                         "reason" => "FAILED",
                         "sweepstakes_id" => $sweepstakes_id);
        }
        if($sweepstakes->end_date < $now_time->toDateTimeString()){
            return array("result" => false, 
                         "reason" => "ENDED",
                         "sweepstakes_id" => $sweepstakes_id);
        }

        DB::transaction(function() use ($user_id, $sweepstakes_id){
            $entry = Application::firstOrCreate(['user_id' => $user_id,
                                                       'sweepstakes_id' => $sweepstakes_id]);
            $entry->increment('entry_num', 1);
            $sweepstakes = Competition::find($sweepstakes_id);
            $sweepstakes->increment('entry_num', 1);
        });

        return array("result" => true,
                     "reason" => "SUCCESS",
                     "sweepstakes_id" => $sweepstakes->id);

    }

    public static function getResults($user_unique_id) {
        $user = User::where('unique_id', '=', $user_unique_id)->first();
        if(!isset($user->id)){
            return false;
        }
        $user_id = $user->id;
        $now_time = Carbon::now();
        $results = 
            DB::table('entries')
                ->select(DB::raw("entries.result,entries.receive_flag,".
                                 "sweepstakes.id,items.name,items.image_url,".
                                 "sweepstakes.win_num,sweepstakes.end_date,".
                                 "sweepstakes.entry_num as total_entry_num,".
                                 "entries.entry_num as entry_num"))
                ->Join('sweepstakes', 'entries.sweepstakes_id', '=', 'sweepstakes.id')
                ->join('items', 'sweepstakes.item_id', '=', 'items.id')
                ->where('entries.user_id', '=', $user_id)
                ->get();
                //->toSql();

        foreach($results as $key => $result){
            if($result->end_date > $now_time->toDateTimeString()){
                $results[$key]->progress = "1";
            }elseif($result->result == "0"){
                $results[$key]->progress = "2";
            }else{
                $results[$key]->progress = "3";
            }
        }

        return $results;
    }

    public static function getResult($user_unique_id, $sweepstakes_id) {
        $user = User::where('unique_id', '=', $user_unique_id)->firstOrFail();
        if(!isset($user->id)){
            return false;
        }
        $user_id = $user->id;
        $now_time = Carbon::now();
        $result = 
            DB::table('entries')
                ->select(DB::raw("entries.result,entries.receive_flag,".
                                 "sweepstakes.id,items.name,items.image_url,".
                                 "sweepstakes.win_num,sweepstakes.end_date,".
                                 "sweepstakes.entry_num as total_entry_num,".
                                 "entries.entry_num as entry_num"))
                ->Join('sweepstakes', 'entries.sweepstakes_id', '=', 'sweepstakes.id')
                ->join('items', 'sweepstakes.item_id', '=', 'items.id')
                ->where('entries.user_id', '=', $user_id)
                ->where('entries.sweepstakes_id', '=', $sweepstakes_id)
                ->first();
                //->toSql();

        if(!$result){
            return false;
        }

        if($result->end_date > $now_time->toDateTimeString()){
            $result->progress = "1";
        }elseif($result->result == "0"){
            $result->progress = "2";
        }else{
            $result->progress = "3";
        }

        return $result;
    }
}
