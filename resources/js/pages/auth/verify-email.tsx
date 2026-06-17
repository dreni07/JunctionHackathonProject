import { Form, Head, usePage } from '@inertiajs/react';
import { MailCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

type Props = {
    status?: string;
    /** Seconds remaining before another email may be sent (server-enforced). */
    resendCooldown?: number;
};

type PageProps = {
    auth: Auth;
};

export default function VerifyEmail({ status, resendCooldown = 0 }: Props) {
    const { auth } = usePage<PageProps>().props;
    const [seconds, setSeconds] = useState(resendCooldown);

    // Re-sync whenever the server reports a new cooldown (e.g. after a send).
    useEffect(() => {
        setSeconds(resendCooldown);
    }, [resendCooldown]);

    // Tick down to zero.
    useEffect(() => {
        if (seconds <= 0) {
            return;
        }
        const timer = setTimeout(
            () => setSeconds((s) => Math.max(0, s - 1)),
            1000,
        );
        return () => clearTimeout(timer);
    }, [seconds]);

    const waiting = seconds > 0;

    return (
        <>
            <Head title="Email verification" />

            <div className="flex flex-col gap-6">
                <div className="flex items-center gap-3 rounded-xl border border-border bg-muted/40 p-4">
                    <span className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <MailCheck className="size-5" />
                    </span>
                    <p className="text-sm text-muted-foreground">
                        We sent a verification <strong className="text-foreground">link</strong>{' '}
                        (not a numeric code) to{' '}
                        <strong className="text-foreground">{auth.user?.email}</strong>.
                        Check spam if you do not see it. The link stays valid for{' '}
                        <strong className="text-foreground">15 minutes</strong> —
                        after that, request a fresh one below.
                    </p>
                </div>

                {status === 'verification-link-sent' && (
                    <div className="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                        A new verification link has been sent to your email
                        address.
                    </div>
                )}

                <Form {...send.form()} className="flex flex-col gap-4">
                    {({ processing, errors }) => (
                        <>
                            <InputError message={errors.email} />

                            <Button
                                type="submit"
                                disabled={processing || waiting}
                                className="w-full"
                            >
                                {processing && <Spinner />}
                                {waiting
                                    ? `Resend available in ${seconds}s`
                                    : 'Resend verification email'}
                            </Button>

                            <TextLink
                                href={logout()}
                                className="mx-auto block text-sm"
                            >
                                Log out
                            </TextLink>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verify your email',
    description: 'Click the link we just emailed you to activate your account.',
};
