<?php

namespace App\Repositories\Exchange;

use App\Models\Exchange;
use App\Models\User;
use App\Traits\ServiceResponser;
use Illuminate\Support\Facades\Http;
use LaravelEasyRepository\Implementations\Eloquent;

class ExchangeRepositoryImplement extends Eloquent implements ExchangeRepository
{

    use ServiceResponser;

    protected User $userModel;

    public function __construct(
        User $userModel
    )
    {
        $this->userModel = $userModel;
    }

    public function getExchangeRate(string $fromCurrency, string $toCurrency): array
    {
        try {
            $response = Http::get(env('EXCHANGE_RATE_API_URL') . $fromCurrency)->json();

            if ($response['result'] == 'success') {
                $exchangeRate = $response['conversion_rates'][$toCurrency];
                $formattedExchangeRate = number_format($exchangeRate, 8, '.', ',');
                $data = [
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency,
                    'exchange_rate' => $response['conversion_rates'][$toCurrency],
                    'description' => "Exchange rate from $fromCurrency to $toCurrency is $formattedExchangeRate"
                ];
                return $this->finalResultSuccess($data);
            }

            return $this->finalResultFail([], $response['error-type']);
        } catch (\Exception $e) {
            return $this->finalResultFail([], $e->getMessage());
        }

    }

    public function getUserIdByEmail(string $email): array
    {
        $user = $this->userModel->where('email', $email)->first();
        if (!$user) {
            return $this->finalResultFail([], 'User not found');
        }

        return $this->finalResultSuccess($user->id);
    }

    public function isUserVerified(int $userId): array
    {
        $user = $this->userModel->find($userId);
        if (!(bool)$user->is_verified) {
            return $this->finalResultFail([], 'User not verified');
        }

        return $this->finalResultSuccess($user->is_verified);
    }
}
