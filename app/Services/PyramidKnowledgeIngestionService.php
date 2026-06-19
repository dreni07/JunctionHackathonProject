<?php

declare(strict_types=1);

namespace App\Services;

use App\Agent\Neuron\PyramidKnowledgeAgent;
use Illuminate\Http\UploadedFile;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\Events\ToolCalling;
use NeuronAI\Observability\ObserverInterface;
use RuntimeException;

class PyramidKnowledgeIngestionService
{
    public function __construct(
        private readonly PdfTextExtractor $pdfTextExtractor,
    ) {}

    /**
     * @return array{
     *     summary: string,
     *     extract_preview: string,
     *     character_count: int,
     *     tool_activity: list<array{name: string, input: array<string, mixed>, result: string}>
     * }
     */
    public function ingest(UploadedFile $file): array
    {
        if ((string) config('services.openai.api_key') === '') {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $text = $this->pdfTextExtractor->extractFromFile($file);

        if ($text === '') {
            throw new RuntimeException('No text could be extracted from the PDF.');
        }

        $toolActivity = new PyramidIngestionToolActivityCollector;
        $agent = PyramidKnowledgeAgent::make();
        $agent->observe($this->toolObserver($toolActivity));

        $prompt = <<<PROMPT
Document filename: {$file->getClientOriginalName()}

Extracted PDF text:
---
{$text}
---

Structure the facts above into Pyramid database tables using your tools.
PROMPT;

        $summary = $agent->chat(new UserMessage($prompt))
            ->getMessage()
            ->getContent();

        return [
            'summary' => is_string($summary) ? trim($summary) : '',
            'extract_preview' => str($text)->limit(1200)->toString(),
            'character_count' => strlen($text),
            'tool_activity' => $toolActivity->all(),
        ];
    }

    private function toolObserver(PyramidIngestionToolActivityCollector $toolActivity): ObserverInterface
    {
        $pendingInputs = [];

        return new class($toolActivity, $pendingInputs) implements ObserverInterface
        {
            /**
             * @param  array<string, array<string, mixed>>  $pendingInputs
             */
            public function __construct(
                private readonly PyramidIngestionToolActivityCollector $toolActivity,
                private array $pendingInputs,
            ) {}

            public function onEvent(string $event, object $source, mixed $data = null, ?string $branchId = null): void
            {
                if ($event === 'tool-calling' && $data instanceof ToolCalling) {
                    $this->pendingInputs[$data->tool->getName()] = $data->tool->getInputs();
                }

                if ($event === 'tool-called' && $data instanceof ToolCalled) {
                    $name = $data->tool->getName();

                    $this->toolActivity->entries[] = [
                        'name' => $name,
                        'input' => $this->pendingInputs[$name] ?? $data->tool->getInputs(),
                        'result' => (string) $data->tool->getResult(),
                    ];

                    unset($this->pendingInputs[$name]);
                }
            }
        };
    }
}
