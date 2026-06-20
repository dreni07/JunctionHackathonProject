<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * An email composed in "Manage boring things": an AI-written subject and body,
 * wrapped in one of the worker-chosen Blade templates.
 */
class GeneratedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $template  The blade template key (announcement, invitation, …).
     */
    public function __construct(
        public string $template,
        public string $subjectLine,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.'.$this->template,
            with: [
                'subjectLine' => $this->subjectLine,
                'body' => $this->body,
            ],
        );
    }
}
