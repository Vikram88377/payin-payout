<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;

class MerchantController extends Controller
{
    public function wallet(int $id): JsonResponse
    {
        $merchant = Merchant::with('wallet')->find($id);

        if (! $merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'merchant_id' => $merchant->id,
                'merchant_name' => $merchant->name,
                'balance' => optional($merchant->wallet)->balance ?? 0,
            ],
        ]);
    }
}