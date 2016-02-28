<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class User extends Model
{
    public static function regist() {

        $user = new User;
        $user->unique_id = sha1( uniqid( mt_rand() , true ) );
        $user->save();

        return $user->unique_id;

    }
}
