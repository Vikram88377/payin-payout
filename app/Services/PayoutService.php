<?php

namespace App\Services;

use App\Helpers\TransactionIdGenerator;
use App\Models\Payout;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    public function create(array $data): Payout
    {
        $payout = Payout::create([
            'transaction_id' => TransactionIdGenerator::forPayout(),
            'merchant_id' => $data['merchant_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'INR',
            'status' => Payout::STATUS_PENDING,
            'payload' => $data,
        ]);

        Log::channel('payments')->info('Payout initiated', [
            'transaction_id' => $payout->transaction_id,
            'merchant_id' => $payout->merchant_id,
            'amount' => $payout->amount,
        ]);

        return $payout;
    }
}