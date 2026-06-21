<?php

namespace App\Agent;

interface Tool
{
    /**
     * The tool name the LLM uses to call it (snake_case).
     */
    public function name(): string;

    /**
     * A clear description telling the LLM when to use this tool.
     */
    public function description(): string;

    /**
     * JSON Schema describing the tool's arguments.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array;

    /**
     * Execute the tool and return a string result for the LLM.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function execute(array $arguments): string;
}
