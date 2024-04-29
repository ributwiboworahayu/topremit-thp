<?php

namespace App\Services\Auth;

use App\Repositories\Auth\AuthRepository;
use App\Traits\ServiceResponser;
use LaravelEasyRepository\Service;

class AuthServiceImplement extends Service implements AuthService
{

    use ServiceResponser;

    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected AuthRepository $mainRepository;

    public function __construct(
        AuthRepository $mainRepository,
    )
    {
        $this->mainRepository = $mainRepository;
    }

    public function login($email, $password): array
    {
        $result = $this->mainRepository->login($email, $password);
        if (!$result['status']) return $this->finalResultFail(message: $result['message']);
        return $this->finalResultSuccess($result['data']);
    }

    public function refreshToken($refreshToken): array
    {
        $result = $this->mainRepository->refreshToken($refreshToken);
        if (!$result['status']) return $this->finalResultFail(message: $result['message']);
        return $this->finalResultSuccess($result['data']);
    }
}
