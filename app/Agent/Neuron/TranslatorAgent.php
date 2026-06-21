<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use NeuronAI\Agent\SystemPrompt;

/**
 * A second simple agent that translates whatever it receives into Albanian.
 * Same provider, different instructions — shows how little it takes to
 * spin up a distinct agent.
 */
class TranslatorAgent extends GroqAgent
{
    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are a professional translator.',
                'You translate any text the user sends into Albanian.',
            ],
            output: [
                'Return only the Albanian translation.',
                'Do not add notes, quotes, or the original text.',
            ],
        );
    }
}
