<?php

namespace App\Services;

use App\Models\BareMessage;
use App\Models\DuplicateLink;
use App\Models\UniqueNews;
use App\Services\AI\EmbeddingService;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Distance;

class NewsProcessorService
{
    protected EmbeddingService $embeddingService;
    protected float $similarityThreshold;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
        $this->similarityThreshold = (float) env('NEWS_SIMILARITY_THRESHOLD', 0.15);
    }

    /**
     * Process a single bare message to check if it's unique.
     *
     * @param BareMessage $message
     * @return void
     */
    public function process(BareMessage $message): void
    {
        Log::info("Processing message ID: {$message->id}");

        $embedding = $this->embeddingService->getEmbedding($message->message_text);

        if (!$embedding) {
            Log::error("Could not generate embedding for message ID: {$message->id}. Skipping.");
            return;
        }

        // Corrected line: Use the explicit cosineDistance method
        $mostSimilar = UniqueNews::query()
            ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
            ->first();

        $distance = $mostSimilar ? $mostSimilar->distance : 2.0;

        if ($distance < $this->similarityThreshold) {
            Log::info("Found duplicate for message ID: {$message->id}. Similar to UniqueNews ID: {$mostSimilar->id} with distance: {$distance}");
            DuplicateLink::create([
                'bare_message_id' => $message->id,
                'unique_news_id' => $mostSimilar->id,
                'similarity_score' => 1 - $distance,
            ]);
        } else {
            Log::info("Creating new unique story from message ID: {$message->id}. Nearest distance was: {$distance}");
            UniqueNews::create([
                'source_message_id' => $message->id,
                'original_text' => $message->message_text,
                'embedding' => $embedding,
            ]);
        }

        $message->update(['processed_at' => now()]);
    }
}
