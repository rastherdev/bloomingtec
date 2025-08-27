<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Task;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as Http;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
    /** @var User $user */
    $token = JWTAuth::fromUser($user);
        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_index_returns_only_authenticated_user_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Task::factory()->for($user)->count(2)->create();
        Task::factory()->for($other)->count(3)->create();

        $res = $this->getJson('/api/tasks', $this->authHeaders($user));
        $res->assertOk();
        $this->assertCount(2, $res->json());
    }

    public function test_store_creates_task_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $payload = [
            'title' => 'Sample Task',
            'start_date' => now()->toDateString(),
            'status' => 'incomplete',
        ];
        $res = $this->postJson('/api/tasks', $payload, $this->authHeaders($user));
        $res->assertStatus(Http::HTTP_CREATED)->assertJsonPath('title', 'Sample Task');
        $this->assertDatabaseHas('tasks', ['title' => 'Sample Task', 'user_id' => $user->id]);
    }

    public function test_show_forbidden_for_other_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($owner)->create();
        $this->getJson('/api/tasks/'.$task->id, $this->authHeaders($other))
            ->assertStatus(Http::HTTP_FORBIDDEN);
    }

    public function test_show_not_found_returns_404(): void
    {
        $user = User::factory()->create();
        $this->getJson('/api/tasks/999999', $this->authHeaders($user))
            ->assertStatus(Http::HTTP_NOT_FOUND)
            ->assertJsonPath('message', 'Task not found');
    }

    public function test_show_invalid_id_returns_422(): void
    {
        $user = User::factory()->create();
        $this->getJson('/api/tasks/abcXYZ', $this->authHeaders($user))
            ->assertStatus(Http::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('message', 'Invalid task id');
        $this->getJson('/api/tasks/%23%24', $this->authHeaders($user)) // encoded #$
            ->assertStatus(Http::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('message', 'Invalid task id');
    }

    public function test_update_modifies_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['title' => 'Old']);
        $res = $this->putJson('/api/tasks/'.$task->id, [
            'title' => 'New Title',
            'status' => 'complete',
        ], $this->authHeaders($user));
        $res->assertOk()->assertJsonPath('title', 'New Title');
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'New Title', 'status' => 'complete']);
    }

    public function test_destroy_soft_deletes(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();
        $this->deleteJson('/api/tasks/'.$task->id, [], $this->authHeaders($user))
            ->assertStatus(Http::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
