<?php

namespace App\Console\Commands;

use App\Events\PaymentStatusChanged;
use App\Models\Payin;
use App\Models\Payout;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPendingPayments extends Command
{
    protected $signature = 'payments:process-pending';

    protected $description = 'Picks up PENDING payins/payouts and randomly marks them SUCCESS, FAILED or PENDING';

    public function __construct(private WalletService $walletService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Processing pending payins...');
        $this->processPayins();

        $this->info('Processing pending payouts...');
        $this->processPayouts();

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function processPayins(): void
    {
        Payin::where('status', Payin::STATUS_PENDING)
            ->chunkById(50, function ($payins) {
                foreach ($payins as $payin) {
                    try {
                        $this->processOnePayin($payin->id);
                    } catch (Throwable $e) {
                        Log::channel('payments')->error('Failed to process payin', [
                            'payin_id' => $payin->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }

    private function processOnePayin(int $payinId): void
    {
        DB::transaction(function () use ($payinId) {
            $payin = Payin::where('id', $payinId)->lockForUpdate()->first();

            if (! $payin || $payin->status !== Payin::STATUS_PENDING) {
                return;
            }

            $oldStatus = $payin->status;
            $newStatus = $this->randomStatus(); // Returns lowercase 'success', 'failed', 'pending'
            $merchant = $payin->merchant()->with('wallet')->first();

            // Credit wallet ONLY if status becomes SUCCESS
            if ($newStatus === Payin::STATUS_SUCCESS) {
                if ($merchant && $merchant->wallet && ! $payin->wallet_credited) {
                    $this->walletService->credit(
                        $merchant->wallet,
                        (float) $payin->amount,
                        'payin',
                        $payin->id
                    );

                    $payin->wallet_credited = true;
                }
            }

            if ($newStatus !== Payin::STATUS_PENDING) {
                $payin->status = $newStatus;
                $payin->processed_at = now();
            }

            $payin->save();

            if ($newStatus !== Payin::STATUS_PENDING) {
                PaymentStatusChanged::dispatch('payin', $payin->transaction_id, $oldStatus, $newStatus);
            }
        });
    }

    private function processPayouts(): void
    {
        Payout::where('status', Payout::STATUS_PENDING)
            ->chunkById(50, function ($payouts) {
                foreach ($payouts as $payout) {
                    try {
                        $this->processOnePayout($payout->id);
                    } catch (Throwable $e) {
                        Log::channel('payments')->error('Failed to process payout', [
                            'payout_id' => $payout->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }

    private function processOnePayout(int $payoutId): void
    {
        DB::transaction(function () use ($payoutId) {
            $payout = Payout::where('id', $payoutId)->lockForUpdate()->first();

            if (! $payout || $payout->status !== Payout::STATUS_PENDING) {
                return;
            }

            $oldStatus = $payout->status;
            $newStatus = $this->randomStatus();
            $merchant = $payout->merchant()->with('wallet')->first();

            // Debit wallet ONLY if status resolves to SUCCESS and balance is sufficient
            if ($newStatus === Payout::STATUS_SUCCESS) {
                if ($merchant && $merchant->wallet && $merchant->wallet->balance >= $payout->amount) {
                    if (! $payout->wallet_debited) {
                        $this->walletService->debit(
                            $merchant->wallet,
                            (float) $payout->amount,
                            'payout',
                            $payout->id
                        );

                        $payout->wallet_debited = true;
                    }
                } else {
                    $newStatus = Payout::STATUS_FAILED;
                    $payout->failure_reason = 'Insufficient wallet balance or wallet missing';
                }
            }

            if ($newStatus !== Payout::STATUS_PENDING) {
                $payout->status = $newStatus;
                $payout->processed_at = now();
            }

            $payout->save();

            if ($newStatus !== Payout::STATUS_PENDING) {
                PaymentStatusChanged::dispatch('payout', $payout->transaction_id, $oldStatus, $newStatus);
            }
        });
    }

    private function randomStatus(): string
    {
        // Lowercase to match standard Laravel model status constants ('success', 'failed', 'pending')
        $options = ['success', 'failed', 'pending'];

        return $options[array_rand($options)];
    }
}