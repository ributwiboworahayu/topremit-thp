<?php

namespace App\Repositories\Transaction;

use LaravelEasyRepository\Repository;

interface TransactionRepository extends Repository
{

    public function insertTransaction(array $data): array;

    public function getTransactionByCode(string $code): array;

    public function updateTransactionById(int $id, array $data): array;
}
