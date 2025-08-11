<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class EmbeddingService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected string $model;

    public function __construct()
    {
        $this->model = config('services.huggingface.embedding_model');
        $this->apiUrl = "https://router.huggingface.co/hf-inference/models/{$this->model}/pipeline/feature-extraction";
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
            $response = $this->getClient()->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => [
                    'inputs'  => $text,
                    'options' => [
                        'wait_for_model' => true,
                        'use_cache'      => false,
                    ]
                ],
                'http_errors'    => false,
                'timeout'        => 120,
                'connect_timeout'=> 10,
            ]);

            $status = $response->getStatusCode();
            $body   = (string) $response->getBody();

            if ($status >= 200 && $status < 300) {
                $responseData = json_decode($body, true);

                if (is_array($responseData) && array_is_list($responseData) && isset($responseData[0])) {
                    $embeddingArray = is_array($responseData[0]) ? $responseData[0] : $responseData;
                } else {
                    $embeddingArray = $responseData;
                }

                if (is_array($embeddingArray) && count($embeddingArray) > 0) {
                    return new Vector($embeddingArray);
                }

                Log::error("Invalid embedding format received from HF API for model: {$this->model}");
                return null;
            }

            Log::error("Hugging Face API Error: Status {$status}, Body: {$body}");
            Log::error("Model used: {$this->model}");
            Log::error("API URL: {$this->apiUrl}");

            if ($status === 404) {
                Log::error("Model '{$this->model}' not found. Consider alternatives like 'intfloat/multilingual-e5-base' or 'sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2'.");
            }

            return null;
        } catch (GuzzleException $e) {
            Log::error('Failed to get embedding (HTTP): ' . $e->getMessage());
            Log::error('Model: ' . $this->model);
            return null;
        } catch (\Throwable $e) {
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
            $testText = "این یک متن تست است.";
            $embedding = $this->getEmbedding($testText);
            return $embedding !== null;
        } catch (\Exception $e) {
            Log::error('Model test failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getClient(): Client
    {
        $stack = HandlerStack::create();

        $decider = function (
            int $retries,
            RequestInterface $request,
            ?ResponseInterface $response = null,
            ?\Throwable $exception = null
        ) {
            if ($retries >= 3) {
                return false;
            }
            if ($exception instanceof ConnectException) {
                return true;
            }
            if ($response) {
                $code = $response->getStatusCode();
                if ($code === 429 || ($code >= 500 && $code <= 599)) {
                    return true;
                }
            }
            return false;
        };

        $delay = function (int $retries) {
            return 5000;
        };

        $stack->push(Middleware::retry($decider, $delay));

        return new Client([
            'handler'         => $stack,
            'timeout'         => 120,
            'connect_timeout' => 10,
            'http_errors'     => false,
        ]);
    }

}
