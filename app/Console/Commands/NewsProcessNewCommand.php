<?php

namespace App\Console\Commands;

use App\Jobs\ProcessBareMessageJob;
use App\Models\BareMessage;
use Illuminate\Console\Command;

class NewsProcessNewCommand extends Command
{
    protected $signature = 'news:process-new';

    protected $description = 'Find unprocessed bare messages and dispatch jobs to process them.';

    public function handle()
    {
        $this->info('Checking for new messages to process...');

        BareMessage::whereNull('processed_at')
            ->orderBy('id')
            ->chunkById(100, function ($messages) {
                $this->info("Found {$messages->count()} new messages. Dispatching jobs...");

                foreach ($messages as $message) {
                    ProcessBareMessageJob::dispatch($message);
                }

                $this->info("Dispatched jobs for chunk. Waiting for next chunk...");
            });

        $this->info('All new messages have been queued for processing.');
        return 0;
    }
}

