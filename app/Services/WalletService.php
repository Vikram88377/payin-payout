<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function credit(Wallet $wallet, float $amount, string $referenceType, int $referenceId): void
    {
        DB::transaction(function () use ($wallet, $amount, $referenceType, $referenceId) {
            // lock the row so two cron runs can't update balance at the same time
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $newBalance = $wallet->balance + $amount;

            $wallet->update(['balance' => $newBalance]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            Log::channel('payments')->info('Wallet credited', [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'new_balance' => $newBalance,
            ]);
        });
    }

    public function debit(Wallet $wallet, float $amount, string $referenceType, int $referenceId): void
    {
        DB::transaction(function () use ($wallet, $amount, $referenceType, $referenceId) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $newBalance = $wallet->balance - $amount;

            $wallet->update(['balance' => $newBalance]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'DEBIT',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            Log::channel('payments')->info('Wallet debited', [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'new_balance' => $newBalance,
            ]);
        });
    }
}