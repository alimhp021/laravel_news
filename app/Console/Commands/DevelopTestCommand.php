<?php

namespace App\Console\Commands;

use App\Models\BareMessage;
use App\Models\UniqueNews;
use App\Services\NewsProcessorService;
use Illuminate\Console\Command;

class DevelopTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(NewsProcessorService $service)
    {
        dd(
            [
                'unique_news' => UniqueNews::findOrFail(3)->original_text,
                'bare' => BareMessage::find(10)->message_text
            ]
        );
        dd(UniqueNews::all()->toArray());
        $service->process(BareMessage::find(31));
    }
}
