import { Head, router, usePage } from '@inertiajs/react';
import {
    Bell,
    Building2,
    CalendarDays,
    CheckCircle2,
    ClipboardList,
    LayoutDashboard,
    ListChecks,
    LogOut,
    type LucideIcon,
    MapPin,
    RefreshCw,
    TriangleAlert,
} from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

/* ---- palette (shared with the landing + planner) ---- */
const C = {
    cream: '#F4F3EE',
    card: '#FFFFFF',
    border: '#E0DCD3',
    borderSoft: '#EAE7DC',
    green: '#10825B',
    greenDeep: '#0E6E4D',
    greenDark: '#2A6F44',
    greenTint: '#D8E2DC',
    ink: '#1A1A1A',
    muted: '#6E6E6E',
    faint: '#9A958B',
    amber: '#8A6D1C',
    amberTint: 'rgba(138,109,28,0.12)',
    danger: '#B4453A',
    dangerTint: 'rgba(180,69,58,0.1)',
};

const css = `
.ops-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:${C.cream};color:${C.ink}}
.ops-root *{box-sizing:border-box}
.ops-nav{transition:background .18s ease,color .18s ease}
.ops-nav:hover{background:${C.cream}}
.ops-nav.is-active{background:${C.greenTint};color:${C.green}}
.ops-card{transition:box-shadow .25s ease,transform .25s ease}
.ops-stat:hover{box-shadow:0 16px 36px -28px rgba(26,26,26,0.3)}
.ops-btn{transition:background .18s ease,border-color .18s ease,color .18s ease}
.ops-btn:hover{background:${C.cream}}
.ops-pill:hover{border-color:${C.green}}
.ops-spin{animation:ops-spin 1s linear infinite}
@keyframes ops-spin{to{transform:rotate(360deg)}}
select.ops-select{font-family:inherit;font-size:13px;font-weight:600;color:${C.ink};background:${C.card};border:1px solid ${C.border};border-radius:8px;padding:5px 8px;outline:none;cursor:pointer}
select.ops-select:hover{border-color:${C.green}}
`;

/* ---- types ---- */
type AuthUser = {
    id: number;
    name: string;
    email: string;
    worker_role: string | null;
    tenant_id: number | null;
    account_type: string | null;
};

type PageProps = {
    auth: { user: AuthUser; permissions: string[] };
    tenant: { id: number; title: string; description: string | null } | null;
};

type Summary = {
    pending_requests: number;
    events_this_week: number;
    open_conflicts: number;
    tasks_due_today: number;
    unread_alerts: number;
};

type Task = {
    id: string;
    name: string;
    description: string | null;
    state: string;
    state_label: string;
    phase_label: string;
    due_at: string | null;
    user_id: number | null;
    worker: { id: number; name: string } | null;
    event: { id: string; title: string } | null;
};

type Alert = {
    id: string;
    title: string;
    message: string;
    source: string;
    severity: string;
    status: string;
    created_at: string | null;
};

type Event = {
    id: string;
    title: string | null;
    status_label: string;
    event_type_label: string | null;
    attendees: number | null;
    start_time: string | null;
};

type Space = {
    id: string;
    name: string;
    room_code: string | null;
    zone_class: string;
    floor: number;
    capacity: number;
    functional_type: string | null;
};

type EventRequest = {
    id: string;
    title: string | null;
    description: string | null;
    event_type_label: string | null;
    attendees: number | null;
    price_suggested: number | null;
    price_agreed: number | null;
    preferred_start_at: string | null;
    preferred_end_at: string | null;
    status: string;
    status_label: string;
    submitter: { id: number; name: string; email: string } | null;
    matched_space: {
        id: string;
        name: string;
        zone_class: string;
        capacity: number;
    } | null;
    event_id: string | null;
};

type View = 'overview' | 'requests' | 'tasks' | 'alerts' | 'events' | 'spaces';

