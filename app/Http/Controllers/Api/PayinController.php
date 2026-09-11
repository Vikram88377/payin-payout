<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayinRequest;
use App\Models\Payin;
use App\Services\PayinService;
use Illuminate\Http\JsonResponse;

class PayinController extends Controller
{
    public function __construct(private PayinService $payinService)
    {
    }

    public function store(StorePayinRequest $request): JsonResponse
    {
        $payin = $this->payinService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payin initiated successfully.',
            'data' => [
                'transaction_id' => $payin->transaction_id,
                'status' => $payin->status,
                'amount' => $payin->amount,
            ],
        ], 201);
    }

    public function show(string $transactionId): JsonResponse
    {
        $payin = Payin::where('transaction_id', $transactionId)->first();

        if (! $payin) {
            return response()->json([
                'success' => false,
                'message' => 'Payin not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transaction_id' => $payin->transaction_id,
                'status' => $payin->status,
                'amount' => $payin->amount,
                'created_at' => $payin->created_at,
                'processed_at' => $payin->processed_at,
            ],
        ]);
    }
}