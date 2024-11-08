<?php

namespace App\Services\Exchange;

use LaravelEasyRepository\BaseService;

interface ExchangeService extends BaseService
{

    public function convert(int $userId, string $fromCurrency, string $toCurrency, float $amount, ?string $voucherCode): array;

    public function sendMoney($reqData): array;

    public function storePayment(array $data): array;
}