/* ---- fetch helpers ---- */
function csrf(): string {
    const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return m ? decodeURIComponent(m[1]) : '';
}

async function mutate(url: string, method: string): Promise<void> {
    await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': csrf(),
        },
    });
}

async function patchJson(url: string, body: unknown): Promise<void> {
    await fetch(url, {
        method: 'PATCH',
        credentials: 'same-origin',
        body: JSON.stringify(body),
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': csrf(),
        },
    });
}

function useApi<T>(url: string | null): {
    data: T | null;
    loading: boolean;
    reload: () => void;
} {
    const [data, setData] = useState<T | null>(null);
    const [loading, setLoading] = useState(true);
    const [tick, setTick] = useState(0);

    useEffect(() => {
        if (!url) {
            return;
        }

        let active = true;
        setLoading(true);

        fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((r) => (r.ok ? r.json() : Promise.reject(r)))
            .then((json) => {
                if (active) {
                    setData(json);
                    setLoading(false);
                }
            })
            .catch(() => active && setLoading(false));

        return () => {
            active = false;
        };
    }, [url, tick]);

    return { data, loading, reload: () => setTick((t) => t + 1) };
}

const TASK_STATES = [
    'pending',
    'started',
    'ongoing',
    'on_process',
    'finished',
    'cancelled',
];

const NAV: { view: View; label: string; icon: LucideIcon; perm?: string }[] = [
    { view: 'overview', label: 'Overview', icon: LayoutDashboard },
    {
        view: 'requests',
        label: 'Event Requests',
        icon: ClipboardList,
        perm: 'requests.view',
    },
    { view: 'tasks', label: 'My Tasks', icon: ListChecks, perm: 'tasks.view' },
    { view: 'alerts', label: 'Alerts', icon: Bell, perm: 'requests.view' },
    { view: 'events', label: 'Events', icon: CalendarDays, perm: 'events.view' },
    { view: 'spaces', label: 'Spaces', icon: MapPin, perm: 'spaces.view' },
];

