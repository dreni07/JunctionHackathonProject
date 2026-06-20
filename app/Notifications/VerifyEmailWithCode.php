<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class VerifyEmailWithCode extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = (int) config('auth.verification.expire', 15);

        return (new MailMessage)
            ->subject('Verify your email address')
            ->greeting('Hello!')
            ->line('Enter this verification code on the email verification screen to activate your account:')
            ->line(new HtmlString(
                '<p style="font-size:28px;font-weight:700;letter-spacing:0.35em;margin:24px 0;">'
                .e($this->code)
                .'</p>',
            ))
            ->line("The code expires in {$minutes} minutes.")
            ->line('If you did not create an account, no further action is required.');
    }
}
