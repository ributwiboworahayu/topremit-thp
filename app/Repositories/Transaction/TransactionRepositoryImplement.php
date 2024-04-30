<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction;
use App\Traits\ServiceResponser;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LaravelEasyRepository\Implementations\Eloquent;

class TransactionRepositoryImplement extends Eloquent implements TransactionRepository
{

    use ServiceResponser;

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected Transaction $model;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function insertTransaction(array $data): array
    {
        try {
            $data = $this->model->create([
                'user_id' => $data['user_id'],
                'sender_id' => $data['sender_id'],
                'recipient_id' => $data['recipient_id'],
                'transaction_code' => $this->setUniqueCode(),
                'from_currency' => $data['from_currency'],
                'to_currency' => $data['to_currency'],
                'amount' => $data['amount'],
                'exchange_amount' => $data['exchange_amount'],
                'exchange_rate' => $data['exchange_rate'],
                'fee' => $data['fee'],
                'amount_type' => $data['amount_type'],
                'user_voucher_id' => $data['user_voucher_id'] ?? null,
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
            ]);

            return $this->finalResultSuccess($data);
        } catch (Exception $exception) {
            return $this->finalResultFail([], $exception->getMessage());
        }
    }

    private function setUniqueCode(): string
    {
        $code = Str::uuid()->toString();
        if ($this->model->where('transaction_code', $code)->exists()) {
            return $this->setUniqueCode();
        }
        return $code;
    }

    public function getTransactionByCode(string $code): array
    {
        $data = $this->model->where('transaction_code', $code)->first();
        if (!$data) {
            return $this->finalResultFail([], 'Transaction not found');
        }

        return $this->finalResultSuccess($data);
    }

    public function updateTransactionById(int $id, array $data): array
    {
        $transaction = $this->model->find($id);
        if (!$transaction) {
            return $this->finalResultFail([], 'Transaction not found');
        }

        try {
            $transaction->update($data);
            return $this->finalResultSuccess($transaction);
        } catch (Exception $exception) {
            return $this->finalResultFail([], $exception->getMessage());
        }
    }
}
