<?php

namespace App\Services\Auth;

use LaravelEasyRepository\BaseService;

interface AuthService extends BaseService
{

    public function login($email, $password): array;

    public function logout($user): array;

    public function refreshToken($refreshToken): array;

    public function verifyProfile($data): array;
}
