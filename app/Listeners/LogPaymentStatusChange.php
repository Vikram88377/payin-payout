<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\PaymentStatusChanged;
use Illuminate\Support\Facades\Log;
class LogPaymentStatusChange
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
      public function handle(PaymentStatusChanged $event): void
  {
        Log::channel('payments')->info('Payment status changed', [
            'type' => $event->paymentType,
            'transaction_id' => $event->transactionId,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);
    }
}