export default function OperationsDashboard() {
    const { auth, tenant } = usePage<PageProps>().props;
    const [view, setView] = useState<View>('overview');

    const can = (perm?: string) => !perm || auth.permissions.includes(perm);
    const nav = NAV.filter((item) => can(item.perm));
    const initials = auth.user.name
        .split(' ')
        .map((w) => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <div
            className="ops-root"
            style={{
                display: 'flex',
                height: '100vh',
                width: '100%',
                overflow: 'hidden',
            }}
        >
            <Head title="Operations">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link
                    rel="preconnect"
                    href="https://fonts.gstatic.com"
                    crossOrigin=""
                />
                <link
                    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap"
                    rel="stylesheet"
                />
            </Head>
            <style dangerouslySetInnerHTML={{ __html: css }} />

            {/* ===== SIDEBAR ===== */}
            <aside
                style={{
                    width: 256,
                    flex: 'none',
                    background: C.card,
                    borderRight: `1px solid ${C.borderSoft}`,
                    display: 'flex',
                    flexDirection: 'column',
                    padding: '18px 14px',
                }}
            >
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 10,
                        padding: '4px 8px 16px',
                    }}
                >
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 30,
                            height: 30,
                            borderRadius: 8,
                            background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                            color: '#fff',
                        }}
                    >
                        <Building2 size={16} />
                    </span>
                    <span style={{ fontWeight: 800, letterSpacing: '0.04em' }}>
                        PIRAMIDA
                    </span>
                </div>

                {/* worker card */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 11,
                        padding: '12px',
                        margin: '0 0 16px',
                        borderRadius: 12,
                        background: C.cream,
                    }}
                >
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 38,
                            height: 38,
                            flex: 'none',
                            borderRadius: '50%',
                            background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                            color: '#fff',
                            fontWeight: 700,
                            fontSize: 14,
                        }}
                    >
                        {initials}
                    </span>
                    <div style={{ minWidth: 0 }}>
                        <div
                            style={{
                                fontSize: 14,
                                fontWeight: 600,
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                whiteSpace: 'nowrap',
                            }}
                        >
                            {auth.user.name}
                        </div>
                        <div style={{ fontSize: 12, color: C.muted }}>
                            {auth.user.worker_role ?? 'Worker'}
                        </div>
                    </div>
                </div>

                <p
                    style={{
                        fontSize: 11.5,
                        fontWeight: 600,
                        letterSpacing: '0.04em',
                        textTransform: 'uppercase',
                        color: C.faint,
                        padding: '0 8px',
                        marginBottom: 8,
                    }}
                >
                    {tenant?.title ?? 'Operations'}
                </p>

                <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    {nav.map((item) => (
                        <button
                            key={item.view}
                            type="button"
                            className={`ops-nav${view === item.view ? ' is-active' : ''}`}
                            onClick={() => setView(item.view)}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 11,
                                width: '100%',
                                padding: '9px 10px',
                                borderRadius: 9,
                                border: 'none',
                                background: 'transparent',
                                fontSize: 14,
                                fontWeight: 500,
                                color: view === item.view ? C.green : '#3A3A3A',
                                cursor: 'pointer',
                                textAlign: 'left',
                            }}
                        >
                            <item.icon
                                size={17}
                                color={view === item.view ? C.green : C.muted}
                            />
                            {item.label}
                        </button>
                    ))}
                </div>

                <button
                    type="button"
                    className="ops-nav"
                    onClick={() => router.post('/logout')}
                    style={{
                        marginTop: 'auto',
                        display: 'flex',
                        alignItems: 'center',
                        gap: 11,
                        padding: '9px 10px',
                        borderRadius: 9,
                        border: 'none',
                        background: 'transparent',
                        fontSize: 14,
                        fontWeight: 500,
                        color: '#3A3A3A',
                        cursor: 'pointer',
                        textAlign: 'left',
                    }}
                >
                    <LogOut size={17} color={C.muted} />
                    Sign out
                </button>
            </aside>

            {/* ===== MAIN ===== */}
            <main
                style={{
                    flex: 1,
                    minWidth: 0,
                    overflowY: 'auto',
                    padding: 'clamp(20px,3vw,34px)',
                }}
            >
                <div style={{ maxWidth: 1080, margin: '0 auto' }}>
                    {view === 'overview' && (
                        <Overview user={auth.user} tenant={tenant} />
                    )}
                    {view === 'requests' && (
                        <RequestsView canManage={can('requests.manage')} />
                    )}
                    {view === 'tasks' && <TasksView user={auth.user} />}
                    {view === 'alerts' && <AlertsView />}
                    {view === 'events' && <EventsView />}
                    {view === 'spaces' && <SpacesView />}
                </div>
            </main>
        </div>
    );
}

/* ============================ OVERVIEW ============================ */

