<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.groq.com/openai/v1',
        private readonly string $model = 'openai/gpt-oss-20b',
        private readonly int $timeout = 60,
    ) {}

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Send a chat conversation to Groq and return the assistant's reply.
     *
     * @param  list<array<string, mixed>>  $messages
     */
    public function chat(array $messages): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GROQ_API_KEY is not set.');
        }

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl.'/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Groq request failed ('.$response->status().'): '.$response->body()
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new RuntimeException('Unexpected response from Groq.');
        }

        return trim($content);
    }

    /**
     * Send a conversation plus tool definitions and return the raw assistant
     * message (which may contain `tool_calls`).
     *
     * @param  list<array<string, mixed>>  $messages
     * @param  list<array<string, mixed>>  $tools
     * @return array<string, mixed>
     */
    public function chatWithTools(array $messages, array $tools): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GROQ_API_KEY is not set.');
        }

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl.'/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'tools' => $tools,
                'tool_choice' => 'auto',
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Groq request failed ('.$response->status().'): '.$response->body()
            );
        }

        $message = $response->json('choices.0.message');

        if (! is_array($message)) {
            throw new RuntimeException('Unexpected response from Groq.');
        }

        return $message;
    }
}
