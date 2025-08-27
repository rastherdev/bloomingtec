<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuthLoginRequest;
use App\Http\Requests\Api\AuthRegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['first_name'].' '.$data['last_name'].'-'.Str::random(6));
        $user = User::create($data);
        $token = JWTAuth::fromUser($user);
    return $this->respondWithToken($token, $user, HttpResponse::HTTP_CREATED);
    }

    public function login(AuthLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], HttpResponse::HTTP_UNAUTHORIZED);
        }
        return $this->respondWithToken($token, auth()->user());
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    return response()->json(['message' => 'Logged out'], HttpResponse::HTTP_OK);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    public function refresh(): JsonResponse
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return $this->respondWithToken($token, auth()->user());
    }

    protected function respondWithToken(string $token, $user, int $status = HttpResponse::HTTP_OK): JsonResponse
    {
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], $status);
    }
}