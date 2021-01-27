<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Game;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_games_can_be_created()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('games.store'));

        $response->assertJsonStructure([
            'result',
            'id'
        ])->assertStatus(Response::HTTP_CREATED);
    }

    public function test_only_authenticated_users_can_create_games()
    {
        $response = $this->postJson(route('games.store'));
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_games_data_can_be_updated()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create game
        $r = $this->postJson(route('games.store'));
        $game = Game::findOrFail($r['id']);

        // Update the game data through the API
        $response = $this->patchJson(route('games.update', [$game]), [
            'data' =>
            json_encode(['the_message' => 'Hello World'])
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'result' => 'OK'
        ]);
    }
}
