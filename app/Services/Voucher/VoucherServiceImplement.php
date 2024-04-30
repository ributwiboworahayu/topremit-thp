<?php

namespace App\Services\Voucher;

use App\Repositories\Reward\RewardRepository;
use App\Repositories\Voucher\VoucherRepository;
use App\Traits\ServiceResponser;
use LaravelEasyRepository\Service;

class VoucherServiceImplement extends Service implements VoucherService
{
    use ServiceResponser;

    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected VoucherRepository $mainRepository;
    protected RewardRepository $rewardRepository;

    public function __construct(
        VoucherRepository $mainRepository,
        RewardRepository  $rewardRepository
    )
    {
        $this->mainRepository = $mainRepository;
        $this->rewardRepository = $rewardRepository;
    }

    public function index(): array
    {
        $vouchers = $this->mainRepository->all();
        return $this->finalResultSuccess($vouchers);
    }

    public function redeem($voucherCode, $userId): array
    {
        $voucher = $this->mainRepository->findByCode($voucherCode);
        if (!$voucher['status']) return $this->finalResultFail(message: $voucher['message']);

        $hasRedeemed = $this->mainRepository->hasRedeemedVoucherId($voucher['data']['id'], $userId);
        if (!$hasRedeemed['status']) return $this->finalResultFail(message: $hasRedeemed['message']);
        if ($hasRedeemed['data']) return $this->finalResultFail(message: 'Voucher already redeemed');

        $pointUser = $this->rewardRepository->getPointByUserId($userId);
        if (!$pointUser['status']) return $this->finalResultFail(message: $pointUser['message']);
        if ($pointUser['data'] < $voucher['data']['point_required']) return $this->finalResultFail(message: 'Insufficient point');

        $deductPoint = $this->rewardRepository->deductPoint($userId, $voucher['data']['point_required']);
        if (!$deductPoint['status']) return $this->finalResultFail(message: $deductPoint['message']);

        $redeemVoucher = $this->mainRepository->redeemVoucher($voucher['data']['id'], $userId);
        if (!$redeemVoucher['status']) return $this->finalResultFail(message: $redeemVoucher['message']);

        return $this->finalResultSuccess($voucher['data']);
    }
}
