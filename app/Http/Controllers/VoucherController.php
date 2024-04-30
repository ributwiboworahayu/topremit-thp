<?php

namespace App\Http\Controllers;

use App\Http\Requests\RedeemRequest;
use App\Services\Voucher\VoucherService;
use Illuminate\Http\JsonResponse;

class VoucherController extends Controller
{

    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function index(): JsonResponse
    {
        $vouchers = $this->voucherService->index();
        return $this->successResponse($vouchers['data']);
    }

    public function redeem(RedeemRequest $request): JsonResponse
    {
        $voucherCode = $request->voucher_code;
        $userId = auth()->id();
        $result = $this->voucherService->redeem($voucherCode, $userId);

        if (!$result['status']) return $this->failResponse($result['data'], 200, $result['message']);
        return $this->successResponse($result['data']);
    }
}
