<?php

namespace Tests\Feature;

use App\Events\TransferStatusUpdated;
use App\Listeners\TransferStatusLog;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EventTransferLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_status_updated_event_is_fired()
    {
        Event::fake();

        $order = Transfer::factory()->create();
        event(new TransferStatusUpdated($order, $order->status, 'completed'));

        Event::assertDispatched(TransferStatusUpdated::class);
    }
}
