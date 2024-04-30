<?php

namespace App\Services\Voucher;

use LaravelEasyRepository\BaseService;

interface VoucherService extends BaseService
{

    public function index(): array;

    public function redeem($voucherCode, $userId): array;
}
