<?php

namespace App\Jobs;

use App\Models\BareMessage;
use App\Services\NewsProcessorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBareMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BareMessage $message;

    public function __construct(BareMessage $message)
    {
        $this->message = $message;
    }

    public function handle(NewsProcessorService $processor): void
    {
        $processor->process($this->message);
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("Job failed for BareMessage ID {$this->message->id}: " . $exception->getMessage());
    }
}