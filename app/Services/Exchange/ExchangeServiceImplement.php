<?php

namespace App\Services\Exchange;

use App\Repositories\AppSetting\AppSettingRepository;
use App\Repositories\Exchange\ExchangeRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Traits\ServiceResponser;
use LaravelEasyRepository\Service;

class ExchangeServiceImplement extends Service implements ExchangeService
{

    use ServiceResponser;

    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected ExchangeRepository $mainRepository;
    protected AppSettingRepository $appSettingRepository;
    protected TransactionRepository $transactionRepository;
    protected PaymentRepository $paymentRepository;

    public function __construct(
        ExchangeRepository    $mainRepository,
        AppSettingRepository  $appSettingRepository,
        TransactionRepository $transactionRepository,
        PaymentRepository     $paymentRepository
    )
    {
        $this->mainRepository = $mainRepository;
        $this->appSettingRepository = $appSettingRepository;
        $this->transactionRepository = $transactionRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function convert(string $fromCurrency, string $toCurrency, float $amount): array
    {
        $data = $this->mainRepository->getExchangeRate($fromCurrency, $toCurrency);
        if (!$data['status']) {
            return $this->finalResultFail($data['data'], $data['message']);
        }

        $fee = 0;
        if ($fromCurrency == 'IDR') {
            $fee = $this->appSettingRepository->getValueByKey('exchange_fee');
        }
        $data['data']['from_amount'] = $amount - $fee;
        $data['data']['transfer_fee'] = (float)$fee;
        $data['data']['to_amount'] = floor($amount * $data['data']['exchange_rate'] * 10000) / 10000;

        return $this->finalResultSuccess($data['data']);
    }

    public function sendMoney(int $userId, string $fromCurrency, string $toCurrency, float $amount, string $receiverEmail, ?string $note): array
    {
        $isVerified = $this->mainRepository->isUserVerified($userId);
        if (!$isVerified['status']) {
            return $this->finalResultFail([], 'User not verified, please verify your account');
        }

        $data = $this->convert($fromCurrency, $toCurrency, $amount);
        if (!$data['status']) {
            return $this->finalResultFail($data['data'], $data['message']);
        }


        $getReceiverId = $this->mainRepository->getUserIdByEmail($receiverEmail);
        if (!$getReceiverId['status']) {
            return $this->finalResultFail([], 'Receiver email not found');
        }

        $senderData = [
            'user_id' => $userId, // the owner of the transaction
            'sender_id' => $userId,
            'recipient_id' => $getReceiverId['data'],
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'amount' => $amount,
            'exchange_amount' => $data['data']['to_amount'],
            'exchange_rate' => $data['data']['exchange_rate'],
            'fee' => $data['data']['transfer_fee'],
            'amount_type' => 'send',
            'status' => 'pending',
            'description' => $note,
        ];

        $sender = $this->transactionRepository->insertTransaction($senderData);
        if (!$sender['status']) {
            return $this->finalResultFail($sender['data'], $sender['message']);
        }

        $data['data']['payment_code'] = $sender['data']['transaction_code'];
        return $this->finalResultSuccess($data['data']);
    }

    public function storePayment($data): array
    {
        $getApiKey = $this->appSettingRepository->getValueByKey('api_key');

        if ($data['api_key'] != $getApiKey) {
            return $this->finalResultFail([], 'Invalid API Key');
        }

        $transaction = $this->transactionRepository->getTransactionByCode($data['transaction_code']);
        if (!$transaction['status']) {
            return $this->finalResultFail([], $transaction['message']);
        }

        if ($transaction['data']['status'] != 'pending') {
            return $this->finalResultFail([], 'Transaction already processed');
        }

        if ($data['amount'] != $transaction['data']['amount']) {
            return $this->finalResultFail([], 'Invalid amount');
        }

        $data['user_id'] = $transaction['data']['user_id'];
        $data['status'] = 'success';
        $setPayment = $this->paymentRepository->storePayment($data);
        if (!$setPayment['status']) {
            return $this->finalResultFail([], $setPayment['message']);
        }

        $updateTransaction = $this->transactionRepository->updateTransactionById($transaction['data']['id'], ['status' => 'success']);
        if (!$updateTransaction['status']) {
            return $this->finalResultFail([], $updateTransaction['message']);
        }

        $companyReceiverData = [
            'user_id' => 2, // the owner of the transaction (company)
            'sender_id' => $transaction['data']['sender_id'],
            'recipient_id' => 2,
            'from_currency' => $transaction['data']['from_currency'],
            'to_currency' => $transaction['data']['to_currency'],
            'amount' => $transaction['data']['fee'],
            'exchange_amount' => 0,
            'exchange_rate' => 1,
            'fee' => 0,
            'amount_type' => 'receive',
            'status' => 'success', // 'pending', 'success', 'failed
            'description' => 'Transfer fee',
        ];

        $companyReceiver = $this->transactionRepository->insertTransaction($companyReceiverData);
        if (!$companyReceiver['status']) {
            return $this->finalResultFail($companyReceiver['data'], $companyReceiver['message']);
        }

        $receiverData = [
            'user_id' => $transaction['data']['recipient_id'], // the owner of the transaction
            'sender_id' => $transaction['data']['sender_id'],
            'recipient_id' => $transaction['data']['recipient_id'],
            'from_currency' => $transaction['data']['from_currency'],
            'to_currency' => $transaction['data']['to_currency'],
            'amount' => $transaction['data']['amount'] - $transaction['data']['fee'],
            'exchange_amount' => $transaction['data']['exchange_amount'],
            'exchange_rate' => $transaction['data']['exchange_rate'],
            'fee' => 0,
            'amount_type' => 'receive',
            'status' => 'success', // 'pending', 'success', 'failed
            'description' => $transaction['data']['description'],
        ];

        $receiver = $this->transactionRepository->insertTransaction($receiverData);
        if (!$receiver['status']) {
            return $this->finalResultFail($receiver['data'], $receiver['message']);
        }

        return $this->finalResultSuccess($data);
    }
}
