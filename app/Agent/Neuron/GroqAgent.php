<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use NeuronAI\Agent\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAILike;

/**
 * Base agent wired to Groq.
 *
 * Groq exposes an OpenAI-compatible API, so we use Neuron's OpenAILike
 * provider and point it at the Groq base URL. Concrete agents only need
 * to define their own instructions().
 */
abstract class GroqAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAILike(
            baseUri: (string) config('services.groq.base_url'),
            key: (string) config('services.groq.api_key'),
            model: (string) config('services.groq.model'),
        );
    }
}
