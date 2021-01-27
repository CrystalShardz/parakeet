<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

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
}
