<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

class GameUser extends Pivot
{
    public static function boot() {
        parent::boot();
        static::creating(function(GameUser $model) {
            // Ensure user seat is assigned
            if(is_null($model->seat)) {
                // Assign next available seat
                $lastSeat = DB::table('game_user')->select('seat')->where('game_id', '=', $model->game_id)->limit(1)->first(['seat']);
                if(null == $lastSeat) {
                    $nextSeat = 1;
                } else {
                    $nextSeat = $lastSeat->seat + 1;
                }
                $model->seat = $nextSeat;
            }
        });

        static::deleted(function(GameUser $model) {
            $game = Game::findOrFail($model->game_id);
            if($game->host->id == $model->user_id) {
                $game->setRandomHost();
            }
        });
    }
}
