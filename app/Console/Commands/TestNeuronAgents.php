<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Agent\Neuron\ComedianAgent;
use App\Agent\Neuron\TranslatorAgent;
use Illuminate\Console\Command;
use NeuronAI\Chat\Messages\UserMessage;

class TestNeuronAgents extends Command
{
    protected $signature = 'agent:test {message=Tell me about Tirana}';

    protected $description = 'Run the two sample Neuron AI agents against a message';

    public function handle(): int
    {
        $message = (string) $this->argument('message');

        $this->line("Input: {$message}");
        $this->newLine();

        // Each agent: build it (::make), send a UserMessage via chat(),
        // then getMessage()->getContent() blocks until done and returns text.
        $joke = ComedianAgent::make()
            ->chat(new UserMessage($message))
            ->getMessage()
            ->getContent();

        $this->info('🤡 Comedian:');
        $this->line((string) $joke);
        $this->newLine();

        $translation = TranslatorAgent::make()
            ->chat(new UserMessage($message))
            ->getMessage()
            ->getContent();

        $this->info('🌐 Translator (Albanian):');
        $this->line((string) $translation);

        return self::SUCCESS;
    }
}
