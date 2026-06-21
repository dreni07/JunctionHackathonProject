<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

/**
 * A tiny mutable collector an observer writes tool results into, keyed by tool
 * name — so a caller can read structured output back out of an agent run.
 */
class ToolResultCollector
{
    /** @var array<string, string> */
    public array $results = [];
}
