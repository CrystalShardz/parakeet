<?php

namespace App\Observers;

use App\Models\Game;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class GameObserver
{
    /**
     * HAndle the Game "creating" event.
     *
     * @param Game $game
     * @return void
     */
    public function creating(Game $game)
    {
        // Generate uuid for game
        $game->id = Str::uuid()->toString();

        // Ensure max_players is set
        if (is_null($game->max_players)) {
            $game->max_players = getenv('GAME_DEFAULT_MAX_PLAYERS');
        }

        // Ensure host is set
        if (null == $game->host) {
            $game->host()->associate(Auth::user());
        }
    }

    /**
     * Handle the Game "created" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function created(Game $game)
    {
        //
    }

    /**
     * Handle the Game "updated" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function updated(Game $game)
    {
        //
    }

    /**
     * Handle the Game "deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function deleted(Game $game)
    {
        //
    }

    /**
     * Handle the Game "restored" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function restored(Game $game)
    {
        //
    }

    /**
     * Handle the Game "force deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function forceDeleted(Game $game)
    {
        //
    }
}
