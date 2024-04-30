<?php

namespace App\Services\Exchange;

use App\Repositories\AppSetting\AppSettingRepository;
use App\Repositories\Exchange\ExchangeRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Repositories\Reward\RewardRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Repositories\Voucher\VoucherRepository;
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
    protected RewardRepository $rewardRepository;
    protected VoucherRepository $voucherRepository;

    public function __construct(
        ExchangeRepository    $mainRepository,
        AppSettingRepository  $appSettingRepository,
        TransactionRepository $transactionRepository,
        PaymentRepository     $paymentRepository,
        RewardRepository      $rewardRepository,
        VoucherRepository     $voucherRepository
    )
    {
        $this->mainRepository = $mainRepository;
        $this->appSettingRepository = $appSettingRepository;
        $this->transactionRepository = $transactionRepository;
        $this->paymentRepository = $paymentRepository;
        $this->rewardRepository = $rewardRepository;
        $this->voucherRepository = $voucherRepository;
    }

    public function convert(int $userId, string $fromCurrency, string $toCurrency, float $amount, ?string $voucherCode): array
    {
        $data = $this->getExchangeRate($fromCurrency, $toCurrency);
        if (!$data['status']) {
            return $this->finalResultFail($data['data'], $data['message']);
        }

        $fee = 0;
        if ($fromCurrency == 'IDR') {
            $fee = $this->appSettingRepository->getValueByKey('exchange_fee');
        }

        $discount = 0;
        if ($voucherCode) {
            $getVoucherDiscount = $this->getVoucherDiscount($userId, $voucherCode, $amount);
            if (!$getVoucherDiscount['status']) {
                return $this->finalResultFail([], $getVoucherDiscount['message']);
            }

            $discount = $getVoucherDiscount['data'];
        }

        $data['data']['from_amount'] = ($amount - $fee) - $discount;
        $data['data']['transfer_fee'] = (float)$fee;
        $data['data']['to_amount'] = floor(($amount - $fee) * $data['data']['exchange_rate'] * 10000) / 10000;

        return $this->finalResultSuccess($data['data']);
    }

    public function sendMoney($reqData): array
    {
        $isVerified = $this->mainRepository->isUserVerified($reqData['user_id']);
        if (!$isVerified['status']) {
            return $this->finalResultFail([], 'User not verified, please verify your account');
        }

        $data = $this->getExchangeRate($reqData['from_currency'], $reqData['to_currency']);

        $fee = 0;
        if ($reqData['from_currency'] == 'IDR') {
            $fee = $this->appSettingRepository->getValueByKey('exchange_fee');
        }

        if ($reqData['to_currency'] == 'AUD' && $reqData['address'] == null) {
            return $this->finalResultFail([], 'Address Needed for AUS');
        }


        // check use voucher
        $discount = 0;
        if ($reqData['voucher_code']) {
            $getVoucherDiscount = $this->getVoucherDiscount($reqData['user_id'], $reqData['voucher_code'], $reqData['amount']);
            if (!$getVoucherDiscount['status']) {
                return $this->finalResultFail([], $getVoucherDiscount['message']);
            }

            $usedVoucher = $this->voucherRepository->useVoucher($reqData['user_id'], $reqData['voucher_code']);
            if (!$usedVoucher['status']) {
                return $this->finalResultFail([], $usedVoucher['message']);
            }

            $discount = $getVoucherDiscount['data'];
            $reqData['amount'] -= $discount;
        }

        $data['data']['from_amount'] = ($reqData['amount'] - $fee);
        $data['data']['transfer_fee'] = (float)$fee;
        $data['data']['to_amount'] = floor((($reqData['amount'] - $fee) + $discount) * $data['data']['exchange_rate'] * 10000) / 10000;

        $getReceiverId = $this->mainRepository->getUserIdByEmail($reqData['receiver_email']);
        if (!$getReceiverId['status']) {
            return $this->finalResultFail([], 'Receiver email not found');
        }

        $senderData = [
            'user_id' => $reqData['user_id'],
            'sender_id' => $reqData['user_id'],
            'recipient_id' => $getReceiverId['data'],
            'from_currency' => $reqData['from_currency'],
            'to_currency' => $reqData['to_currency'],
            'amount' => $reqData['amount'],
            'exchange_amount' => $data['data']['to_amount'],
            'exchange_rate' => $data['data']['exchange_rate'],
            'fee' => $data['data']['transfer_fee'],
            'amount_type' => 'send',
            'user_voucher_id' => $usedVoucher['data']['id'] ?? null,
            'status' => 'pending',
            'description' => $reqData['address'] ?? $reqData['note'],
        ];

        $sender = $this->transactionRepository->insertTransaction($senderData);
        if (!$sender['status']) {
            return $this->finalResultFail($sender['data'], $sender['message']);
        }

        $data['data']['payment_code'] = $sender['data']['transaction_code'];
        $data['data']['payment_amount'] = $reqData['amount'];
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

        // insert reward
        $rewardData = [
            'user_id' => $transaction['data']['user_id'],
            'point' => $transaction['data']['amount'] / 1000,
        ];
        $reward = $this->rewardRepository->insertReward($rewardData);
        if (!$reward['status']) {
            return $this->finalResultFail($reward['data'], $reward['message']);
        }

        // send notif
        dispatch(function () use ($transaction) {
            $this->mainRepository->sendNotification($transaction['data']['user_id']);
        });

        return $this->finalResultSuccess();
    }

    private function getVoucherDiscount($userId, $voucherCode, $amount): array
    {
        $voucher = $this->voucherRepository->findByCode($voucherCode);
        if (!$voucher['status']) {
            return $this->finalResultFail([], $voucher['message']);
        }

        $availableVoucher = $this->voucherRepository->getAvailableVoucherForUser($voucher['data']['id'], $userId);
        if (!$availableVoucher['status']) {
            return $this->finalResultFail([], $availableVoucher['message']);
        }
        if (!$availableVoucher['data']) {
            return $this->finalResultFail([], 'Voucher Not Redeemed');
        }

        $discount = ($voucher['data']['discount_percentage'] / 100) * $amount;

        return $this->finalResultSuccess($discount);
    }

    private function getExchangeRate(string $fromCurrency, string $toCurrency): array
    {
        $response = $this->mainRepository->getExchangeRate($fromCurrency, $toCurrency);
        if (!$response['status']) {
            return $this->finalResultFail([], $response['message']);
        }

        return $this->finalResultSuccess($response['data']);
    }
}
