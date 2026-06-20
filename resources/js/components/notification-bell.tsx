import { usePage } from '@inertiajs/react';
import { Bell, CheckCheck } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import type { Auth } from '@/types';

type NotificationItem = {
    id: string;
    data: {
        title?: string;
        message?: string;
        type?: string;
    };
    read: boolean;
    created_at: string | null;
};

function xsrf(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function post(url: string): Promise<void> {
    await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': xsrf(),
        },
    });
}

function relativeTime(iso: string | null): string {
    if (!iso) {
        return '';
    }

    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.round(diff / 60000);

    if (mins < 1) {
        return 'just now';
    }
    if (mins < 60) {
        return `${mins}m ago`;
    }

    const hours = Math.round(mins / 60);
    if (hours < 24) {
        return `${hours}h ago`;
    }

    return `${Math.round(hours / 24)}d ago`;
}

export function NotificationBell() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const [open, setOpen] = useState(false);
    const [items, setItems] = useState<NotificationItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [unread, setUnread] = useState(auth.unreadNotifications ?? 0);

    useEffect(() => {
        setUnread(auth.unreadNotifications ?? 0);
    }, [auth.unreadNotifications]);

    const load = useCallback(async () => {
        setLoading(true);

        try {
            const response = await fetch('/notifications', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const json = await response.json();

            setItems(json.data ?? []);
            setUnread(json.unread ?? 0);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (open) {
            void load();
        }
    }, [open, load]);

    const markOne = async (id: string) => {
        setItems((prev) =>
            prev.map((item) =>
                item.id === id ? { ...item, read: true } : item,
            ),
        );
        setUnread((count) => Math.max(0, count - 1));
        await post(`/notifications/${id}/read`);
    };

    const markAll = async () => {
        setItems((prev) => prev.map((item) => ({ ...item, read: true })));
        setUnread(0);
        await post('/notifications/read-all');
    };

    return (
        <DropdownMenu open={open} onOpenChange={setOpen}>
            <DropdownMenuTrigger asChild>
                <button
                    type="button"
                    aria-label="Notifications"
                    className="relative flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                >
                    <Bell className="size-5" />
                    {unread > 0 && (
                        <span className="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                            {unread > 9 ? '9+' : unread}
                        </span>
                    )}
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80 p-0">
                <div className="flex items-center justify-between border-b px-3 py-2.5">
                    <span className="text-sm font-semibold">Notifications</span>
                    {unread > 0 && (
                        <button
                            type="button"
                            onClick={markAll}
                            className="flex items-center gap-1 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <CheckCheck className="size-3.5" />
                            Mark all read
                        </button>
                    )}
                </div>

                <div className="max-h-96 overflow-y-auto">
                    {loading && items.length === 0 ? (
                        <div className="px-3 py-8 text-center text-sm text-muted-foreground">
                            Loading…
                        </div>
                    ) : items.length === 0 ? (
                        <div className="px-3 py-8 text-center text-sm text-muted-foreground">
                            You're all caught up.
                        </div>
                    ) : (
                        items.map((item) => (
                            <button
                                key={item.id}
                                type="button"
                                onClick={() => !item.read && markOne(item.id)}
                                className={cn(
                                    'flex w-full gap-3 border-b px-3 py-3 text-left transition-colors last:border-b-0 hover:bg-accent',
                                    !item.read && 'bg-accent/40',
                                )}
                            >
                                <span
                                    className={cn(
                                        'mt-1.5 size-2 shrink-0 rounded-full',
                                        item.read
                                            ? 'bg-transparent'
                                            : 'bg-emerald-500',
                                    )}
                                />
                                <span className="min-w-0 flex-1">
                                    <span className="block text-sm font-medium">
                                        {item.data.title ?? 'Notification'}
                                    </span>
                                    {item.data.message && (
                                        <span className="mt-0.5 block text-xs text-muted-foreground">
                                            {item.data.message}
                                        </span>
                                    )}
                                    <span className="mt-1 block text-[11px] text-muted-foreground/70">
                                        {relativeTime(item.created_at)}
                                    </span>
                                </span>
                            </button>
                        ))
                    )}
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
