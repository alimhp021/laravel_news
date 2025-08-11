<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

class EmbeddingService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected string $model;

    public function __construct()
    {
        $this->model = config('services.huggingface.embedding_model');
        $this->apiUrl = "https://api-inference.huggingface.co/models/{$this->model}";
        $this->apiToken = config('services.huggingface.api_token');
    }

    /**
     * Generates a vector embedding for the given text.
     *
     * @param string $text
     * @return Vector|null
     */
    public function getEmbedding(string $text): ?Vector
    {
        try {
            // First, check if the model is loaded
            $response = Http::withToken($this->apiToken)
                ->timeout(120) // Increased timeout for model loading
                ->retry(3, 5000) // Retry 3 times with 5 second delay
                ->post($this->apiUrl, [
                    'inputs' => $text, // Some models expect string, others array
                    'options' => [
                        'wait_for_model' => true,
                        'use_cache' => false
                    ]
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Handle different response formats
                if (is_array($responseData) && isset($responseData[0])) {
                    // Response is array of embeddings
                    $embeddingArray = is_array($responseData[0]) ? $responseData[0] : $responseData;
                } else {
                    // Response is direct embedding array
                    $embeddingArray = $responseData;
                }

                if (is_array($embeddingArray) && count($embeddingArray) > 0) {
                    return new Vector($embeddingArray);
                }

                Log::error("Invalid embedding format received from Hugging Face API for model: {$this->model}");
                return null;
            }

            // Log detailed error information
            $errorBody = $response->body();
            $statusCode = $response->status();
            
            Log::error("Hugging Face API Error: Status {$statusCode}, Body: {$errorBody}");
            Log::error("Model used: {$this->model}");
            Log::error("API URL: {$this->apiUrl}");

            // If model not found, suggest alternatives
            if ($statusCode === 404) {
                Log::error("Model '{$this->model}' not found. Consider using alternative Persian models like 'HooshvareLab/bert-base-parsbert-uncased' or 'sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2'");
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to get embedding: ' . $e->getMessage());
            Log::error('Model: ' . $this->model);
            return null;
        }
    }

    /**
     * Test the model availability
     *
     * @return bool
     */
    public function testModel(): bool
    {
        try {
            $testText = "این یک متن تست است."; // "This is a test text." in Persian
            $embedding = $this->getEmbedding($testText);
            return $embedding !== null;
        } catch (\Exception $e) {
            Log::error('Model test failed: ' . $e->getMessage());
            return false;
        }
    }
}