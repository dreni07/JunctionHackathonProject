import { Link, usePage } from '@inertiajs/react';
import { UserRoundPen } from 'lucide-react';
import type { Auth } from '@/types';

/**
 * A subtle header pill inviting the user to finish their profile. Hidden once
 * the profile is fully complete.
 */
export function ProfileCompletionNudge() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const completion = auth.profileCompletion;

    if (
        completion === null ||
        completion >= 100 ||
        auth.user.account_type === 'operational'
    ) {
        return null;
    }

    return (
        <Link
            href="/profile/complete"
            className="hidden items-center gap-2 rounded-full border border-emerald-600/30 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100 sm:flex dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <UserRoundPen className="size-3.5" />
            Complete your profile · {completion}%
        </Link>
    );
}
