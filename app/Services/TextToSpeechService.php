<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Text-to-speech via the OpenAI speech API (POST /audio/speech).
 * Returns raw audio bytes the caller can stream or persist.
 */
class TextToSpeechService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.openai.com/v1',
        private readonly string $model = 'gpt-4o-mini-tts',
        private readonly string $voice = 'alloy',
        private readonly string $format = 'mp3',
        private readonly int $timeout = 120,
    ) {}

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Synthesize speech from text and return the raw audio bytes.
     *
     * @param  string|null  $voice  Override the default voice (alloy, echo, fable, …).
     * @param  string|null  $format  Override the default format (mp3, opus, aac, flac, wav, pcm).
     */
    public function synthesize(string $text, ?string $voice = null, ?string $format = null): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OPENAI_API_KEY is not set.');
        }

        $text = trim($text);

        if ($text === '') {
            throw new RuntimeException('Cannot synthesize empty text.');
        }

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/audio/speech', [
                'model' => $this->model,
                'input' => $text,
                'voice' => $voice ?? $this->voice,
                'response_format' => $format ?? $this->format,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'OpenAI speech synthesis failed ('.$response->status().'): '.$response->body()
            );
        }

        return $response->body();
    }

    /**
     * Synthesize speech and write it to a file, returning the path.
     */
    public function synthesizeToFile(string $text, string $path, ?string $voice = null, ?string $format = null): string
    {
        file_put_contents($path, $this->synthesize($text, $voice, $format));

        return $path;
    }

    /**
     * The HTTP content type for a given (or the default) audio format.
     */
    public function mimeType(?string $format = null): string
    {
        return match ($format ?? $this->format) {
            'mp3' => 'audio/mpeg',
            'opus' => 'audio/ogg',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
            'wav' => 'audio/wav',
            'pcm' => 'audio/pcm',
            default => 'application/octet-stream',
        };
    }
}