function Overview({
    user,
    tenant,
}: {
    user: AuthUser;
    tenant: PageProps['tenant'];
}) {
    const { data, loading, reload } = useApi<{ data: Summary }>(
        '/operations/dashboard',
    );
    const tasks = useApi<{ data: Task[] }>('/operations/tasks?per_page=50');
    const alerts = useApi<{ data: Alert[] }>('/operations/alerts?per_page=10');

    const summary = data?.data;
    const myTasks = (tasks.data?.data ?? [])
        .filter((t) => t.worker?.id === user.id)
        .filter((t) => t.state !== 'finished' && t.state !== 'cancelled')
        .slice(0, 6);

    const stats: { label: string; value: number; icon: LucideIcon }[] = [
        {
            label: 'Tasks due today',
            value: summary?.tasks_due_today ?? 0,
            icon: ListChecks,
        },
        {
            label: 'Pending requests',
            value: summary?.pending_requests ?? 0,
            icon: ClipboardList,
        },
        {
            label: 'Events this week',
            value: summary?.events_this_week ?? 0,
            icon: CalendarDays,
        },
        {
            label: 'Open conflicts',
            value: summary?.open_conflicts ?? 0,
            icon: TriangleAlert,
        },
        {
            label: 'Unread alerts',
            value: summary?.unread_alerts ?? 0,
            icon: Bell,
        },
    ];

    return (
        <>
            <Header
                title={`Welcome back, ${user.name.split(' ')[0]}`}
                subtitle={`${user.worker_role ?? 'Worker'}${tenant ? ` · ${tenant.title}` : ''}`}
                onRefresh={() => {
                    reload();
                    tasks.reload();
                    alerts.reload();
                }}
            />

            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns:
                        'repeat(auto-fit, minmax(170px, 1fr))',
                    gap: 14,
                    marginBottom: 26,
                }}
            >
                {stats.map((s) => (
                    <div
                        key={s.label}
                        className="ops-stat"
                        style={{
                            background: C.card,
                            border: `1px solid ${C.border}`,
                            borderRadius: 16,
                            padding: '16px 18px',
                        }}
                    >
                        <span
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: 36,
                                height: 36,
                                borderRadius: 10,
                                background: C.greenTint,
                                color: C.green,
                                marginBottom: 12,
                            }}
                        >
                            <s.icon size={18} />
                        </span>
                        <div
                            style={{
                                fontSize: 26,
                                fontWeight: 800,
                                letterSpacing: '-0.01em',
                            }}
                        >
                            {loading ? '—' : s.value}
                        </div>
                        <div style={{ fontSize: 13, color: C.muted }}>
                            {s.label}
                        </div>
                    </div>
                ))}
            </div>

            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))',
                    gap: 18,
                }}
            >
                <Panel title="My tasks" loading={tasks.loading}>
                    {myTasks.length === 0 ? (
                        <Empty text="No open tasks assigned to you. Nice and clear." />
                    ) : (
                        myTasks.map((task) => (
                            <TaskRow
                                key={task.id}
                                task={task}
                                onChanged={tasks.reload}
                            />
                        ))
                    )}
                </Panel>

                <Panel title="Recent alerts" loading={alerts.loading}>
                    {(alerts.data?.data ?? []).length === 0 ? (
                        <Empty text="No alerts right now." />
                    ) : (
                        (alerts.data?.data ?? [])
                            .slice(0, 5)
                            .map((alert) => (
                                <AlertRow
                                    key={alert.id}
                                    alert={alert}
                                    onChanged={alerts.reload}
                                />
                            ))
                    )}
                </Panel>
            </div>
        </>
    );
}

/* ============================ TASKS ============================ */

function TasksView({ user }: { user: AuthUser }) {
    const [scope, setScope] = useState<'mine' | 'all'>('mine');
    const { data, loading, reload } = useApi<{ data: Task[] }>(
        '/operations/tasks?per_page=100',
    );

    const all = data?.data ?? [];
    const tasks =
        scope === 'mine' ? all.filter((t) => t.worker?.id === user.id) : all;

    return (
        <>
            <Header
                title="Tasks"
                subtitle="Update the status of your work as you go."
                onRefresh={reload}
            />
            <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
                {(['mine', 'all'] as const).map((s) => (
                    <button
                        key={s}
                        type="button"
                        className="ops-pill"
                        onClick={() => setScope(s)}
                        style={{
                            padding: '7px 14px',
                            borderRadius: 999,
                            border: `1px solid ${scope === s ? C.green : C.border}`,
                            background: scope === s ? C.greenTint : C.card,
                            color: scope === s ? C.green : C.muted,
                            fontSize: 13,
                            fontWeight: 600,
                            cursor: 'pointer',
                        }}
                    >
                        {s === 'mine' ? 'Assigned to me' : 'All tasks'}
                    </button>
                ))}
            </div>

            <Panel title={null} loading={loading}>
                {tasks.length === 0 ? (
                    <Empty text="No tasks here." />
                ) : (
                    tasks.map((task) => (
                        <TaskRow key={task.id} task={task} onChanged={reload} />
                    ))
                )}
            </Panel>
        </>
    );
}

