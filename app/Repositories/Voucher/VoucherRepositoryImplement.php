<?php

namespace App\Repositories\Voucher;

use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Traits\ServiceResponser;
use Illuminate\Database\Eloquent\Model;
use LaravelEasyRepository\Implementations\Eloquent;

class VoucherRepositoryImplement extends Eloquent implements VoucherRepository
{

    use ServiceResponser;

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected Voucher $model;
    protected UserVoucher $userVoucher;

    public function __construct(
        Voucher     $model,
        UserVoucher $userVoucher
    )
    {
        $this->model = $model;
        $this->userVoucher = $userVoucher;
    }

    public function hasRedeemedVoucherId($voucherId, $userId): array
    {
        // get redeem limit voucher
        $voucher = $this->model->find($voucherId);
        if (!$voucher) return $this->finalResultFail(message: 'Voucher not found');
        $totalVoucher = $this->userVoucher->where('voucher_id', $voucherId)->where('user_id', $userId)->count();
        if ($voucher->redeem_limit >= $totalVoucher) return $this->finalResultSuccess(false);

        return $this->finalResultSuccess(true);
    }

    public function findByCode($code): array
    {
        $voucher = $this->model->where('code', $code)->first();
        if (!$voucher) return $this->finalResultFail(message: 'Voucher not found');
        if ($voucher->start_date > now()) return $this->finalResultFail(message: 'Voucher not started yet');
        if ($voucher->stock == 0) return $this->finalResultFail(message: 'Voucher out of stock');
        if ($voucher->expired_date < now()) return $this->finalResultFail(message: 'Voucher expired');
        return $this->finalResultSuccess($voucher);
    }

    public function redeemVoucher($voucherId, $userId): array
    {
        // update stock
        $voucher = $this->model->find($voucherId);
        if ($voucher->stock == 0) return $this->finalResultFail(message: 'Voucher out of stock');

        $voucher->stock = $voucher->stock - 1;
        $voucher->total_redeemed = $voucher->total_redeemed + 1;

        // insert user voucher
        $userVoucher = $this->userVoucher->create([
            'user_id' => $userId,
            'voucher_id' => $voucherId,
            'redeemed_at' => now(),
            'redeemed_point' => $voucher->point_required,
        ]);
        $voucher = $voucher->save();
        return $this->finalResultSuccess($voucher);
    }

    public function useVoucher($userId, $voucherCode): array
    {
        $userVouchers = $this->userVoucher->where('user_id', $userId)
            ->whereHas('voucher', function ($query) use ($voucherCode) {
                $query->where('code', $voucherCode);
            })
            ->where('is_expired', false)
            ->where('is_used', false)->first();

        if (!$userVouchers) return $this->finalResultFail(message: 'Voucher not found or already used');

        $userVouchers->is_used = true;
        $userVouchers->used_at = now();
        $userVouchers->save();

        return $this->finalResultSuccess($userVouchers);

    }

    public function getAvailableVoucherForUser($voucherId, $userId): array
    {
        $totalVoucher = $this->userVoucher->where('voucher_id', $voucherId)
            ->where('user_id', $userId)
            ->where('is_expired', false)
            ->where('is_expired', false)
            ->count();
        if ($totalVoucher <= 0) return $this->finalResultSuccess(false);

        return $this->finalResultSuccess(true);
    }
}
