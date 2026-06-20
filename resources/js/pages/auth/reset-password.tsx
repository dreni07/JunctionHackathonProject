import { Form, Link } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AuthMinimalLayout from '@/layouts/auth/auth-minimal-layout';
import { login } from '@/routes';
import { update } from '@/routes/password';

const inputClass =
    'w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-[15px] text-neutral-900 placeholder:text-neutral-400 outline-none transition focus:border-neutral-900 read-only:bg-neutral-50 read-only:text-neutral-600';
const labelClass = 'mb-2 block text-sm font-medium text-neutral-700';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmation, setShowConfirmation] = useState(false);

    return (
        <AuthMinimalLayout title="Reset password">
            <div className="mb-7 text-center">
                <h1 className="text-[26px] font-bold tracking-tight text-neutral-900">
                    Reset password
                </h1>
                <p className="mx-auto mt-2 max-w-[300px] text-sm leading-relaxed text-neutral-500">
                    Choose a new password for your Piramida account.
                </p>
            </div>

            <Form
                {...update.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-5"
            >
                {({ processing, errors }) => (
                    <>
                        <div>
                            <label htmlFor="email" className={labelClass}>
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                value={email}
                                readOnly
                                className={inputClass}
                            />
                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        <div>
                            <div className="mb-2 flex items-center justify-between">
                                <label
                                    htmlFor="password"
                                    className="text-sm font-medium text-neutral-700"
                                >
                                    New password
                                </label>
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowPassword((value) => !value)
                                    }
                                    className="flex items-center gap-1.5 text-sm text-neutral-500 transition hover:text-neutral-800"
                                >
                                    {showPassword ? (
                                        <Eye className="size-4" />
                                    ) : (
                                        <EyeOff className="size-4" />
                                    )}
                                    {showPassword ? 'Show' : 'Hide'}
                                </button>
                            </div>
                            <input
                                id="password"
                                name="password"
                                type={showPassword ? 'text' : 'password'}
                                required
                                autoFocus
                                autoComplete="new-password"
                                passwordrules={passwordRules}
                                className={inputClass}
                            />
                            <InputError
                                message={errors.password}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <div className="mb-2 flex items-center justify-between">
                                <label
                                    htmlFor="password_confirmation"
                                    className="text-sm font-medium text-neutral-700"
                                >
                                    Confirm password
                                </label>
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowConfirmation((value) => !value)
                                    }
                                    className="flex items-center gap-1.5 text-sm text-neutral-500 transition hover:text-neutral-800"
                                >
                                    {showConfirmation ? (
                                        <Eye className="size-4" />
                                    ) : (
                                        <EyeOff className="size-4" />
                                    )}
                                    {showConfirmation ? 'Show' : 'Hide'}
                                </button>
                            </div>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type={showConfirmation ? 'text' : 'password'}
                                required
                                autoComplete="new-password"
                                passwordrules={passwordRules}
                                className={inputClass}
                            />
                            <InputError
                                message={errors.password_confirmation}
                                className="mt-2"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="mt-1 flex w-full items-center justify-center gap-2 rounded-full bg-neutral-900 px-6 py-3.5 text-[15px] font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60"
                            data-test="reset-password-button"
                        >
                            {processing && <Spinner />}
                            Reset password
                        </button>

                        <p className="text-center text-sm text-neutral-500">
                            Remember your password?{' '}
                            <Link
                                href={login()}
                                className="font-medium text-neutral-800 underline underline-offset-2"
                            >
                                Log in
                            </Link>
                        </p>
                    </>
                )}
            </Form>
        </AuthMinimalLayout>
    );
}