function TaskRow({ task, onChanged }: { task: Task; onChanged: () => void }) {
    const [saving, setSaving] = useState(false);

    const change = async (state: string) => {
        setSaving(true);
        await patchJson(`/operations/tasks/${task.id}`, { state });
        setSaving(false);
        onChanged();
    };

    return (
        <div
            style={{
                display: 'flex',
                alignItems: 'center',
                gap: 12,
                padding: '12px 0',
                borderTop: `1px solid ${C.borderSoft}`,
            }}
        >
            <StateDot state={task.state} />
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 14.5, fontWeight: 600 }}>
                    {task.name}
                </div>
                <div style={{ fontSize: 12.5, color: C.muted }}>
                    {task.event?.title ?? 'Unassigned event'}
                    {task.due_at ? ` · due ${formatDate(task.due_at)}` : ''}
                </div>
            </div>
            <select
                className="ops-select"
                value={task.state}
                disabled={saving}
                onChange={(e) => change(e.target.value)}
            >
                {TASK_STATES.map((s) => (
                    <option key={s} value={s}>
                        {labelize(s)}
                    </option>
                ))}
            </select>
        </div>
    );
}

/* ============================ ALERTS ============================ */

function AlertsView() {
    const { data, loading, reload } = useApi<{ data: Alert[] }>(
        '/operations/alerts?per_page=100',
    );
    const alerts = data?.data ?? [];

    return (
        <>
            <Header
                title="Alerts"
                subtitle="Notifications from the agents and the system."
                onRefresh={reload}
            />
            <Panel title={null} loading={loading}>
                {alerts.length === 0 ? (
                    <Empty text="No alerts." />
                ) : (
                    alerts.map((alert) => (
                        <AlertRow
                            key={alert.id}
                            alert={alert}
                            onChanged={reload}
                        />
                    ))
                )}
            </Panel>
        </>
    );
}

function AlertRow({ alert, onChanged }: { alert: Alert; onChanged: () => void }) {
    const act = async (action: 'read' | 'dismiss') => {
        await mutate(`/operations/alerts/${alert.id}/${action}`, 'PATCH');
        onChanged();
    };

    const dim = alert.status === 'dismissed' || alert.status === 'read';

    return (
        <div
            style={{
                display: 'flex',
                alignItems: 'flex-start',
                gap: 12,
                padding: '13px 0',
                borderTop: `1px solid ${C.borderSoft}`,
                opacity: dim ? 0.6 : 1,
            }}
        >
            <SeverityTag severity={alert.severity} />
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 14, fontWeight: 600 }}>
                    {alert.title}
                </div>
                <div
                    style={{
                        fontSize: 13,
                        color: C.muted,
                        marginTop: 2,
                        lineHeight: 1.5,
                    }}
                >
                    {alert.message}
                </div>
                <div style={{ fontSize: 11.5, color: C.faint, marginTop: 4 }}>
                    {labelize(alert.source)}
                    {alert.created_at ? ` · ${formatDate(alert.created_at)}` : ''}
                </div>
            </div>
            {alert.status !== 'dismissed' && (
                <div style={{ display: 'flex', gap: 6, flex: 'none' }}>
                    {alert.status === 'unread' && (
                        <button
                            type="button"
                            className="ops-btn"
                            onClick={() => act('read')}
                            style={ghostBtn()}
                            title="Mark read"
                        >
                            <CheckCircle2 size={15} />
                        </button>
                    )}
                    <button
                        type="button"
                        className="ops-btn"
                        onClick={() => act('dismiss')}
                        style={ghostBtn()}
                    >
                        Dismiss
                    </button>
                </div>
            )}
        </div>
    );
}

