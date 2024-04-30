<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExchangeRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\SendMoneyRequest;
use App\Services\Exchange\ExchangeService;
use Illuminate\Http\JsonResponse;

class ExchangeController extends Controller
{
    protected ExchangeService $exchangeService;

    public function __construct(ExchangeService $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    public function index(ExchangeRequest $request): JsonResponse
    {
        $fromCurrency = 'IDR'; // default currency is 'IDR
        $userId = auth()->id();
        $toCurrency = $request->to_currency;
        $amount = $request->amount;
        $voucherCode = $request->voucher_code;
        $result = $this->exchangeService->convert($userId, $fromCurrency, $toCurrency, $amount, $voucherCode);
        if (!$result['status']) {
            return $this->failResponse(message: $result['message']);
        }

        return $this->successResponse($result['data']);
    }

    public function sendMoney(SendMoneyRequest $request): JsonResponse
    {
        $fromCurrency = 'IDR'; // default currency is 'IDR
        $toCurrency = $request->to_currency;
        $amount = $request->amount;
        $receiverEmail = $request->receiver_email;
        $note = $request->note;
        $voucherCode = $request->voucher_code;
        $result = $this->exchangeService->sendMoney(
            userId: auth()->id(),
            fromCurrency: $fromCurrency,
            toCurrency: $toCurrency,
            amount: $amount,
            receiverEmail: $receiverEmail,
            note: $note,
            voucherCode: $voucherCode
        );
        if (!$result['status']) {
            return $this->failResponse(message: $result['message']);
        }

        return $this->successResponse($result['data']);
    }

    public function storePayment(PaymentRequest $request)
    {
        $requestData = $request->all();
        $result = $this->exchangeService->storePayment($requestData);
        if (!$result['status']) {
            return $this->failResponse(message: $result['message']);
        }

        return $this->successResponse($result['data']);
    }
}
