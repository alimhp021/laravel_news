<?php

namespace App\Console\Commands;

use App\Services\AI\EmbeddingService;
use Illuminate\Console\Command;

class TestEmbedding extends Command
{
    protected $signature = 'embedding:test {--text=این یک متن تست است}';
    protected $description = 'Test the embedding service with Persian text';

    public function handle(EmbeddingService $embeddingService): int
    {
        $text = $this->option('text');
        
        $this->info("Testing embedding service with text: {$text}");
        $this->info("Model: " . config('services.huggingface.embedding_model'));
        
        $embedding = $embeddingService->getEmbedding($text);
        
        if ($embedding) {
            $this->info("✅ Embedding generated successfully!");
            $this->info("Vector dimension: " . count($embedding->toArray()));
            $this->line("First 5 values: " . implode(', ', array_slice($embedding->toArray(), 0, 5)));
        } else {
            $this->error("❌ Failed to generate embedding");
            return 1;
        }

        return 0;
    }
}