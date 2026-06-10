<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $data = [
          'name' => 'testName',
          'password' => '123@QWER',
          'email' => 'testEmail@test.com'
        ];

        $response = $this->postJson(route('register'),$data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users',[
           'name' => $data['name'],
           'email' => $data['email']
        ]);
    }

    public function test_user_can_not_register_with_used_email()
    {
        $userExisted = User::factory()->create();

        $user = User::factory()->make([
            'email' => $userExisted->email
        ]);

        $data = [
            ...$user->toArray(),
            'password' => '123@QWER'
        ];

        $response = $this->postJson(route('register'),$data);

        $response->assertJsonValidationErrors(['email']);
        $response->assertStatus(422);
        $this->assertDatabaseMissing('users',[
            'name' => $user->name,
            'email' => $user->email
        ]);
    }

    public function test_user_can_login()
    {
        $password = '123@QWEr';

        $user = User::factory()->create([
            'password' => $password
        ]);

        $response = $this->postJson(route('login',[
            'email' => $user->email,
            'password' => $password
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
        ]);
    }

    public function test_user_can_not_login_with_wrong_credentials()
    {
        $password = '123@QWEr';

        $user = User::factory()->create([
            'password' => $password
        ]);

        $response = $this->postJson(route('login',[
            'email' => $user->email,
            'password' => 123
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

    }

    public function test_user_can_see_itself_with_token()
    {
        $password = '123@QWEr';

        $user = User::factory()->create([
            'password' => $password
        ]);

        $user->createToken('test');

        $response = $this->actingAs($user)->getJson(route('user.show'));

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
        $json
            ->where('data.email', $user->email)
            ->where('data.name', $user->name)
            ->etc()
        );
    }
}