/* ============================ EVENT REQUESTS ============================ */

const PENDING_STATUSES = ['submitted', 'under_review', 'proposal_draft'];

function RequestsView({ canManage }: { canManage: boolean }) {
    const [scope, setScope] = useState<'pending' | 'all'>('pending');
    const { data, loading, reload } = useApi<{ data: EventRequest[] }>(
        '/operations/event-requests?per_page=100',
    );

    const all = data?.data ?? [];
    const requests =
        scope === 'pending'
            ? all.filter((r) => PENDING_STATUSES.includes(r.status))
            : all;

    return (
        <>
            <Header
                title="Event requests"
                subtitle="Incoming requests from organizers — confirm one to register it as an event."
                onRefresh={reload}
            />
            <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
                {(['pending', 'all'] as const).map((s) => (
                    <button
                        key={s}
                        type="button"
                        className="ops-pill"
                        onClick={() => setScope(s)}
                        style={{
                            padding: '7px 14px',
                            borderRadius: 999,
                            border: `1px solid ${scope === s ? C.green : C.border}`,
                            background: scope === s ? C.greenTint : C.card,
                            color: scope === s ? C.green : C.muted,
                            fontSize: 13,
                            fontWeight: 600,
                            cursor: 'pointer',
                        }}
                    >
                        {s === 'pending' ? 'To confirm' : 'All requests'}
                    </button>
                ))}
            </div>

            <Panel title={null} loading={loading}>
                {requests.length === 0 ? (
                    <Empty text="No event requests here." />
                ) : (
                    requests.map((request) => (
                        <RequestRow
                            key={request.id}
                            request={request}
                            canManage={canManage}
                            onChanged={reload}
                        />
                    ))
                )}
            </Panel>
        </>
    );
}

function RequestRow({
    request,
    canManage,
    onChanged,
}: {
    request: EventRequest;
    canManage: boolean;
    onChanged: () => void;
}) {
    const [busy, setBusy] = useState(false);
    const registered =
        request.status === 'converted' || request.event_id != null;
    const price = request.price_agreed ?? request.price_suggested;

    const confirm = async () => {
        setBusy(true);
        await mutate(`/operations/event-requests/${request.id}/convert`, 'POST');
        setBusy(false);
        onChanged();
    };

    return (
        <div
            style={{
                display: 'flex',
                alignItems: 'flex-start',
                gap: 12,
                padding: '14px 0',
                borderTop: `1px solid ${C.borderSoft}`,
            }}
        >
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 14.5, fontWeight: 600 }}>
                    {request.title ?? 'Untitled request'}
                </div>
                <div style={{ fontSize: 12.5, color: C.muted, marginTop: 2 }}>
                    {request.event_type_label ?? '—'}
                    {request.attendees ? ` · ${request.attendees} people` : ''}
                    {request.preferred_start_at
                        ? ` · ${formatDate(request.preferred_start_at)}`
                        : ''}
                </div>
                <div style={{ fontSize: 12.5, color: C.faint, marginTop: 3 }}>
                    {request.matched_space
                        ? request.matched_space.name
                        : 'No venue'}
                    {price != null
                        ? ` · €${Math.round(Number(price)).toLocaleString()}`
                        : ''}
                    {request.submitter ? ` · ${request.submitter.name}` : ''}
                </div>
            </div>

            {registered ? (
                <span
                    style={{
                        flex: 'none',
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 5,
                        padding: '5px 11px',
                        borderRadius: 999,
                        fontSize: 12.5,
                        fontWeight: 700,
                        color: C.green,
                        background: C.greenTint,
                    }}
                >
                    <CheckCircle2 size={14} />
                    Registered
                </span>
            ) : canManage ? (
                <button
                    type="button"
                    className="ops-confirm"
                    onClick={confirm}
                    disabled={busy}
                    style={{
                        flex: 'none',
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 7,
                        padding: '8px 15px',
                        borderRadius: 9,
                        border: 'none',
                        background: C.green,
                        color: '#fff',
                        fontSize: 13,
                        fontWeight: 600,
                        cursor: busy ? 'not-allowed' : 'pointer',
                        opacity: busy ? 0.6 : 1,
                    }}
                >
                    {busy ? (
                        <RefreshCw size={14} className="ops-spin" />
                    ) : (
                        <CheckCircle2 size={15} />
                    )}
                    {busy ? 'Confirming…' : 'Confirm & register'}
                </button>
            ) : (
                <Chip text={request.status_label} />
            )}
        </div>
    );
}

