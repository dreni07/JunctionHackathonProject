<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Enums\AccountType;
use App\Mail\GeneratedEmail;
use App\Models\EmailPrompt;
use App\Models\User;
use App\Services\EmailComposerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EmailController extends OperationsController
{
    /**
     * The selectable email templates (each maps to a Blade view in emails/).
     *
     * @var list<array{key: string, name: string, description: string}>
     */
    private const TEMPLATES = [
        ['key' => 'announcement', 'name' => 'Announcement', 'description' => 'Share news or a decision with a group.'],
        ['key' => 'invitation', 'name' => 'Invitation', 'description' => 'Invite people to an event or meeting.'],
        ['key' => 'reminder', 'name' => 'Reminder', 'description' => 'A friendly nudge about something due.'],
        ['key' => 'thank-you', 'name' => 'Thank you', 'description' => 'Thank attendees, partners or staff.'],
        ['key' => 'update', 'name' => 'Update', 'description' => 'A status or progress update.'],
    ];

    public function __construct(private readonly EmailComposerService $composer) {}

    /**
     * The "Manage boring things" page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('operations/manage-boring-things', [
            'templates' => self::TEMPLATES,
            'previousPrompts' => $this->previousPrompts($request),
            'organizationContacts' => $this->organizationContacts(),
        ]);
    }

    /**
     * Generate an email (subject + body) from a template and a brief, and save
     * the brief so it can be reused later.
     */
    public function generate(Request $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            $validated = $request->validate([
                'template' => ['required', Rule::in(array_column(self::TEMPLATES, 'key'))],
                'prompt' => ['required', 'string', 'max:2000'],
                'recipients' => ['array'],
                'recipients.*' => ['email'],
            ]);

            $this->rememberPrompt($request, $validated['prompt'], $validated['template']);

            $templateName = collect(self::TEMPLATES)->firstWhere('key', $validated['template'])['name'];

            $email = $this->composer->compose(
                $templateName,
                $validated['recipients'] ?? [],
                $validated['prompt'],
            );

            return $this->json([
                'subject' => $email['subject'],
                'body' => $email['body'],
                'previousPrompts' => $this->previousPrompts($request),
            ]);
        });
    }

    /**
     * Send the (reviewed) email to every recipient using the chosen template.
     */
    public function send(Request $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            $validated = $request->validate([
                'template' => ['required', Rule::in(array_column(self::TEMPLATES, 'key'))],
                'subject' => ['required', 'string', 'max:255'],
                'body' => ['required', 'string', 'max:20000'],
                'recipients' => ['required', 'array', 'min:1'],
                'recipients.*' => ['email'],
            ]);

            $sent = [];
            $failed = [];

            foreach ($validated['recipients'] as $recipient) {
                try {
                    Mail::to($recipient)->send(new GeneratedEmail(
                        $validated['template'],
                        $validated['subject'],
                        $validated['body'],
                    ));
                    $sent[] = $recipient;
                } catch (Throwable $e) {
                    report($e);
                    $failed[] = $recipient;
                }
            }

            return $this->json([
                'sent' => $sent,
                'failed' => $failed,
            ]);
        });
    }

    /**
     * Save the brief for reuse, skipping an exact repeat of the latest one.
     */
    private function rememberPrompt(Request $request, string $prompt, string $template): void
    {
        $latest = EmailPrompt::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->first();

        if ($latest !== null && trim($latest->prompt) === trim($prompt)) {
            return;
        }

        EmailPrompt::query()->create([
            'user_id' => $request->user()->id,
            'prompt' => $prompt,
            'template' => $template,
        ]);
    }

    /**
     * The worker's most recent briefs, newest first.
     *
     * @return list<array{id: int, prompt: string, template: string|null, created_at: string|null}>
     */
    private function previousPrompts(Request $request): array
    {
        return EmailPrompt::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(20)
            ->get(['id', 'prompt', 'template', 'created_at'])
            ->map(fn (EmailPrompt $p): array => [
                'id' => $p->id,
                'prompt' => $p->prompt,
                'template' => $p->template,
                'created_at' => $p->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Registered organization accounts that can receive operational emails.
     *
     * @return list<array{id: int, name: string, email: string, organization: string|null}>
     */
    private function organizationContacts(): array
    {
        return User::query()
            ->where('account_type', AccountType::Organization)
            ->with('organization:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'organization_id'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'organization' => $user->organization?->name,
            ])
            ->all();
    }
}
