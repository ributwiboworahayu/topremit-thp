<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExchangeRequest;
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
        $fromCurrency = $request->from_currency;
        $toCurrency = $request->to_currency;
        $amount = $request->amount;
        $result = $this->exchangeService->convert($fromCurrency, $toCurrency, $amount);
        if (!$result['status']) {
            return $this->failResponse(message: $result['message']);
        }

        return $this->successResponse($result['data']);
    }

    public function sendMoney(SendMoneyRequest $request): JsonResponse
    {
        $fromCurrency = $request->from_currency;
        $toCurrency = $request->to_currency;
        $amount = $request->amount;
        $receiverEmail = $request->receiver_email;
        $note = $request->note;
        $result = $this->exchangeService->sendMoney(
            userId: auth()->id(),
            fromCurrency: $fromCurrency,
            toCurrency: $toCurrency,
            amount: $amount,
            receiverEmail: $receiverEmail,
            note: $note
        );
        if (!$result['status']) {
            return $this->failResponse(message: $result['message']);
        }

        return $this->successResponse($result['data']);
    }
}
