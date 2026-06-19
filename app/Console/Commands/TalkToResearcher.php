<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Agent\Neuron\ResearcherAgent;
use Illuminate\Console\Command;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\Events\ToolCalling;
use NeuronAI\Observability\ObserverInterface;

class TalkToResearcher extends Command
{
    protected $signature = 'agent:researcher';

    protected $description = 'Have a live conversation with the Researcher agent in the terminal';

    public function handle(): int
    {
        $agent = ResearcherAgent::make();

        // Attach an observer so we can SEE the tool-call loop as it happens.
        $agent->observe($this->toolWatcher());

        $this->info('Researcher agent ready. Ask a question (type "exit" to quit).');
        $this->newLine();

        while (true) {
            $question = (string) $this->ask('You');

            if (in_array(strtolower(trim($question)), ['exit', 'quit', ''], true)) {
                $this->info('Bye!');

                return self::SUCCESS;
            }

            // Send the message. getMessage() blocks and runs the full agentic
            // loop: the LLM may call web_search (you'll see it via the observer),
            // read the result, then produce the final answer.
            $answer = $agent->chat(new UserMessage($question))
                ->getMessage()
                ->getContent();

            $this->newLine();
            $this->line('<fg=green>Researcher:</> '.$answer);
            $this->newLine();
        }
    }

    /**
     * A tiny observer that prints when a tool is called and when it returns.
     */
    private function toolWatcher(): ObserverInterface
    {
        return new class($this) implements ObserverInterface
        {
            public function __construct(private Command $command) {}

            public function onEvent(string $event, object $source, mixed $data = null, ?string $branchId = null): void
            {
                if ($event === 'tool-calling' && $data instanceof ToolCalling) {
                    $inputs = json_encode($data->tool->getInputs());
                    $this->command->line("  <fg=yellow>↳ calling tool</> {$data->tool->getName()}({$inputs})");
                }

                if ($event === 'tool-called' && $data instanceof ToolCalled) {
                    $result = str($data->tool->getResult())->limit(120)->toString();
                    $this->command->line("  <fg=yellow>↳ tool returned:</> {$result}");
                }
            }
        };
    }
}
