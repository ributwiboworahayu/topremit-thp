<?php

namespace App\Repositories\Reward;

use App\Models\Reward;
use App\Traits\ServiceResponser;
use Exception;
use Illuminate\Database\Eloquent\Model;
use LaravelEasyRepository\Implementations\Eloquent;

class RewardRepositoryImplement extends Eloquent implements RewardRepository
{
    use ServiceResponser;

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected Reward $model;

    public function __construct(Reward $model)
    {
        $this->model = $model;
    }

    public function insertReward($data): array
    {
        try {
            $reward = $this->model->where('user_id', $data['user_id'])->first();
            if ($reward) {
                $reward->update([
                    'point' => $reward['point'] + $data['point'],
                ]);
            } else {
                $reward = $this->model->create([
                    'user_id' => $data['user_id'],
                    'point' => $data['point'],
                ]);

            }
            return $this->finalResultSuccess($reward);
        } catch (Exception $exception) {
            return $this->finalResultFail([], $exception->getMessage());
        }
    }

    public function getPointByUserId($userId): array
    {
        try {
            $rewardPoint = $this->model->where('user_id', $userId)->value('point') ?? 0;
            return $this->finalResultSuccess($rewardPoint);
        } catch (Exception $exception) {
            return $this->finalResultFail([], $exception->getMessage());
        }
    }

    public function deductPoint($userId, $point): array
    {
        try {
            $reward = $this->model->where('user_id', $userId)->first();
            if ($reward['point'] < $point) {
                return $this->finalResultFail([], 'Insufficient point');
            }
            $reward->update([
                'point' => $reward['point'] - $point,
                'redeemed_point' => $reward['redeemed_point'] + $point,
                'redeemed_count' => $reward['redeemed_count'] + 1,
                'last_redeemed_at' => now(),
            ]);
            return $this->finalResultSuccess($reward);
        } catch (Exception $exception) {
            return $this->finalResultFail([], $exception->getMessage());
        }
    }
}
