<?php

namespace App\Repositories\Voucher;

use LaravelEasyRepository\Repository;

interface VoucherRepository extends Repository
{

    public function findByCode($code): array;

    public function hasRedeemedVoucherId($voucherId, $userId): array;

    public function redeemVoucher($voucherId, $userId): array;

    public function useVoucher($userId, $voucherCode): array;

    public function getAvailableVoucherForUser($voucherId, $userId): array;

}
