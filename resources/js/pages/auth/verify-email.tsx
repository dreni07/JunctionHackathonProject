import { store as verifyCode } from '@/actions/App/Http/Controllers/Auth/VerifyEmailCodeController';
import { Form, Link, usePage } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { MailCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import InputError from '@/components/input-error';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import AuthMinimalLayout from '@/layouts/auth/auth-minimal-layout';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

const CODE_LENGTH = 6;

type Props = {
    status?: string;
    /** Seconds remaining before another email may be sent (server-enforced). */
    resendCooldown?: number;
};

type PageProps = {
    auth: Auth;
};

const buttonClass =
    'flex w-full items-center justify-center gap-2 rounded-full bg-neutral-900 px-6 py-3.5 text-[15px] font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60';

type ResendCooldownButtonProps = {
    resendCooldown: number;
    processing: boolean;
    errors: Record<string, string | undefined>;
};

function ResendCooldownButton({
    resendCooldown,
    processing,
    errors,
}: ResendCooldownButtonProps) {
    const [elapsedSeconds, setElapsedSeconds] = useState(0);
    const seconds = Math.max(0, resendCooldown - elapsedSeconds);
    const waiting = seconds > 0;

    useEffect(() => {
        if (seconds <= 0) {
            return;
        }

        const timer = setTimeout(
            () => setElapsedSeconds((elapsed) => elapsed + 1),
            1000,
        );

        return () => clearTimeout(timer);
    }, [seconds]);

    return (
        <>
            <InputError message={errors.email} className="mb-2" />

            <button
                type="submit"
                disabled={processing || waiting}
                className={`${buttonClass} mt-1`}
            >
                {processing && <Spinner />}
                {waiting
                    ? `Resend available in ${seconds}s`
                    : 'Resend verification code'}
            </button>
        </>
    );
}

export default function VerifyEmail({ status, resendCooldown = 0 }: Props) {
    const { auth } = usePage<PageProps>().props;
    const [code, setCode] = useState('');

    return (
        <AuthMinimalLayout title="Verify your email">
            <div className="mb-7 text-center">
                <h1 className="text-[26px] font-bold tracking-tight text-neutral-900">
                    Verify your email
                </h1>
                <p className="mx-auto mt-2 max-w-[320px] text-sm leading-relaxed text-neutral-500">
                    Enter the 6-digit code we sent to your inbox.
                </p>
            </div>

            <div className="mb-5 flex items-start gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-left">
                <span className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-neutral-900/5 text-neutral-900">
                    <MailCheck className="size-5" />
                </span>
                <p className="text-sm leading-relaxed text-neutral-600">
                    We sent a verification{' '}
                    <strong className="font-medium text-neutral-900">
                        code
                    </strong>{' '}
                    to{' '}
                    <strong className="font-medium text-neutral-900">
                        {auth.user?.email}
                    </strong>
                    . Check spam if you do not see it. The code stays valid for{' '}
                    <strong className="font-medium text-neutral-900">
                        15 minutes
                    </strong>
                    .
                </p>
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-5 rounded-lg bg-neutral-100 px-4 py-2.5 text-center text-sm font-medium text-neutral-700">
                    A new verification code has been sent to your email address.
                </div>
            )}

            {status === 'verification-code-required' && (
                <div className="mb-5 rounded-lg bg-amber-50 px-4 py-2.5 text-center text-sm font-medium text-amber-900">
                    Email links are no longer used. Enter the code from your
                    email below.
                </div>
            )}

            <Form
                {...verifyCode.form()}
                resetOnError={['code']}
                className="mb-6 flex flex-col gap-4"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="flex flex-col items-center gap-3">
                            <InputOTP
                                name="code"
                                maxLength={CODE_LENGTH}
                                value={code}
                                onChange={setCode}
                                disabled={processing}
                                pattern={REGEXP_ONLY_DIGITS}
                                autoFocus
                            >
                                <InputOTPGroup>
                                    {Array.from(
                                        { length: CODE_LENGTH },
                                        (_, index) => (
                                            <InputOTPSlot
                                                key={index}
                                                index={index}
                                            />
                                        ),
                                    )}
                                </InputOTPGroup>
                            </InputOTP>
                            <InputError message={errors.code} />
                        </div>

                        <button
                            type="submit"
                            disabled={processing || code.length !== CODE_LENGTH}
                            className={buttonClass}
                        >
                            {processing && <Spinner />}
                            Verify email
                        </button>
                    </>
                )}
            </Form>

            <Form {...send.form()} className="flex flex-col gap-5">
                {({ processing, errors }) => (
                    <>
                        <ResendCooldownButton
                            key={resendCooldown}
                            resendCooldown={resendCooldown}
                            processing={processing}
                            errors={errors}
                        />

                        <p className="text-center text-sm text-neutral-500">
                            Wrong account?{' '}
                            <Link
                                href={logout()}
                                className="font-medium text-neutral-800 underline underline-offset-2"
                            >
                                Log out
                            </Link>
                        </p>
                    </>
                )}
            </Form>
        </AuthMinimalLayout>
    );
}
