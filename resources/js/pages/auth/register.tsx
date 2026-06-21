import { Form, Link } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AuthMinimalLayout from '@/layouts/auth/auth-minimal-layout';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { request } from '@/routes/password';

const inputClass =
    'w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-[15px] text-neutral-900 placeholder:text-neutral-400 outline-none transition focus:border-neutral-900';
const labelClass = 'mb-2 block text-sm font-medium text-neutral-700';

const passwordRules = ['Use 8 or more characters'];

export default function Register() {
    const [showPassword, setShowPassword] = useState(false);

    return (
        <AuthMinimalLayout title="Create an account">
            <div className="mb-7 text-center">
                <h1 className="text-[26px] font-bold tracking-tight text-neutral-900">
                    Create an account
                </h1>
                <p className="mx-auto mt-2 max-w-[300px] text-sm leading-relaxed text-neutral-500">
                    Set up your organization to start planning events at the
                    Pyramid of Tirana.
                </p>
            </div>

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                disableWhileProcessing
                className="flex flex-col gap-5"
            >
                {({ processing, errors }) => (
                    <>
                        {/* Email + tooltip */}
                        <div>
                            <label htmlFor="email" className={labelClass}>
                                Email
                            </label>
                            <div className="relative">
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    autoFocus
                                    autoComplete="email"
                                    className={inputClass}
                                />
                                <div className="pointer-events-none absolute top-1/2 left-full ml-4 hidden -translate-y-1/2 rounded-lg bg-neutral-800 px-3 py-2 text-xs whitespace-nowrap text-white shadow-lg lg:block">
                                    We will use your email as your user ID.
                                    <span className="absolute top-1/2 right-full -translate-y-1/2 border-8 border-transparent border-r-neutral-800" />
                                </div>
                            </div>
                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        {/* Phone */}
                        <div>
                            <label htmlFor="phone" className={labelClass}>
                                Phone
                            </label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                autoComplete="tel"
                                className={inputClass}
                            />
                            <p className="mt-2 text-xs leading-relaxed text-neutral-400">
                                We strongly recommend adding a phone number. This
                                will help verify your account and keep it safe.
                            </p>
                            <InputError message={errors.phone} className="mt-2" />
                        </div>

                        {/* Password */}
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
                                autoComplete="new-password"
                                className={inputClass}
                            />
                            <InputError
                                message={errors.password}
                                className="mt-2"
                            />

                            <ul className="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2">
                                {passwordRules.map((rule) => (
                                    <li
                                        key={rule}
                                        className="flex items-center gap-2 text-[13px] text-neutral-400"
                                    >
                                        <span className="size-1.5 rounded-full bg-neutral-300" />
                                        {rule}
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="mt-1 flex w-full items-center justify-center gap-2 rounded-full bg-neutral-900 px-6 py-3.5 text-[15px] font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60"
                            data-test="register-user-button"
                        >
                            {processing && <Spinner />}
                            Create account
                        </button>

                        <div className="flex flex-col items-center gap-1.5 text-center text-sm text-neutral-500">
                            <p>
                                Already have an account?{' '}
                                <Link
                                    href={login()}
                                    className="font-medium text-neutral-800 underline underline-offset-2"
                                >
                                    Log in
                                </Link>
                            </p>
                            <Link
                                href={request()}
                                className="hover:text-neutral-700"
                            >
                                Forget your user ID or password?
                            </Link>
                        </div>

                        <p className="text-center text-[13px] leading-relaxed text-neutral-500">
                            By creating an account, you agree to the{' '}
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
