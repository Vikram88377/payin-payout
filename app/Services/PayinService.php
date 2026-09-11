<?php

namespace App\Services;

use App\Helpers\TransactionIdGenerator;
use App\Models\Payin;
use Illuminate\Support\Facades\Log;

class PayinService
{
    public function create(array $data): Payin
    {
        $payin = Payin::create([
            'transaction_id' => TransactionIdGenerator::forPayin(),
            'merchant_id' => $data['merchant_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'INR',
            'status' => Payin::STATUS_PENDING,
            'payload' => $data,
        ]);

        Log::channel('payments')->info('Payin initiated', [
            'transaction_id' => $payin->transaction_id,
            'merchant_id' => $payin->merchant_id,
            'amount' => $payin->amount,
        ]);

        return $payin;
    }
}