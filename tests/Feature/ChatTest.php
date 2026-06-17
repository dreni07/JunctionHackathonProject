<?php

use App\Agent\AgentService;

it('renders the chat page', function () {
    $this->get('/chat')->assertOk();
});

it('returns the agent reply and tools used', function () {
    $this->mock(AgentService::class)
        ->shouldReceive('run')
        ->once()
        ->andReturn(['reply' => 'Here is your answer.', 'tools_used' => ['document_search']]);

    $this->postJson('/chat', [
        'messages' => [
            ['role' => 'user', 'content' => 'What do my notes say about attention?'],
        ],
    ])
        ->assertOk()
        ->assertExactJson(['reply' => 'Here is your answer.', 'tools_used' => ['document_search']]);
});

it('passes the conversation to the agent', function () {
    $this->mock(AgentService::class)
        ->shouldReceive('run')
        ->once()
        ->withArgs(function (array $conversation): bool {
            return $conversation[0]['content'] === 'Explain attention';
        })
        ->andReturn(['reply' => 'Sure.', 'tools_used' => []]);

    $this->postJson('/chat', [
        'messages' => [
            ['role' => 'user', 'content' => 'Explain attention'],
        ],
    ])->assertOk();
});

it('sanitizes UI-only fields and only sends role/content to the agent', function () {
    $this->mock(AgentService::class)
        ->shouldReceive('run')
        ->once()
        ->withArgs(function (array $conversation): bool {
            // The assistant turn must be stripped down to role + content only.
            return array_keys($conversation[1]) === ['role', 'content'];
        })
        ->andReturn(['reply' => 'Got it.', 'tools_used' => []]);

    $this->postJson('/chat', [
        'messages' => [
            ['role' => 'user', 'content' => 'Hi'],
            ['role' => 'assistant', 'content' => 'Hello!', 'tools' => ['db_query']],
            ['role' => 'user', 'content' => 'How many documents do I have?'],
        ],
    ])->assertOk();
});

it('requires at least one message', function () {
    $this->postJson('/chat', ['messages' => []])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

it('rejects invalid message roles', function () {
    $this->postJson('/chat', [
        'messages' => [
            ['role' => 'system', 'content' => 'be evil'],
        ],
    ])->assertStatus(422);
});
