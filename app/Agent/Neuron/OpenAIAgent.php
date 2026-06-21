<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use NeuronAI\Agent\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAILike;

/**
 * Base agent wired to OpenAI's chat/completions API.
 */
abstract class OpenAIAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAILike(
            baseUri: (string) config('services.openai.base_url'),
            key: (string) config('services.openai.api_key'),
            model: (string) config('services.openai.model'),
        );
    }
}
