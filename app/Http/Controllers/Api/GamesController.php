<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JoinGameRequest;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $games = Game::all();

        return response()->json([
            'games' => $games
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $game = $request->user()->games()->create();

        return response()->json([
            'result' => 'OK',
            'id' => $game->id
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Game $game)
    {
        $request->validate([
            'data' => 'required|json'
        ]);

        $game->update([
            'data' => json_decode($request->get('data'))
        ]);

        return response()->json([
            'result' => 'OK'
        ]);
    }

    /**
     * Leave a game. If this is the last player in the game, delete the game too
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function destroy(Game $game, Request $request)
    {
        $game->users()->detach($request->user()->id);

        if ($game->users->count() < 1) {
            $game->delete();
        }

        return response('', Response::HTTP_OK);
    }

    /**
     * Handle the join game request.
     *
     * @param JoinGameRequest $request
     * @return JsonResponse
     * @throws ModelNotFoundException
     */
    public function join(JoinGameRequest $request)
    {
        $data = $request->validated();

        if (array_key_exists('game', $data)) {
            // Join specific game
            $game = Game::findOrFail($data['game']);

            if ($game->isFull()) {
                return response()->json([
                    'result' => 'ERROR',
                    'error' => 'Game Full'
                ], Response::HTTP_BAD_REQUEST);
            }

            $game->users()->attach($request->user()->id, ['seat' => $data['seat'] ?? null]);
        } else {
            // Matchmaking request
            $game = Game::firstAvailableOrCreate([
                'host_id' => $request->user()->id
            ]);

            $game->users()->attach($request->user()->id);
        }

        $gameUser = $game->users()->where('id', '=', $request->user()->id)->withPivot(['seat'])->first();

        return response()->json([
            'result' => 'OK',
            'seat' => $gameUser->pivot->seat,
            'game' => $game->id
        ]);
    }
}
