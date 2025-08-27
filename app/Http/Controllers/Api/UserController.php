<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserStoreRequest;
use App\Http\Requests\Api\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserController extends Controller
{
    public function store(UserStoreRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
    return response()->json($user, HttpResponse::HTTP_CREATED);
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());
        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
    return response()->json([], HttpResponse::HTTP_NO_CONTENT);
    }
}