/* ============================ EVENTS ============================ */

function EventsView() {
    const { data, loading, reload } = useApi<{ data: Event[] }>(
        '/operations/events?per_page=50',
    );
    const events = data?.data ?? [];

    return (
        <>
            <Header
                title="Events"
                subtitle="Planned events across the Pyramid."
                onRefresh={reload}
            />
            <Panel title={null} loading={loading}>
                {events.length === 0 ? (
                    <Empty text="No events yet." />
                ) : (
                    events.map((event) => (
                        <div
                            key={event.id}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 12,
                                padding: '13px 0',
                                borderTop: `1px solid ${C.borderSoft}`,
                            }}
                        >
                            <div style={{ flex: 1, minWidth: 0 }}>
                                <div style={{ fontSize: 14.5, fontWeight: 600 }}>
                                    {event.title ?? 'Untitled event'}
                                </div>
                                <div style={{ fontSize: 12.5, color: C.muted }}>
                                    {event.event_type_label ?? '—'}
                                    {event.attendees
                                        ? ` · ${event.attendees} people`
                                        : ''}
                                    {event.start_time
                                        ? ` · ${formatDate(event.start_time)}`
                                        : ''}
                                </div>
                            </div>
                            <Chip text={event.status_label} />
                        </div>
                    ))
                )}
            </Panel>
        </>
    );
}

/* ============================ SPACES ============================ */

function SpacesView() {
    const { data, loading, reload } = useApi<{ data: Space[] }>(
        '/operations/spaces?per_page=100',
    );
    const spaces = data?.data ?? [];

    return (
        <>
            <Header
                title="Spaces"
                subtitle="The bookable rooms across the building."
                onRefresh={reload}
            />
            <Panel title={null} loading={loading}>
                {spaces.length === 0 ? (
                    <Empty text="No spaces." />
                ) : (
                    spaces.map((space) => (
                        <div
                            key={space.id}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 12,
                                padding: '12px 0',
                                borderTop: `1px solid ${C.borderSoft}`,
                            }}
                        >
                            <div style={{ flex: 1, minWidth: 0 }}>
                                <div style={{ fontSize: 14.5, fontWeight: 600 }}>
                                    {space.name}
                                </div>
                                <div style={{ fontSize: 12.5, color: C.muted }}>
                                    {space.room_code ? `${space.room_code} · ` : ''}
                                    Floor {space.floor} · {space.capacity} cap
                                </div>
                            </div>
                            <ZoneTag zone={space.zone_class} />
                        </div>
                    ))
                )}
            </Panel>
        </>
    );
}

/* ============================ shared bits ============================ */

function Header({
    title,
    subtitle,
    onRefresh,
}: {
    title: string;
    subtitle: string;
    onRefresh: () => void;
}) {
    const [spinning, setSpinning] = useState(false);

    const refresh = useCallback(() => {
        setSpinning(true);
        onRefresh();
        setTimeout(() => setSpinning(false), 600);
    }, [onRefresh]);

    return (
        <div
            style={{
                display: 'flex',
                alignItems: 'flex-start',
                justifyContent: 'space-between',
                gap: 12,
                marginBottom: 22,
            }}
        >
            <div>
                <h1
                    style={{
                        fontSize: 'clamp(22px,3vw,28px)',
                        fontWeight: 800,
                        letterSpacing: '-0.02em',
                    }}
                >
                    {title}
                </h1>
                <p style={{ fontSize: 14, color: C.muted, marginTop: 3 }}>
                    {subtitle}
                </p>
            </div>
            <button
                type="button"
                className="ops-btn"
                onClick={refresh}
                style={{
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: 7,
                    padding: '8px 13px',
                    borderRadius: 9,
                    border: `1px solid ${C.border}`,
                    background: C.card,
                    color: C.muted,
                    fontSize: 13,
                    fontWeight: 600,
                    cursor: 'pointer',
                }}
            >
                <RefreshCw
                    size={14}
                    className={spinning ? 'ops-spin' : undefined}
                />
                Refresh
            </button>
        </div>
    );
}

