<?php

namespace App\Repositories\Reward;

use LaravelEasyRepository\Repository;

interface RewardRepository extends Repository
{

    public function insertReward($data): array;

    public function getPointByUserId($userId): array;

    public function deductPoint($userId, $point): array;
}
