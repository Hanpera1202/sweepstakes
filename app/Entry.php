<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Entry extends Model
{
    protected $table = 'entries';
    protected $fillable = ['id', 'user_id', 'sweepstakes_id'];

}
