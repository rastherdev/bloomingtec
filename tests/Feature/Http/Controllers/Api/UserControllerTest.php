<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\UserController
 */
final class UserControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\UserController::class,
            'store',
            \App\Http\Requests\Api\UserStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_responds_with(): void
    {
        $first_name = fake()->firstName();
        $last_name = fake()->lastName();
        $email = fake()->safeEmail();
        $password = fake()->password();

        $response = $this->post(route('users.store'), [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $password,
        ]);

        $users = User::query()
            ->where('first_name', $first_name)
            ->where('last_name', $last_name)
            ->where('email', $email)
            ->where('password', $password)
            ->get();
        $this->assertCount(1, $users);
        $user = $users->first();

        $response->assertOk();
        $response->assertJson($user);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\Api\UserController::class,
            'update',
            \App\Http\Requests\Api\UserUpdateRequest::class
        );
    }

    #[Test]
    public function update_responds_with(): void
    {
        $user = User::factory()->create();
        $first_name = fake()->firstName();
        $last_name = fake()->lastName();
        $email = fake()->safeEmail();
        $phone = fake()->phoneNumber();

        $response = $this->put(route('users.update', $user), [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
        ]);

        $user->refresh();

        $response->assertOk();
        $response->assertJson($user);

        $this->assertEquals($first_name, $user->first_name);
        $this->assertEquals($last_name, $user->last_name);
        $this->assertEquals($email, $user->email);
        $this->assertEquals($phone, $user->phone);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('users.destroy', $user));

        $response->assertNoContent();

        $this->assertSoftDeleted($user);
    }
}
