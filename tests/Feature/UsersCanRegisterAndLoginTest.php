<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersCanRegisterAndLogin extends TestCase
{
    use WithFaker;

    /** @test */
    public function test_user_can_register_through_api()
    {
        $userData = [
            'email' => $this->faker->email(),
            'name' => $this->faker->userName,
            'password' => $this->faker->password()
        ];

        $result = $this->postJson(route('register'), $userData);
        $user = User::where('email', '=', $userData['email'])->get()->first();
        $result->assertStatus(201)
            ->assertJson([
                'result' => 'OK'
            ]);
    }

    /** @test */
    public function test_user_registration_validation()
    {
        $userData = [];

        $result = $this->postJson(route('register'), $userData);
        $result->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'name', 'password']);
    }

    /** @test */
    public function test_user_registration_email_unique()
    {
        $userData = [
            'email' => $this->faker->email(),
            'name' => $this->faker->userName,
            'password' => $this->faker->password()
        ];

        User::create($userData);

        $result = $this->postJson(route('register'), $userData);
        $result->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function test_user_can_login_through_api()
    {
        $this->withoutExceptionHandling();

        $user = User::Factory()->create();

        $result = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'PHPUnit'
        ]);

        $result->assertStatus(200)
            ->assertJsonStructure(['result', 'bearer']);
    }

    /** @test */
    public function test_unknown_users_cannot_login()
    {
        $userData = [
            'email' => $this->faker->email(),
            'name' => $this->faker->userName,
            'password' => $this->faker->password(),
            'device_name' => 'PHPUnit'
        ];

        $result = $this->postJson(route('login'), $userData);
        $result->assertStatus(422)
            ->assertJsonStructure(['result', 'message']);
    }

    /** @test */
    public function test_user_invalid_credentials_rejected()
    {
        $user = User::factory()->create();
        $result = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'invalidpassword',
            'device_name' => 'PHPUnit'
        ]);

        $result->assertStatus(400)
            ->assertJsonStructure(['result', 'message']);
    }
}
