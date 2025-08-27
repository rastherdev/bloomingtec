<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuthLoginRequest;
use App\Http\Requests\Api\AuthRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request): Response
    {
        $user->save();

        return $user;
    }

    public function login(AuthLoginRequest $request): Response
    {
        return response()->noContent(200);
    }

    public function logout(Request $request): Response
    {
        return response()->noContent();
    }

    public function me(Request $request): Response
    {
        return response()->noContent(200);
    }

    public function refresh(Request $request): Response
    {
        return response()->noContent(200);
    }
}
