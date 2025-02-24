<?php

namespace App\Listeners;

use App\Events\TransferStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TransferStatusLog
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
    public function handle(TransferStatusUpdated $event)
    {
        Log::info("Transaction #{$event->transaction->id} status changed from {$event->oldStatus} to {$event->newStatus}");
    }
}
