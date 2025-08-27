<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as Http;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(): array
    {
    /** @var User $user */
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_store_creates_user(): void
    {
        $headers = $this->authHeaders();
        $payload = [
            'first_name' => 'Alice',
            'last_name' => 'Admin',
            'email' => 'alice@example.com',
            'password' => 'secret123',
        ];
        $res = $this->postJson('/api/users', $payload, $headers);
        $res->assertStatus(Http::HTTP_CREATED)
            ->assertJsonPath('email', 'alice@example.com');
        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function test_update_modifies_user(): void
    {
        $headers = $this->authHeaders();
        $user = User::factory()->create(['first_name' => 'Old']);
        $res = $this->putJson('/api/users/'.$user->id, [
            'first_name' => 'NewName',
            'last_name' => $user->last_name,
            'email' => $user->email,
        ], $headers);
        $res->assertOk()->assertJsonPath('first_name', 'NewName');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'first_name' => 'NewName']);
    }

    public function test_destroy_soft_deletes_user(): void
    {
        $headers = $this->authHeaders();
        $user = User::factory()->create();
        $this->deleteJson('/api/users/'.$user->id, [], $headers)
            ->assertStatus(Http::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
