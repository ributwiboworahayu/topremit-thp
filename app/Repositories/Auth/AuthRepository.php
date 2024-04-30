<?php

namespace App\Repositories\Auth;

use LaravelEasyRepository\Repository;

interface AuthRepository extends Repository
{

    public function login($email, $password): array;

    public function logout($user): array;
}
