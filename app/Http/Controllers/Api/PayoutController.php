<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayoutRequest;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;

class PayoutController extends Controller
{
    public function __construct(private PayoutService $payoutService)
    {
    }

    public function store(StorePayoutRequest $request): JsonResponse
    {
        $payout = $this->payoutService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payout initiated successfully.',
            'data' => [
                'transaction_id' => $payout->transaction_id,
                'status' => $payout->status,
                'amount' => $payout->amount,
            ],
        ], 201);
    }

    public function show(string $transactionId): JsonResponse
    {
        $payout = Payout::where('transaction_id', $transactionId)->first();

        if (! $payout) {
            return response()->json([
                'success' => false,
                'message' => 'Payout not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transaction_id' => $payout->transaction_id,
                'status' => $payout->status,
                'amount' => $payout->amount,
                'created_at' => $payout->created_at,
                'processed_at' => $payout->processed_at,
            ],
        ]);
    }
}