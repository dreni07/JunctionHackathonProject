import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

const ROOMS = [
    { name: 'Blue', color: 'bg-blue-500' },
    { name: 'Orange', color: 'bg-orange-500' },
    { name: 'Green', color: 'bg-emerald-500' },
    { name: 'Yellow', color: 'bg-yellow-400' },
];

export default function AuthSplitLayout({
    children,
    title,
    description,
    name = 'Pyramid Backstage',
}: AuthLayoutProps) {
    return (
        <div className="grid min-h-svh lg:grid-cols-[1.05fr_1fr]">
            {/* Brand panel */}
            <div className="relative hidden flex-col justify-between overflow-hidden bg-zinc-950 p-12 text-white lg:flex">
                {/* Ambient gradient glows */}
                <div className="pointer-events-none absolute inset-0">
                    <div className="absolute -top-32 -left-24 size-[28rem] rounded-full bg-violet-600/30 blur-3xl" />
                    <div className="absolute top-1/3 -right-24 size-[26rem] rounded-full bg-fuchsia-600/20 blur-3xl" />
                    <div className="absolute -bottom-32 left-1/4 size-[30rem] rounded-full bg-indigo-600/20 blur-3xl" />
                </div>
                {/* Subtle grid */}
                <div
                    className="pointer-events-none absolute inset-0 opacity-[0.07]"
                    style={{
                        backgroundImage:
                            'linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px)',
                        backgroundSize: '44px 44px',
                    }}
                />

                {/* Top: wordmark */}
                <Link
                    href={home()}
                    className="relative z-10 flex items-center gap-3 text-lg font-semibold tracking-tight"
                >
                    <span className="flex size-9 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15 backdrop-blur">
                        <AppLogoIcon className="size-5 fill-current text-white" />
                    </span>
                    {name}
                </Link>

                {/* Middle: pitch */}
                <div className="relative z-10 max-w-md">
                    <h2 className="text-4xl leading-tight font-semibold tracking-tight text-balance">
                        Turn every event request into an{' '}
                        <span className="bg-gradient-to-r from-violet-300 via-fuchsia-300 to-orange-200 bg-clip-text text-transparent">
                            operational plan
                        </span>
                        .
                    </h2>
                    <p className="mt-5 text-sm leading-relaxed text-balance text-white/60">
                        From inquiry to execution — spaces, inventory,
                        quotations, conflicts and task lists, coordinated in one
                        place. No more scattered emails and spreadsheets.
                    </p>

                    <div className="mt-8 flex items-center gap-5">
                        {ROOMS.map((room) => (
                            <div
                                key={room.name}
                                className="flex items-center gap-2 text-xs font-medium text-white/50"
                            >
                                <span
                                    className={`size-2.5 rounded-full ${room.color}`}
                                />
                                {room.name}
                            </div>
                        ))}
                    </div>
                </div>

                {/* Bottom: quote */}
                <figure className="relative z-10 max-w-md border-l-2 border-white/15 pl-4">
                    <blockquote className="text-sm leading-relaxed text-white/70">
                        “The Pyramid has transformed from a monument of the past
                        into a platform for the future.”
                    </blockquote>
                    <figcaption className="mt-2 text-xs text-white/40">
                        Pyramid of Tirana — CEO
                    </figcaption>
                </figure>
            </div>

            {/* Form panel */}
            <div className="flex flex-col items-center justify-center bg-background px-6 py-12 sm:px-12">
                <div className="w-full max-w-sm">
                    {/* Mobile logo */}
                    <Link
                        href={home()}
                        className="mb-8 flex items-center justify-center gap-2 lg:hidden"
                    >
                        <span className="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                            <AppLogoIcon className="size-5 fill-current text-primary" />
                        </span>
                        <span className="font-semibold">{name}</span>
                    </Link>

                    <div className="mb-8 flex flex-col gap-2">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        {description && (
                            <p className="text-sm text-balance text-muted-foreground">
                                {description}
                            </p>
                        )}
                    </div>

                    {children}
                </div>
            </div>
        </div>
    );
}
