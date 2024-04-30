<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(
        AuthService $authService,
    )
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->email;
        $password = $request->password;
        $result = $this->authService->login($email, $password);
        if (!$result['status']) return $this->failResponse($result['data'], 200, $result['message']);
        return $this->successResponse($result['data']);
    }

    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = $request->refresh_token;
        $result = $this->authService->refreshToken($refreshToken);
        if (!$result['status']) return $this->failResponse($result['data'], 200, $result['message']);
        return $this->successResponse($result['data']);
    }

    public function logout(Request $request): JsonResponse
    {
        $result = $this->authService->logout($request->user());
        if (!$result['status']) return $this->failResponse($result['data'], 200, $result['message']);
        return $this->successResponse($result['data']);
    }

    public function verifyProfile(UpdateProfileRequest $request): JsonResponse
    {
        $request['user_id'] = auth()->id();
        $result = $this->authService->verifyProfile($request->all());
        if (!$result['status']) return $this->failResponse($result['data'], 200, $result['message']);
        return $this->successResponse($result['data']);
    }
}
