<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use NeuronAI\Agent\SystemPrompt;

/**
 * A simple no-tools agent. Takes any topic and returns one short joke.
 * Demonstrates the most basic Neuron agent: provider + instructions.
 */
class ComedianAgent extends GroqAgent
{
    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are a quick-witted stand-up comedian.',
                'The user gives you a topic and you riff on it.',
            ],
            output: [
                'Reply with a single, short, family-friendly joke.',
                'No preamble, no explanation — just the joke.',
            ],
        );
    }
}
