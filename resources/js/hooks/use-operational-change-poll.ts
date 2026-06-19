import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';
import { poll } from '@/routes/operational/changes';

const POLL_INTERVAL_MS = 5000;

type OperationalChange = {
    id: string;
    model_type: string;
    model_id: string;
    action: 'created' | 'updated' | 'deleted';
    summary: string;
    payload: Record<string, unknown> | null;
    occurred_at: string | null;
};

type PollResponse = {
    success: boolean;
    changes: OperationalChange[];
    last_id: string;
};

function getCookie(name: string): string {
    const match = document.cookie.match(
        new RegExp('(^|; )' + name + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[2]) : '';
}

export function useOperationalChangePoll(): void {
    const { auth } = usePage<{ auth: { user: unknown } }>().props;
    const lastIdRef = useRef('');

    useEffect(() => {
        if (!auth.user) {
            return;
        }

        let cancelled = false;

        async function pollChanges(): Promise<void> {
            const url = poll.url(
                lastIdRef.current
                    ? { query: { since: lastIdRef.current } }
                    : undefined,
            );

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
                    },
                });

                if (!response.ok || cancelled) {
                    return;
                }

                const data = (await response.json()) as PollResponse;

                if (!data.success || data.changes.length === 0) {
                    return;
                }

                for (const change of data.changes) {
                    toast.info(change.summary, {
                        description: `${change.model_type} · ${change.action}`,
                    });
                }

                if (data.last_id) {
                    lastIdRef.current = data.last_id;
                }
            } catch {
                // Ignore transient network errors; the next poll will retry.
            }
        }

        void pollChanges();

        const intervalId = window.setInterval(() => {
            void pollChanges();
        }, POLL_INTERVAL_MS);

        return () => {
            cancelled = true;
            window.clearInterval(intervalId);
        };
    }, [auth.user]);
}
