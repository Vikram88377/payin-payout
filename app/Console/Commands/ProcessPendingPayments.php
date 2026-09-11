<?php

namespace App\Console\Commands;

use App\Events\PaymentStatusChanged;
use App\Models\Payin;
use App\Models\Payout;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
                    $this->processOnePayin($payin->id);
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
            $newStatus = $this->randomStatus();
            $merchant = $payin->merchant()->with('wallet')->first();

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
                    $this->processOnePayout($payout->id);
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
                    $payout->failure_reason = 'Insufficient wallet balance';
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
        $options = ['success', 'failed', 'pending'];

        return $options[array_rand($options)];
    }
}