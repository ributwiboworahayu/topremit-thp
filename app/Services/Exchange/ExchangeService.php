<?php

namespace App\Services\Exchange;

use LaravelEasyRepository\BaseService;

interface ExchangeService extends BaseService
{

    public function convert(string $fromCurrency, string $toCurrency, float $amount): array;

    public function sendMoney(int $userId, string $fromCurrency, string $toCurrency, float $amount, string $receiverEmail, ?string $note): array;
}
