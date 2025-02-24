<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

use App\Jobs\ProcessTransfer;
use App\Models\Transfer;

class JobProcessTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_is_dispatched_to_queue()
    {
        Queue::fake();

        $transfer = Transfer::factory()->create();

        ProcessTransfer::dispatch($transfer->id);

        Queue::assertPushed(ProcessTransfer::class, function ($job) use ($transfer) {
            return $job->id === $transfer->id;
        });
    }
}
