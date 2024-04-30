<?php

namespace App\Repositories\Payment;

use App\Models\Payment;
use App\Traits\ServiceResponser;
use Exception;
use Illuminate\Database\Eloquent\Model;
use LaravelEasyRepository\Implementations\Eloquent;

class PaymentRepositoryImplement extends Eloquent implements PaymentRepository
{
    use ServiceResponser;

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected Payment $model;

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }

    public function storePayment(array $data): array
    {
        try {
            $data = $this->model->create([
                'user_id' => $data['user_id'],
                'payment_code' => $data['transaction_code'],
                'payment_method' => $data['payment_method'],
                'payment_account' => $data['payment_account'],
                'amount' => $data['amount'],
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
            ]);

            return $this->finalResultSuccess($data);
        } catch (Exception $exception) {
            return $this->finalResultFail([], $exception->getMessage());
        }

    }
}
