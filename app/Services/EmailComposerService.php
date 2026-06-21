<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Turns a worker's plain-language brief into a finished email (subject + body)
 * in the tone of the chosen template, using the chat LLM.
 */
class EmailComposerService
{
    public function __construct(private readonly OpenAiChatService $llm) {}

    /**
     * @param  list<string>  $recipients
     * @return array{subject: string, body: string}
     */
    public function compose(string $templateName, array $recipients, string $prompt): array
    {
        $audience = $recipients === [] ? 'the recipients' : implode(', ', $recipients);

        $system = <<<SYS
            You write professional, friendly emails for the Pyramid of Tirana operations team.
            Write ONE complete email in the style of a "{$templateName}" message.
            Return STRICT JSON only, in the exact shape: {"subject": "...", "body": "..."}.
            The "body" is plain text with paragraphs separated by a blank line — no markdown, no HTML,
            no placeholders like [Name]. Keep it concise, warm and clear, and end with a short sign-off
            from the Pyramid of Tirana team. Do not include the subject inside the body.
            SYS;

        $user = "Audience: {$audience}\nWhat the email should say / include:\n{$prompt}";

        $raw = $this->llm->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ]);

        return $this->parse($raw, $prompt);
    }

    /**
     * @return array{subject: string, body: string}
     */
    private function parse(string $raw, string $fallbackPrompt): array
    {
        // Strip ```json fences if the model added them.
        $clean = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($raw)) ?? $raw);

        $decoded = json_decode($clean, true);

        if (is_array($decoded) && isset($decoded['subject'], $decoded['body'])) {
            return [
                'subject' => trim((string) $decoded['subject']),
                'body' => trim((string) $decoded['body']),
            ];
        }

        // Fallback: treat the first line as the subject, the rest as the body.
        $lines = preg_split('/\r?\n/', trim($raw)) ?: [];
        $subject = trim((string) array_shift($lines)) ?: 'A message from the Pyramid of Tirana';

        return [
            'subject' => $subject,
            'body' => trim(implode("\n", $lines)) ?: $fallbackPrompt,
        ];
    }
}
