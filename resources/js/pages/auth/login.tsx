import { Form, Link } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AuthMinimalLayout from '@/layouts/auth/auth-minimal-layout';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

const inputClass =
    'w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-[15px] text-neutral-900 placeholder:text-neutral-400 outline-none transition focus:border-neutral-900';
const labelClass = 'mb-2 block text-sm font-medium text-neutral-700';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    const [showPassword, setShowPassword] = useState(false);

    return (
        <AuthMinimalLayout
            title="Log in"
            topRight={
                <>
                    <p>
                        New to Piramida?{' '}
                        <Link
                            href={register()}
                            className="font-medium text-neutral-800 underline underline-offset-2"
                        >
                            Create an account
                        </Link>
                    </p>
                    {canResetPassword && (
                        <Link
                            href={request()}
                            className="text-neutral-500 hover:text-neutral-700"
                        >
                            Forget your user ID or password?
                        </Link>
                    )}
                </>
            }
        >
            <div className="mb-7 text-center">
                <h1 className="text-[26px] font-bold tracking-tight text-neutral-900">
                    Welcome back
                </h1>
                <p className="mx-auto mt-2 max-w-[300px] text-sm leading-relaxed text-neutral-500">
                    Log in to keep planning your events at the Pyramid of Tirana.
                </p>
            </div>

            {status && (
                <div className="mb-5 rounded-lg bg-neutral-100 px-4 py-2.5 text-center text-sm font-medium text-neutral-700">
                    {status}
                </div>
            )}

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
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
                                name="email"
                                type="email"
                                required
                                autoFocus
                                autoComplete="email"
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
                                    Password
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
                                autoComplete="current-password"
                                className={inputClass}
                            />
                            <InputError
                                message={errors.password}
                                className="mt-2"
                            />
                        </div>

                        <label className="flex items-center gap-2.5 text-sm text-neutral-600 select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                className="size-4 rounded border-neutral-300 text-neutral-900 accent-neutral-900"
                            />
                            Remember me
                        </label>

                        <button
                            type="submit"
                            disabled={processing}
                            className="mt-1 flex w-full items-center justify-center gap-2 rounded-full bg-neutral-900 px-6 py-3.5 text-[15px] font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60"
                            data-test="login-button"
                        >
                            {processing && <Spinner />}
                            Log in
                        </button>

                        <p className="text-center text-[13px] leading-relaxed text-neutral-500">
                            By continuing, you agree to the{' '}
                            <a
                                href="#"
                                className="font-medium text-neutral-700 underline underline-offset-2"
                            >
                                Terms of use
                            </a>{' '}
                            and{' '}
                            <a
                                href="#"
                                className="font-medium text-neutral-700 underline underline-offset-2"
                            >
                                Privacy Policy
                            </a>
                            .
                        </p>
                    </>
                )}
            </Form>
        </AuthMinimalLayout>
    );
}
