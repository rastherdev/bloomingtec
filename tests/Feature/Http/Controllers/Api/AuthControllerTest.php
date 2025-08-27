<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as Http;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token_and_user(): void
    {
        $res = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $res->assertStatus(Http::HTTP_CREATED)
            ->assertJsonStructure(['access_token','token_type','expires_in','user'=>['id','email']]);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_register_duplicate_email_returns_422(): void
    {
        User::factory()->create(['email' => 'john@example.com']);
        $res = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);
        $res->assertStatus(Http::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_success_and_me(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);
        $login->assertOk()->assertJsonStructure(['access_token']);
        $token = $login->json('access_token');

        $me = $this->getJson('/api/auth/me', ['Authorization' => 'Bearer '.$token]);
        $me->assertOk()->assertJsonPath('id', $user->id);
    }

    public function test_me_without_token_is_unauthorized(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(Http::HTTP_UNAUTHORIZED);
    }

    public function test_refresh_and_logout(): void
    {
    /** @var User $user */
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

        $refresh = $this->postJson('/api/auth/refresh', [], ['Authorization' => 'Bearer '.$token]);
        $refresh->assertOk()->assertJsonStructure(['access_token']);
        $newToken = $refresh->json('access_token');

        $logout = $this->postJson('/api/auth/logout', [], ['Authorization' => 'Bearer '.$newToken]);
        $logout->assertStatus(Http::HTTP_OK)->assertJsonPath('message', 'Logged out');
    }
}
