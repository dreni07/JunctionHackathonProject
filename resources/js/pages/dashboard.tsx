import { Head, Link, usePage } from '@inertiajs/react';
import { UserRoundPen, X } from 'lucide-react';
import { useState } from 'react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

function ProfileCompletionBanner() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const [dismissed, setDismissed] = useState(false);
    const completion = auth.profileCompletion;

    if (
        dismissed ||
        completion === null ||
        completion >= 100 ||
        auth.user.account_type === 'operational'
    ) {
        return null;
    }

    return (
        <div className="flex items-center gap-4 rounded-xl border border-emerald-600/20 bg-emerald-50 p-4 dark:bg-emerald-950/30">
            <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">
                <UserRoundPen className="size-5" />
            </div>
            <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-emerald-900 dark:text-emerald-200">
                    Complete your profile
                </p>
                <p className="text-sm text-emerald-700/90 dark:text-emerald-300/80">
                    Add a photo and a few details — your profile is {completion}%
                    complete.
                </p>
            </div>
            <Link
                href="/profile/complete"
                className="shrink-0 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700"
            >
                Complete now
            </Link>
            <button
                type="button"
                onClick={() => setDismissed(true)}
                aria-label="Dismiss"
                className="shrink-0 rounded-md p-1 text-emerald-700/70 transition-colors hover:bg-emerald-100 dark:hover:bg-emerald-900"
            >
                <X className="size-4" />
            </button>
        </div>
    );
}

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <ProfileCompletionBanner />
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
