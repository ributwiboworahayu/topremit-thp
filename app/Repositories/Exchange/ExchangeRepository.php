<?php

namespace App\Repositories\Exchange;

use LaravelEasyRepository\Repository;

interface ExchangeRepository extends Repository
{

    public function getExchangeRate(string $fromCurrency, string $toCurrency): array;

    public function getUserIdByEmail(string $email): array;

    public function isUserVerified(int $userId): array;

    public function sendNotification(int $userId): array;
}