function Panel({
    title,
    loading,
    children,
}: {
    title: string | null;
    loading: boolean;
    children: React.ReactNode;
}) {
    return (
        <div
            className="ops-card"
            style={{
                background: C.card,
                border: `1px solid ${C.border}`,
                borderRadius: 16,
                padding: '18px 20px',
            }}
        >
            {title && (
                <div
                    style={{
                        fontSize: 12,
                        fontWeight: 700,
                        letterSpacing: '0.05em',
                        textTransform: 'uppercase',
                        color: C.faint,
                        marginBottom: 6,
                    }}
                >
                    {title}
                </div>
            )}
            {loading ? <Empty text="Loading…" /> : children}
        </div>
    );
}

function Empty({ text }: { text: string }) {
    return (
        <p style={{ fontSize: 13.5, color: C.faint, padding: '14px 0' }}>
            {text}
        </p>
    );
}

function StateDot({ state }: { state: string }) {
    const color =
        state === 'finished'
            ? C.green
            : state === 'cancelled'
              ? C.faint
              : state === 'pending'
                ? C.amber
                : C.greenDark;

    return (
        <span
            style={{
                width: 9,
                height: 9,
                borderRadius: '50%',
                background: color,
                flex: 'none',
            }}
        />
    );
}

function SeverityTag({ severity }: { severity: string }) {
    const map: Record<string, { c: string; b: string }> = {
        high: { c: C.danger, b: C.dangerTint },
        medium: { c: C.amber, b: C.amberTint },
        low: { c: C.green, b: C.greenTint },
    };
    const s = map[severity] ?? map.low;

    return (
        <span
            style={{
                flex: 'none',
                marginTop: 2,
                padding: '2px 9px',
                borderRadius: 999,
                fontSize: 11,
                fontWeight: 700,
                textTransform: 'capitalize',
                color: s.c,
                background: s.b,
            }}
        >
            {severity}
        </span>
    );
}

function Chip({ text }: { text: string }) {
    return (
        <span
            style={{
                flex: 'none',
                padding: '3px 10px',
                borderRadius: 999,
                fontSize: 12,
                fontWeight: 600,
                color: C.muted,
                background: C.cream,
                border: `1px solid ${C.borderSoft}`,
            }}
        >
            {text}
        </span>
    );
}

function ZoneTag({ zone }: { zone: string }) {
    const isTumo = zone === 'TUMO';

    return (
        <span
            style={{
                flex: 'none',
                padding: '2px 9px',
                borderRadius: 999,
                fontSize: 12,
                fontWeight: 600,
                color: isTumo ? C.green : C.amber,
                background: isTumo ? C.greenTint : C.amberTint,
            }}
        >
            {zone}
        </span>
    );
}

function ghostBtn(): React.CSSProperties {
    return {
        display: 'inline-flex',
        alignItems: 'center',
        gap: 5,
        padding: '5px 10px',
        borderRadius: 8,
        border: `1px solid ${C.border}`,
        background: C.card,
        color: C.muted,
        fontSize: 12.5,
        fontWeight: 600,
        cursor: 'pointer',
    };
}

function labelize(value: string): string {
    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) {
        return iso;
    }

    return d.toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
