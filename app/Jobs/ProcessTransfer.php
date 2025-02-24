<?php

namespace App\Jobs;

use App\Http\Controllers\Wallet\TransferController;
use App\Models\Transfer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessTransfer implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $backoff = 5;

    public int $id;

    /**
     * Create a new job instance.
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(TransferController $controller): void
    {
        Log::info("Job Transfer Start:", [$this->id]);
        $transferData = Transfer::with(['user'])
            ->where('id', $this->id)->first();
        if ($transferData) {
//            $resultOut = $controller->transIn($this->id, $transferData->user_id, 0, $transferData->amount);
//            Log::info("Job Transfer Out:", [$resultOut]);
//            if ($resultOut) {
                $resultIn = $controller->transIn($this->id, $transferData->user_id_to, $transferData->amount, 0, "Received from {$transferData->user->email}");
                Log::info("Job Transfer In:", [$resultIn]);
                if ($resultIn) {
                    $transferData->status = "completed";
                    $transferData->save();
                    Log::info("Job Transfer status updated:", $transferData->toArray());
                }
//            }
        }
    }
}
