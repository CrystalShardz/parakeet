<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Game;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
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
        $game = $user->games()->create();

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

    public function test_all_games_list_can_be_retrieved()
    {
        Sanctum::actingAs(User::factory()->create());

        User::factory(5)->has(Game::factory()->count(2))->create();

        $response = $this->getJson(route('games.index'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_games_can_be_joined()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(User::factory()->create());

        $user = User::factory()->has(Game::factory())->create();

        $game = $user->games()->first();

        $response = $this->postJson(route('games.join'), ['game' => $game->id]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'result' => 'OK'
        ]);
    }

    public function test_full_games_cannot_be_joined()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(User::factory()->create());

        $host = User::factory()->has(Game::factory())->create();

        $game = $host->games()->first();

        $game->users()->attach([(User::factory()->create())->id]);

        $response = $this->postJson(route('games.join'), ['game' => $game->id]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'result' => 'ERROR',
            'error' => 'Game Full'
        ]);
    }

    public function test_user_can_pick_a_seat()
    {
        $this->withoutExceptionHandling();
        $user = User::Factory()->create();
        Sanctum::actingAs($user);

        $host = User::factory()->has(Game::Factory())->create();
        $game = $host->games()->first();
        $game->update(['max_players' => 5]);

        // Send join request with a seat number as form data
        $response = $this->postJson(route('games.join'), ['game' => $game->id, 'seat' => 5]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'result' => 'OK',
            'seat' => 5
        ]);
    }

    public function test_user_assigned_next_available_seat()
    {
        $this->withoutExceptionHandling();
        $user = User::Factory()->create();
        Sanctum::actingAs($user);

        $host = User::factory()->has(Game::factory())->create();
        $game = $host->games()->first();
        $game->update(['max_players' => 5]);

        $response = $this->postJson(route('games.join'), ['game' => $game->id]);

        $response->assertJson([
            'result' => 'OK',
            'seat' => 2
        ]);
    }

    public function test_user_can_leave_a_game()
    {
        $this->withoutExceptionHandling();
        [$host, $user] = User::Factory()->count(2)->create();

        $game = $host->games()->create([
            'host_id' => $host->id
        ]);
        $game->users()->attach($user);

        Sanctum::actingAs($user);
        $response = $this->delete(route('games.destroy', [$game]));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(1, $game->users()->count());
    }

    public function test_game_is_deleted_when_all_users_leave()
    {
        $this->withoutExceptionHandling();
        [$host, $user] = User::Factory()->count(2)->create();

        $game = $host->games()->create([
            'host_id' => $host->id
        ]);
        $game->users()->attach($user);

        Sanctum::actingAs($user);
        $response = $this->delete(route('games.destroy', [$game]));

        Sanctum::actingAs($host);
        $response = $this->delete(route('games.destroy', [$game]));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(0, $host->games()->count());
    }

    public function test_host_is_reassigned_when_host_leaves()
    {
        $this->withoutExceptionHandling();
        [$host, $user] = User::Factory()->count(2)->create();

        $game = $host->games()->create([
            'host_id' => $host->id
        ]);
        $game->users()->attach($user);

        Sanctum::actingAs($host);
        $response = $this->delete(route('games.destroy', [$game]));

        $response->assertStatus(200);
        // refresh game object
        $game->refresh();
        $this->assertEquals($user->id, $game->host->id);
    }

    public function test_matchmaking_creates_game_when_no_games_exist()
    {
        $this->withoutExceptionHandling();
        $user = User::Factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('games.join'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'result' => 'OK',
            'seat' => 1,
            'game' => $user->games()->first()->id
        ]);
    }

    public function test_matchmaking_joins_existing_game()
    {
        $this->withoutExceptionHandling();
        [$host, $client] = User::factory()->count(2)->create();

        $game = $host->games()->create(['host_id' => $host->id]);

        Sanctum::actingAs($client);

        $response = $this->postJson(route('games.join'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'result' => 'OK',
            'seat' => 2,
            'game' => $game->id
        ]);
    }
}
