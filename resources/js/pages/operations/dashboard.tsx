import { Head, Link, router, usePage } from '@inertiajs/react';
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
    Mail,
    MapPin,
    Menu,
    RefreshCw,
    TriangleAlert,
    Users,
    Wallet,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useState, type CSSProperties } from 'react';

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
.ops-link:hover{opacity:.7}
.ops-overlay{animation:ops-fade .18s ease}
@keyframes ops-fade{from{opacity:0}to{opacity:1}}
@keyframes ops-shimmer{0%{background-position:100% 0}100%{background-position:0 0}}
select.ops-select{font-family:inherit;font-size:13px;font-weight:600;color:${C.ink};background:${C.card};border:1px solid ${C.border};border-radius:8px;padding:5px 8px;outline:none;cursor:pointer}
select.ops-select:hover{border-color:${C.green}}
.ops-sidebar{width:256px;flex:none;background:${C.card};border-right:1px solid ${C.borderSoft};display:flex;flex-direction:column;padding:18px 14px}
.ops-mobile-top{display:none;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border-bottom:1px solid ${C.borderSoft};background:${C.card};position:sticky;top:0;z-index:30}
.ops-sidebar-backdrop{display:none;position:fixed;inset:0;background:rgba(26,26,26,.38);z-index:40;border:none;padding:0;cursor:pointer}
.ops-sidebar-close{display:none;margin-left:auto;padding:8px;border:none;background:transparent;cursor:pointer;align-items:center;justify-content:center}
@media(max-width:900px){
.ops-root{flex-direction:column!important}
.ops-sidebar{position:fixed;inset:0 auto 0 0;z-index:50;transform:translateX(-105%);transition:transform .22s ease;box-shadow:0 24px 48px -24px rgba(26,26,26,.35)}
.ops-sidebar.is-open{transform:translateX(0)}
.ops-sidebar-backdrop.is-open{display:block}
.ops-sidebar-close{display:flex}
.ops-mobile-top{display:flex}
.ops-main{padding-top:0!important}
}
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
    auth: {
        user: AuthUser;
        permissions: string[];
    };
    tenant: { id: number; title: string; description: string | null } | null;
    isTenantManager: boolean;
    assignableWorkerRoles: string[];
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
    source_label?: string;
    category?: string | null;
    category_label?: string | null;
    severity: string;
    severity_label?: string;
    status: string;
    status_label?: string;
    created_at: string | null;
    resolved_at?: string | null;
    raised_by?: { id: number; name: string; worker_role: string | null } | null;
    spaces?: { id: string; name: string; zone_class: string; floor: number }[];
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

type ProfileCard = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    account_type: string | null;
    avatar_url: string | null;
    job_title: string | null;
    company: string | null;
    location: string | null;
    website: string | null;
    bio: string | null;
    completion: number;
    member_since: string | null;
};

type View = 'overview' | 'requests' | 'tasks' | 'alerts' | 'events' | 'spaces' | 'team' | 'money';

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

async function postJson(url: string, body: unknown): Promise<Response> {
    return fetch(url, {
        method: 'POST',
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

const NAV: {
    view: View;
    label: string;
    icon: LucideIcon;
    perm?: string;
    managerOnly?: boolean;
}[] = [
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
    {
        view: 'team',
        label: 'Team',
        icon: Users,
        managerOnly: true,
    },
    {
        view: 'money',
        label: 'Money',
        icon: Wallet,
        managerOnly: true,
    },
];

export default function OperationsDashboard() {
    const { auth, tenant, isTenantManager, assignableWorkerRoles } =
        usePage<PageProps>().props;
    const [view, setView] = useState<View>('overview');
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const can = (perm?: string) => !perm || auth.permissions.includes(perm);
    const nav = NAV.filter(
        (item) =>
            can(item.perm) &&
            (!item.managerOnly || isTenantManager),
    );
    const activeNav = nav.find((item) => item.view === view);
    const initials = auth.user.name
        .split(' ')
        .map((w) => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    const selectView = (next: View) => {
        setView(next);
        setSidebarOpen(false);
    };

    const sidebarNav = (
        <>
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
                <button
                    type="button"
                    aria-label="Close menu"
                    className="ops-nav ops-sidebar-close"
                    onClick={() => setSidebarOpen(false)}
                >
                    <X size={18} />
                </button>
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
                        onClick={() => selectView(item.view)}
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
                {can('spaces.view') && (
                    <button
                        type="button"
                        className="ops-nav"
                        onClick={() => {
                            setSidebarOpen(false);
                            router.visit('/operations/map-calibration');
                        }}
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
                            color: '#3A3A3A',
                            cursor: 'pointer',
                            textAlign: 'left',
                        }}
                    >
                        <MapPin size={17} color={C.muted} />
                        Map setup
                    </button>
                )}
                <button
                    type="button"
                    className="ops-nav"
                    onClick={() => {
                        setSidebarOpen(false);
                        router.visit('/operations/manage-boring-things');
                    }}
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
                        color: '#3A3A3A',
                        cursor: 'pointer',
                        textAlign: 'left',
                    }}
                >
                    <Mail size={17} color={C.muted} />
                    Manage boring things
                </button>
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
        </>
    );

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

            <button
                type="button"
                aria-label="Close navigation menu"
                className={`ops-sidebar-backdrop${sidebarOpen ? ' is-open' : ''}`}
                onClick={() => setSidebarOpen(false)}
            />

            {/* ===== SIDEBAR ===== */}
            <aside
                className={`ops-sidebar${sidebarOpen ? ' is-open' : ''}`}
            >
                {sidebarNav}
            </aside>

            {/* ===== MAIN ===== */}
            <div style={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column' }}>
                <div className="ops-mobile-top">
                    <button
                        type="button"
                        aria-label="Open navigation menu"
                        className="ops-nav"
                        onClick={() => setSidebarOpen(true)}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 40,
                            height: 40,
                            borderRadius: 10,
                            border: `1px solid ${C.borderSoft}`,
                            background: C.card,
                            cursor: 'pointer',
                            flex: 'none',
                        }}
                    >
                        <Menu size={18} />
                    </button>
                    <div style={{ minWidth: 0, flex: 1 }}>
                        <div
                            style={{
                                fontSize: 15,
                                fontWeight: 700,
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                whiteSpace: 'nowrap',
                            }}
                        >
                            {activeNav?.label ?? 'Operations'}
                        </div>
                        <div
                            style={{
                                fontSize: 12,
                                color: C.muted,
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                whiteSpace: 'nowrap',
                            }}
                        >
                            {tenant?.title ?? 'Pyramid operations'}
                        </div>
                    </div>
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 36,
                            height: 36,
                            flex: 'none',
                            borderRadius: '50%',
                            background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                            color: '#fff',
                            fontWeight: 700,
                            fontSize: 13,
                        }}
                        title={auth.user.name}
                    >
                        {initials}
                    </span>
                </div>

            <main
                className="ops-main"
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
                    {view === 'alerts' && <AlertsView userId={auth.user.id} />}
                    {view === 'events' && <EventsView />}
                    {view === 'spaces' && <SpacesView />}
                    {view === 'team' && isTenantManager && (
                        <TeamView roles={assignableWorkerRoles} />
                    )}
                    {view === 'money' && isTenantManager && <MoneyView />}
                </div>
            </main>
            </div>
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

const KANBAN_COLUMNS: { state: string; label: string; accent: string }[] = [
    { state: 'pending', label: 'To do', accent: C.faint },
    { state: 'started', label: 'Started', accent: '#3B82C4' },
    { state: 'ongoing', label: 'In progress', accent: C.amber },
    { state: 'on_process', label: 'Ready', accent: '#7A5AF8' },
    { state: 'finished', label: 'Done', accent: C.green },
    { state: 'cancelled', label: 'Cancelled', accent: C.danger },
];

function TasksView({ user }: { user: AuthUser }) {
    const [scope, setScope] = useState<'mine' | 'all'>('mine');
    const { data, loading, reload } = useApi<{ data: Task[] }>(
        '/operations/tasks?per_page=200',
    );
    // Optimistic state overrides so cards move instantly on drop.
    const [moved, setMoved] = useState<Record<string, string>>({});

    const all = (data?.data ?? []).map((t) =>
        moved[t.id] ? { ...t, state: moved[t.id] } : t,
    );
    const tasks =
        scope === 'mine' ? all.filter((t) => t.worker?.id === user.id) : all;

    const move = async (taskId: string, state: string) => {
        setMoved((m) => ({ ...m, [taskId]: state }));
        await patchJson(`/operations/tasks/${taskId}`, { state });
        reload();
    };

    return (
        <>
            <Header
                title="My board"
                subtitle="Drag a task across the board as you work through it."
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

            {loading && tasks.length === 0 ? (
                <Panel title={null} loading>
                    <Empty text="Loading your board…" />
                </Panel>
            ) : tasks.length === 0 ? (
                <Panel title={null}>
                    <Empty text="No tasks here yet — accept an event request to have the AI plan the work." />
                </Panel>
            ) : (
                <KanbanBoard tasks={tasks} showWorker={scope === 'all'} onMove={move} />
            )}
        </>
    );
}

function KanbanBoard({
    tasks,
    showWorker,
    onMove,
}: {
    tasks: Task[];
    showWorker: boolean;
    onMove: (taskId: string, state: string) => void;
}) {
    const [dragId, setDragId] = useState<string | null>(null);
    const [overState, setOverState] = useState<string | null>(null);

    return (
        <div
            style={{
                display: 'flex',
                gap: 14,
                overflowX: 'auto',
                paddingBottom: 8,
                alignItems: 'flex-start',
            }}
        >
            {KANBAN_COLUMNS.map((col) => {
                const colTasks = tasks.filter((t) => t.state === col.state);
                const isOver = overState === col.state;

                return (
                    <div
                        key={col.state}
                        onDragOver={(e) => {
                            e.preventDefault();
                            setOverState(col.state);
                        }}
                        onDragLeave={() =>
                            setOverState((s) => (s === col.state ? null : s))
                        }
                        onDrop={() => {
                            if (dragId) {
                                onMove(dragId, col.state);
                            }
                            setDragId(null);
                            setOverState(null);
                        }}
                        style={{
                            width: 248,
                            flex: 'none',
                            background: isOver ? C.greenTint : C.cream,
                            border: `1px solid ${isOver ? C.green : C.borderSoft}`,
                            borderRadius: 14,
                            padding: 10,
                            transition: 'background .15s ease, border-color .15s ease',
                        }}
                    >
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 8,
                                padding: '4px 6px 10px',
                            }}
                        >
                            <span
                                style={{
                                    width: 9,
                                    height: 9,
                                    borderRadius: '50%',
                                    background: col.accent,
                                }}
                            />
                            <span style={{ fontSize: 13, fontWeight: 700 }}>
                                {col.label}
                            </span>
                            <span
                                style={{
                                    marginLeft: 'auto',
                                    fontSize: 12,
                                    fontWeight: 600,
                                    color: C.faint,
                                }}
                            >
                                {colTasks.length}
                            </span>
                        </div>

                        <div
                            style={{
                                display: 'flex',
                                flexDirection: 'column',
                                gap: 8,
                                minHeight: 60,
                            }}
                        >
                            {colTasks.map((task) => (
                                <KanbanCard
                                    key={task.id}
                                    task={task}
                                    accent={col.accent}
                                    showWorker={showWorker}
                                    dragging={dragId === task.id}
                                    onDragStart={() => setDragId(task.id)}
                                    onDragEnd={() => {
                                        setDragId(null);
                                        setOverState(null);
                                    }}
                                />
                            ))}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

function KanbanCard({
    task,
    accent,
    showWorker,
    dragging,
    onDragStart,
    onDragEnd,
}: {
    task: Task;
    accent: string;
    showWorker: boolean;
    dragging: boolean;
    onDragStart: () => void;
    onDragEnd: () => void;
}) {
    return (
        <div
            draggable
            onDragStart={onDragStart}
            onDragEnd={onDragEnd}
            style={{
                background: C.card,
                borderRadius: 11,
                border: `1px solid ${C.border}`,
                borderLeft: `3px solid ${accent}`,
                padding: '10px 12px',
                cursor: 'grab',
                opacity: dragging ? 0.4 : 1,
                boxShadow: '0 1px 2px rgba(26,26,26,0.04)',
            }}
        >
            <div style={{ fontSize: 13.5, fontWeight: 600, lineHeight: 1.35 }}>
                {task.name}
            </div>
            {task.event?.title && (
                <div style={{ fontSize: 12, color: C.muted, marginTop: 3 }}>
                    {task.event.title}
                </div>
            )}
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 6,
                    marginTop: 8,
                    flexWrap: 'wrap',
                }}
            >
                <span
                    style={{
                        fontSize: 10.5,
                        fontWeight: 700,
                        letterSpacing: '0.04em',
                        textTransform: 'uppercase',
                        color: C.faint,
                        background: C.cream,
                        padding: '2px 7px',
                        borderRadius: 6,
                    }}
                >
                    {task.phase_label}
                </span>
                {task.due_at && (
                    <span style={{ fontSize: 11.5, color: C.faint }}>
                        due {formatDate(task.due_at)}
                    </span>
                )}
                {showWorker && task.worker && (
                    <span
                        style={{
                            marginLeft: 'auto',
                            fontSize: 11.5,
                            fontWeight: 600,
                            color: C.green,
                        }}
                    >
                        {task.worker.name}
                    </span>
                )}
            </div>
        </div>
    );
}

/* ============================ ALERTS ============================ */

const SEVERITY_OPTIONS = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
];

const CATEGORY_OPTIONS = [
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'safety', label: 'Safety' },
    { value: 'equipment', label: 'Equipment' },
    { value: 'cleanliness', label: 'Cleanliness' },
    { value: 'security', label: 'Security' },
    { value: 'staffing', label: 'Staffing' },
    { value: 'scheduling', label: 'Scheduling' },
    { value: 'other', label: 'Other' },
];

const ALERT_SCOPES = [
    { key: 'open', label: 'Open' },
    { key: 'mine', label: 'Raised by me' },
    { key: 'all', label: 'All' },
] as const;

function AlertsView({ userId }: { userId: number }) {
    const [scope, setScope] = useState<'open' | 'mine' | 'all'>('open');
    const [raising, setRaising] = useState(false);
    const { data, loading, reload } = useApi<{ data: Alert[] }>(
        '/operations/alerts?per_page=100',
    );
    const all = data?.data ?? [];

    const alerts = all.filter((a) => {
        if (scope === 'mine') return a.raised_by?.id === userId;
        if (scope === 'open') return a.status !== 'dismissed' && a.status !== 'resolved';
        return true;
    });

    return (
        <>
            <div
                style={{
                    display: 'flex',
                    alignItems: 'flex-start',
                    justifyContent: 'space-between',
                    gap: 12,
                    marginBottom: 16,
                }}
            >
                <Header
                    title="Alerts"
                    subtitle="Flags from the team, the agents and the system — raise one if something needs attention."
                    onRefresh={reload}
                />
                <button
                    type="button"
                    onClick={() => setRaising(true)}
                    style={{
                        flex: 'none',
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 7,
                        padding: '9px 15px',
                        borderRadius: 10,
                        border: 'none',
                        background: C.danger,
                        color: '#fff',
                        fontSize: 13.5,
                        fontWeight: 700,
                        cursor: 'pointer',
                    }}
                >
                    <TriangleAlert size={15} />
                    Raise alert
                </button>
            </div>

            <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
                {ALERT_SCOPES.map((s) => (
                    <button
                        key={s.key}
                        type="button"
                        className="ops-pill"
                        onClick={() => setScope(s.key)}
                        style={{
                            padding: '7px 14px',
                            borderRadius: 999,
                            border: `1px solid ${scope === s.key ? C.green : C.border}`,
                            background: scope === s.key ? C.greenTint : C.card,
                            color: scope === s.key ? C.green : C.muted,
                            fontSize: 13,
                            fontWeight: 600,
                            cursor: 'pointer',
                        }}
                    >
                        {s.label}
                    </button>
                ))}
            </div>

            <Panel title={null} loading={loading}>
                {alerts.length === 0 ? (
                    <Empty text="No alerts here." />
                ) : (
                    alerts.map((alert) => (
                        <AlertRow key={alert.id} alert={alert} onChanged={reload} />
                    ))
                )}
            </Panel>

            {raising && (
                <RaiseAlertModal
                    onClose={() => setRaising(false)}
                    onRaised={() => {
                        setRaising(false);
                        reload();
                    }}
                />
            )}
        </>
    );
}

function AlertRow({ alert, onChanged }: { alert: Alert; onChanged: () => void }) {
    const [busy, setBusy] = useState(false);
    const act = async (action: 'read' | 'dismiss' | 'resolve') => {
        setBusy(true);
        await mutate(`/operations/alerts/${alert.id}/${action}`, 'PATCH');
        setBusy(false);
        onChanged();
    };

    const dim =
        alert.status === 'dismissed' ||
        alert.status === 'read' ||
        alert.status === 'resolved';
    const resolved = alert.status === 'resolved';

    return (
        <div
            style={{
                display: 'flex',
                alignItems: 'flex-start',
                gap: 12,
                padding: '14px 0',
                borderTop: `1px solid ${C.borderSoft}`,
                opacity: dim ? 0.65 : 1,
            }}
        >
            <SeverityTag severity={alert.severity} />
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                    <span style={{ fontSize: 14.5, fontWeight: 600 }}>{alert.title}</span>
                    {alert.category_label && (
                        <span
                            style={{
                                fontSize: 11,
                                fontWeight: 700,
                                color: C.greenDeep,
                                background: C.greenTint,
                                padding: '2px 8px',
                                borderRadius: 999,
                            }}
                        >
                            {alert.category_label}
                        </span>
                    )}
                    {resolved && (
                        <span style={{ fontSize: 11, fontWeight: 700, color: C.green, display: 'inline-flex', alignItems: 'center', gap: 4 }}>
                            <CheckCircle2 size={13} /> Resolved
                        </span>
                    )}
                </div>
                <div style={{ fontSize: 13, color: C.muted, marginTop: 3, lineHeight: 1.5 }}>
                    {alert.message}
                </div>

                {alert.spaces && alert.spaces.length > 0 && (
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginTop: 8 }}>
                        {alert.spaces.map((s) => (
                            <span
                                key={s.id}
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: 4,
                                    fontSize: 11.5,
                                    fontWeight: 600,
                                    color: C.ink,
                                    background: C.cream,
                                    border: `1px solid ${C.borderSoft}`,
                                    padding: '3px 9px',
                                    borderRadius: 7,
                                }}
                            >
                                <MapPin size={11} color={C.faint} />
                                {s.name}
                            </span>
                        ))}
                    </div>
                )}

                <div style={{ fontSize: 11.5, color: C.faint, marginTop: 7 }}>
                    {alert.raised_by
                        ? `${alert.raised_by.name}${alert.raised_by.worker_role ? ` · ${alert.raised_by.worker_role}` : ''}`
                        : alert.source_label ?? labelize(alert.source)}
                    {alert.created_at ? ` · ${formatDate(alert.created_at)}` : ''}
                </div>
            </div>

            {!resolved && alert.status !== 'dismissed' && (
                <div style={{ display: 'flex', gap: 6, flex: 'none' }}>
                    <button
                        type="button"
                        className="ops-btn"
                        onClick={() => act('resolve')}
                        disabled={busy}
                        style={{ ...ghostBtn(), color: C.green, borderColor: C.greenTint }}
                        title="Mark resolved"
                    >
                        <CheckCircle2 size={15} />
                        Resolve
                    </button>
                    <button
                        type="button"
                        className="ops-btn"
                        onClick={() => act('dismiss')}
                        disabled={busy}
                        style={ghostBtn()}
                    >
                        Dismiss
                    </button>
                </div>
            )}
        </div>
    );
}

function RaiseAlertModal({
    onClose,
    onRaised,
}: {
    onClose: () => void;
    onRaised: () => void;
}) {
    const { data: spaceData } = useApi<{ data: Space[] }>(
        '/operations/spaces?per_page=200',
    );
    const spaces = spaceData?.data ?? [];

    const [title, setTitle] = useState('');
    const [message, setMessage] = useState('');
    const [severity, setSeverity] = useState('medium');
    const [category, setCategory] = useState('maintenance');
    const [venueIds, setVenueIds] = useState<string[]>([]);
    const [venueSearch, setVenueSearch] = useState('');
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');

    const filteredSpaces = spaces.filter(
        (s) =>
            !venueSearch ||
            s.name.toLowerCase().includes(venueSearch.toLowerCase()) ||
            (s.room_code ?? '').toLowerCase().includes(venueSearch.toLowerCase()),
    );

    const toggleVenue = (id: string) =>
        setVenueIds((prev) =>
            prev.includes(id) ? prev.filter((v) => v !== id) : [...prev, id],
        );

    const submit = async () => {
        if (!title.trim() || !message.trim()) {
            setError('Give the alert a title and a description.');
            return;
        }
        setBusy(true);
        setError('');
        const res = await postJson('/operations/alerts', {
            title,
            message,
            severity,
            category,
            space_ids: venueIds,
        });
        setBusy(false);
        if (res.ok) {
            onRaised();
        } else {
            setError('Could not raise the alert. Please try again.');
        }
    };

    return (
        <div
            className="ops-overlay"
            onClick={onClose}
            style={{
                position: 'fixed',
                inset: 0,
                zIndex: 60,
                background: 'rgba(26,26,26,0.5)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: 20,
            }}
        >
            <div
                onClick={(e) => e.stopPropagation()}
                style={{
                    width: '100%',
                    maxWidth: 560,
                    maxHeight: '90vh',
                    overflowY: 'auto',
                    background: C.card,
                    borderRadius: 20,
                    border: `1px solid ${C.border}`,
                    boxShadow: '0 40px 90px -34px rgba(26,26,26,0.55)',
                    padding: '24px 24px 22px',
                }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: 11, marginBottom: 18 }}>
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 38,
                            height: 38,
                            borderRadius: 11,
                            background: C.dangerTint,
                            color: C.danger,
                        }}
                    >
                        <TriangleAlert size={19} />
                    </span>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 17, fontWeight: 800 }}>Raise an alert</div>
                        <div style={{ fontSize: 12.5, color: C.muted }}>
                            Flag an issue and tag the venues it affects.
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Close"
                        style={{ width: 32, height: 32, borderRadius: 9, border: `1px solid ${C.border}`, background: C.card, color: C.muted, cursor: 'pointer' }}
                    >
                        ✕
                    </button>
                </div>

                <FieldLabel>Title</FieldLabel>
                <input
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    placeholder="e.g. Broken projector in the main hall"
                    style={alertInput()}
                />

                <FieldLabel>What's going on?</FieldLabel>
                <textarea
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    rows={3}
                    placeholder="Describe the issue, where it is, and how urgent it is…"
                    style={{ ...alertInput(), resize: 'vertical' }}
                />

                <div style={{ display: 'flex', gap: 12 }}>
                    <div style={{ flex: 1 }}>
                        <FieldLabel>Severity</FieldLabel>
                        <select className="ops-select" value={severity} onChange={(e) => setSeverity(e.target.value)} style={{ width: '100%', padding: '10px 12px' }}>
                            {SEVERITY_OPTIONS.map((o) => (
                                <option key={o.value} value={o.value}>{o.label}</option>
                            ))}
                        </select>
                    </div>
                    <div style={{ flex: 1 }}>
                        <FieldLabel>Category</FieldLabel>
                        <select className="ops-select" value={category} onChange={(e) => setCategory(e.target.value)} style={{ width: '100%', padding: '10px 12px' }}>
                            {CATEGORY_OPTIONS.map((o) => (
                                <option key={o.value} value={o.value}>{o.label}</option>
                            ))}
                        </select>
                    </div>
                </div>

                <FieldLabel>
                    Related venues{venueIds.length ? ` (${venueIds.length})` : ''}
                </FieldLabel>
                <input
                    value={venueSearch}
                    onChange={(e) => setVenueSearch(e.target.value)}
                    placeholder="Search venues to tag…"
                    style={{ ...alertInput(), marginBottom: 8 }}
                />
                <div
                    style={{
                        maxHeight: 170,
                        overflowY: 'auto',
                        border: `1px solid ${C.border}`,
                        borderRadius: 10,
                        padding: 6,
                    }}
                >
                    {filteredSpaces.length === 0 ? (
                        <div style={{ padding: 10, fontSize: 13, color: C.faint }}>No venues found.</div>
                    ) : (
                        filteredSpaces.slice(0, 60).map((s) => {
                            const on = venueIds.includes(s.id);
                            return (
                                <button
                                    key={s.id}
                                    type="button"
                                    onClick={() => toggleVenue(s.id)}
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 9,
                                        width: '100%',
                                        textAlign: 'left',
                                        padding: '7px 9px',
                                        borderRadius: 8,
                                        border: 'none',
                                        background: on ? C.greenTint : 'transparent',
                                        cursor: 'pointer',
                                        fontFamily: 'inherit',
                                    }}
                                >
                                    <span
                                        style={{
                                            width: 17,
                                            height: 17,
                                            borderRadius: 5,
                                            border: `1.5px solid ${on ? C.green : C.border}`,
                                            background: on ? C.green : C.card,
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            flex: 'none',
                                        }}
                                    >
                                        {on && <CheckCircle2 size={12} color="#fff" />}
                                    </span>
                                    <span style={{ fontSize: 13, fontWeight: 500, flex: 1, minWidth: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                        {s.name}
                                    </span>
                                    <span style={{ fontSize: 11.5, color: C.faint }}>
                                        fl {s.floor}
                                    </span>
                                </button>
                            );
                        })
                    )}
                </div>

                {error && (
                    <div style={{ color: C.danger, fontSize: 13, marginTop: 12 }}>{error}</div>
                )}

                <button
                    type="button"
                    onClick={submit}
                    disabled={busy}
                    style={{
                        width: '100%',
                        marginTop: 18,
                        display: 'inline-flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        gap: 8,
                        padding: '12px',
                        borderRadius: 11,
                        border: 'none',
                        background: C.danger,
                        color: '#fff',
                        fontSize: 14.5,
                        fontWeight: 700,
                        cursor: busy ? 'not-allowed' : 'pointer',
                        opacity: busy ? 0.6 : 1,
                    }}
                >
                    {busy ? <RefreshCw size={16} className="ops-spin" /> : <TriangleAlert size={16} />}
                    {busy ? 'Raising…' : 'Raise alert'}
                </button>
            </div>
        </div>
    );
}

function FieldLabel({ children }: { children: React.ReactNode }) {
    return (
        <div
            style={{
                fontSize: 12,
                fontWeight: 700,
                letterSpacing: '0.03em',
                textTransform: 'uppercase',
                color: C.faint,
                margin: '14px 0 6px',
            }}
        >
            {children}
        </div>
    );
}

function alertInput(): CSSProperties {
    return {
        width: '100%',
        padding: '10px 12px',
        borderRadius: 10,
        border: `1px solid ${C.border}`,
        fontSize: 14,
        fontFamily: 'inherit',
        outline: 'none',
        color: C.ink,
        background: C.card,
    };
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
    const [showProfile, setShowProfile] = useState(false);
    const [planningEvent, setPlanningEvent] = useState<{
        id: string;
        title: string;
    } | null>(null);
    const registered =
        request.status === 'converted' || request.event_id != null;
    const price = request.price_agreed ?? request.price_suggested;

    const confirm = async () => {
        setBusy(true);
        const res = await postJson(
            `/operations/event-requests/${request.id}/convert`,
            {},
        );
        setBusy(false);
        onChanged();

        // Kick off the AI task planning for the freshly-created event.
        try {
            const json = await res.json();
            const eventId = json?.data?.id;
            if (eventId) {
                setPlanningEvent({
                    id: eventId,
                    title: request.title ?? 'this event',
                });
            }
        } catch {
            /* convert still succeeded; planning can be retried from Events */
        }
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
                    {request.submitter ? (
                        <>
                            {' · '}
                            <button
                                type="button"
                                className="ops-link"
                                onClick={() => setShowProfile(true)}
                                style={{
                                    padding: 0,
                                    border: 'none',
                                    background: 'none',
                                    font: 'inherit',
                                    color: C.green,
                                    fontWeight: 600,
                                    cursor: 'pointer',
                                    textDecoration: 'underline',
                                    textUnderlineOffset: 2,
                                }}
                            >
                                {request.submitter.name}
                            </button>
                        </>
                    ) : (
                        ''
                    )}
                </div>
            </div>

            {showProfile && request.submitter && (
                <ProfileModal
                    userId={request.submitter.id}
                    onClose={() => setShowProfile(false)}
                />
            )}

            {planningEvent && (
                <TaskPlanningModal
                    event={planningEvent}
                    onClose={() => setPlanningEvent(null)}
                />
            )}

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

/* ===================== SUBMITTER PROFILE MODAL ===================== */

function ProfileModal({
    userId,
    onClose,
}: {
    userId: number;
    onClose: () => void;
}) {
    const { data, loading } = useApi<{ data: ProfileCard }>(
        `/users/${userId}/profile`,
    );
    const profile = data?.data ?? null;

    const initials = (profile?.name ?? '?')
        .split(' ')
        .map((w) => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <div
            className="ops-overlay"
            onClick={onClose}
            style={{
                position: 'fixed',
                inset: 0,
                zIndex: 50,
                background: 'rgba(26,26,26,0.45)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: 20,
            }}
        >
            <div
                onClick={(e) => e.stopPropagation()}
                style={{
                    width: '100%',
                    maxWidth: 420,
                    background: C.card,
                    borderRadius: 18,
                    border: `1px solid ${C.border}`,
                    boxShadow: '0 28px 70px -30px rgba(26,26,26,0.5)',
                    overflow: 'hidden',
                }}
            >
                <div
                    style={{
                        height: 84,
                        background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                    }}
                />
                <div style={{ padding: '0 24px 24px', marginTop: -38 }}>
                    <div
                        style={{
                            width: 76,
                            height: 76,
                            borderRadius: '50%',
                            border: `3px solid ${C.card}`,
                            background: C.greenTint,
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            overflow: 'hidden',
                            color: C.greenDeep,
                            fontWeight: 700,
                            fontSize: 24,
                        }}
                    >
                        {profile?.avatar_url ? (
                            <img
                                src={profile.avatar_url}
                                alt={profile.name}
                                style={{
                                    width: '100%',
                                    height: '100%',
                                    objectFit: 'cover',
                                }}
                            />
                        ) : (
                            initials
                        )}
                    </div>

                    {loading && !profile ? (
                        <div
                            style={{
                                padding: '20px 0',
                                color: C.muted,
                                fontSize: 14,
                            }}
                        >
                            Loading profile…
                        </div>
                    ) : profile ? (
                        <>
                            <div
                                style={{
                                    marginTop: 12,
                                    fontSize: 19,
                                    fontWeight: 700,
                                }}
                            >
                                {profile.name}
                            </div>
                            {(profile.job_title || profile.company) && (
                                <div
                                    style={{
                                        fontSize: 13.5,
                                        color: C.muted,
                                        marginTop: 2,
                                    }}
                                >
                                    {[profile.job_title, profile.company]
                                        .filter(Boolean)
                                        .join(' · ')}
                                </div>
                            )}

                            {profile.bio && (
                                <p
                                    style={{
                                        fontSize: 13.5,
                                        color: C.ink,
                                        lineHeight: 1.55,
                                        marginTop: 14,
                                    }}
                                >
                                    {profile.bio}
                                </p>
                            )}

                            <div
                                style={{
                                    marginTop: 16,
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: 8,
                                }}
                            >
                                <ProfileRow label="Email" value={profile.email} />
                                <ProfileRow
                                    label="Phone"
                                    value={profile.phone}
                                />
                                <ProfileRow
                                    label="Location"
                                    value={profile.location}
                                />
                                <ProfileRow
                                    label="Website"
                                    value={profile.website}
                                />
                            </div>

                            {profile.completion < 100 && (
                                <div
                                    style={{
                                        marginTop: 16,
                                        fontSize: 12,
                                        color: C.faint,
                                    }}
                                >
                                    Profile {profile.completion}% complete
                                </div>
                            )}
                        </>
                    ) : (
                        <div
                            style={{
                                padding: '20px 0',
                                color: C.muted,
                                fontSize: 14,
                            }}
                        >
                            Couldn't load this profile.
                        </div>
                    )}

                    <button
                        type="button"
                        onClick={onClose}
                        style={{
                            marginTop: 20,
                            width: '100%',
                            padding: '10px',
                            borderRadius: 10,
                            border: `1px solid ${C.border}`,
                            background: C.cream,
                            color: C.ink,
                            fontSize: 13.5,
                            fontWeight: 600,
                            cursor: 'pointer',
                        }}
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    );
}

function ProfileRow({
    label,
    value,
}: {
    label: string;
    value: string | null;
}) {
    if (!value) {
        return null;
    }

    return (
        <div style={{ display: 'flex', gap: 10, fontSize: 13 }}>
            <span style={{ width: 70, flex: 'none', color: C.faint }}>
                {label}
            </span>
            <span
                style={{
                    color: C.ink,
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                }}
            >
                {value}
            </span>
        </div>
    );
}

/* ===================== AI TASK PLANNING MODAL ===================== */

type PlannedTask = {
    id: string;
    name: string;
    description: string | null;
    phase: string;
    phase_label: string;
    worker: { id: number; name: string; role: string | null } | null;
};

function TaskPlanningModal({
    event,
    onClose,
}: {
    event: { id: string; title: string };
    onClose: () => void;
}) {
    const [status, setStatus] = useState<'planning' | 'done' | 'error'>(
        'planning',
    );
    const [tasks, setTasks] = useState<PlannedTask[]>([]);
    const [summary, setSummary] = useState('');

    useEffect(() => {
        let active = true;

        postJson(`/operations/events/${event.id}/plan-tasks`, {})
            .then((r) => r.json())
            .then((json) => {
                if (!active) {
                    return;
                }
                const data = json?.data ?? {};
                setTasks(data.tasks ?? []);
                setSummary(data.summary ?? '');
                setStatus('done');
            })
            .catch(() => active && setStatus('error'));

        return () => {
            active = false;
        };
    }, [event.id]);

    // Group the plan by worker so the assignment is obvious.
    const byWorker = new Map<string, { name: string; role: string | null; items: PlannedTask[] }>();
    for (const task of tasks) {
        const key = task.worker ? String(task.worker.id) : 'unassigned';
        if (!byWorker.has(key)) {
            byWorker.set(key, {
                name: task.worker?.name ?? 'Unassigned',
                role: task.worker?.role ?? null,
                items: [],
            });
        }
        byWorker.get(key)!.items.push(task);
    }

    return (
        <div
            className="ops-overlay"
            onClick={onClose}
            style={{
                position: 'fixed',
                inset: 0,
                zIndex: 60,
                background: 'rgba(26,26,26,0.5)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: 20,
            }}
        >
            <div
                onClick={(e) => e.stopPropagation()}
                style={{
                    width: '100%',
                    maxWidth: 540,
                    maxHeight: '88vh',
                    overflowY: 'auto',
                    background: C.card,
                    borderRadius: 20,
                    border: `1px solid ${C.border}`,
                    boxShadow: '0 36px 80px -32px rgba(26,26,26,0.55)',
                    padding: '26px 26px 22px',
                }}
            >
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 11,
                        marginBottom: 6,
                    }}
                >
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 38,
                            height: 38,
                            borderRadius: 11,
                            background: C.greenTint,
                            color: C.green,
                        }}
                    >
                        {status === 'planning' ? (
                            <RefreshCw size={19} className="ops-spin" />
                        ) : (
                            <ListChecks size={19} />
                        )}
                    </span>
                    <div>
                        <div style={{ fontSize: 17, fontWeight: 800 }}>
                            {status === 'planning'
                                ? 'Planning the work…'
                                : 'Work plan ready'}
                        </div>
                        <div style={{ fontSize: 12.5, color: C.muted }}>
                            {event.title}
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Close"
                        style={{
                            marginLeft: 'auto',
                            width: 32,
                            height: 32,
                            borderRadius: 9,
                            border: `1px solid ${C.border}`,
                            background: C.card,
                            color: C.muted,
                            cursor: 'pointer',
                        }}
                    >
                        ✕
                    </button>
                </div>

                {status === 'planning' && (
                    <div style={{ padding: '26px 4px', color: C.muted }}>
                        <p style={{ fontSize: 14, margin: '0 0 14px' }}>
                            The AI is reading the team roster and splitting the
                            event into tasks, assigning each by role…
                        </p>
                        {[0, 1, 2].map((i) => (
                            <div
                                key={i}
                                style={{
                                    height: 44,
                                    borderRadius: 10,
                                    background: `linear-gradient(90deg, ${C.cream} 25%, ${C.borderSoft} 37%, ${C.cream} 63%)`,
                                    backgroundSize: '400% 100%',
                                    animation: 'ops-shimmer 1.3s ease infinite',
                                    marginBottom: 8,
                                }}
                            />
                        ))}
                    </div>
                )}

                {status === 'error' && (
                    <div style={{ padding: '20px 4px', color: C.danger, fontSize: 14 }}>
                        The planner couldn't finish. You can retry from the
                        event later.
                    </div>
                )}

                {status === 'done' && (
                    <div style={{ marginTop: 10 }}>
                        <div
                            style={{
                                fontSize: 13.5,
                                color: C.ink,
                                background: 'rgba(16,130,91,0.07)',
                                borderRadius: 10,
                                padding: '10px 12px',
                                marginBottom: 16,
                            }}
                        >
                            {tasks.length > 0
                                ? `${tasks.length} tasks created and assigned across the team.`
                                : summary || 'No tasks were created.'}
                        </div>

                        {Array.from(byWorker.values()).map((group, gi) => (
                            <div key={gi} style={{ marginBottom: 14 }}>
                                <div
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 8,
                                        marginBottom: 6,
                                    }}
                                >
                                    <span style={{ fontSize: 14, fontWeight: 700 }}>
                                        {group.name}
                                    </span>
                                    {group.role && (
                                        <span
                                            style={{
                                                fontSize: 11,
                                                fontWeight: 600,
                                                color: C.green,
                                                background: C.greenTint,
                                                padding: '2px 8px',
                                                borderRadius: 999,
                                            }}
                                        >
                                            {group.role}
                                        </span>
                                    )}
                                    <span
                                        style={{
                                            marginLeft: 'auto',
                                            fontSize: 12,
                                            color: C.faint,
                                        }}
                                    >
                                        {group.items.length} task
                                        {group.items.length === 1 ? '' : 's'}
                                    </span>
                                </div>
                                {group.items.map((task) => (
                                    <div
                                        key={task.id}
                                        style={{
                                            display: 'flex',
                                            gap: 10,
                                            padding: '8px 0',
                                            borderTop: `1px solid ${C.borderSoft}`,
                                        }}
                                    >
                                        <span
                                            style={{
                                                fontSize: 10.5,
                                                fontWeight: 700,
                                                textTransform: 'uppercase',
                                                letterSpacing: '0.04em',
                                                color: C.faint,
                                                flex: 'none',
                                                marginTop: 2,
                                            }}
                                        >
                                            {task.phase_label}
                                        </span>
                                        <span style={{ fontSize: 13.5 }}>
                                            {task.name}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ))}

                        <button
                            type="button"
                            onClick={onClose}
                            style={{
                                width: '100%',
                                marginTop: 8,
                                padding: '11px',
                                borderRadius: 11,
                                border: 'none',
                                background: C.green,
                                color: '#fff',
                                fontSize: 14,
                                fontWeight: 700,
                                cursor: 'pointer',
                            }}
                        >
                            Done
                        </button>
                    </div>
                )}
            </div>
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

/* ============================ TEAM (MANAGERS) ============================ */

type TeamWorker = {
    id: number;
    name: string;
    email: string;
    worker_role: string | null;
    open_tasks_count: number;
    created_at: string | null;
};

type TeamPayload = {
    workers: TeamWorker[];
    assignable_roles: string[];
    stats: {
        total_workers: number;
        active_tasks: number;
        tasks_due_this_week: number;
        roles: Record<string, number>;
    };
};

function TeamView({ roles }: { roles: string[] }) {
    const { data, loading, reload } = useApi<{ data: TeamPayload }>(
        '/operations/team',
    );
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('password');
    const [workerRole, setWorkerRole] = useState(roles[0] ?? '');
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const workers = data?.data.workers ?? [];
    const assignableRoles = data?.data.assignable_roles ?? roles;
    const stats = data?.data.stats;

    useEffect(() => {
        if (assignableRoles.length > 0 && !assignableRoles.includes(workerRole)) {
            setWorkerRole(assignableRoles[0]);
        }
    }, [assignableRoles, workerRole]);

    const createWorker = async () => {
        setSubmitting(true);
        setError(null);

        try {
            const response = await postJson('/operations/team', {
                name,
                email,
                password,
                worker_role: workerRole,
            });

            if (!response.ok) {
                const body = (await response.json()) as {
                    message?: string;
                    errors?: Record<string, string[]>;
                };
                const firstError = body.errors
                    ? Object.values(body.errors)[0]?.[0]
                    : body.message;
                setError(firstError ?? 'Could not create worker.');
                return;
            }

            setName('');
            setEmail('');
            setPassword('password');
            reload();
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <>
            <Header
                title="Team"
                subtitle="Manage branch staff, roles, and workload."
                onRefresh={reload}
            />

            {stats && (
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
                        gap: 12,
                        marginBottom: 18,
                    }}
                >
                    <StatCard label="Team members" value={String(stats.total_workers)} />
                    <StatCard label="Active tasks" value={String(stats.active_tasks)} />
                    <StatCard
                        label="Due this week"
                        value={String(stats.tasks_due_this_week)}
                    />
                    <StatCard
                        label="Roles covered"
                        value={String(Object.keys(stats.roles).length)}
                    />
                </div>
            )}

            {stats && Object.keys(stats.roles).length > 0 && (
                <Panel title="Roles on the floor" loading={false}>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                        {Object.entries(stats.roles).map(([role, count]) => (
                            <span
                                key={role}
                                style={{
                                    fontSize: 12,
                                    fontWeight: 600,
                                    padding: '6px 12px',
                                    borderRadius: 999,
                                    background: C.greenTint,
                                    color: C.greenDark,
                                }}
                            >
                                {role} · {count}
                            </span>
                        ))}
                    </div>
                </Panel>
            )}

            <div style={{ height: 18 }} />
            <Panel title="Add worker" loading={false}>
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))',
                        gap: 12,
                    }}
                >
                    <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                            Name
                        </span>
                        <input
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            style={inputStyle}
                        />
                    </label>
                    <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                            Email
                        </span>
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            style={inputStyle}
                        />
                    </label>
                    <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                            Password
                        </span>
                        <input
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            style={inputStyle}
                        />
                    </label>
                    <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                            Role
                        </span>
                        <select
                            className="ops-select"
                            value={workerRole}
                            onChange={(e) => setWorkerRole(e.target.value)}
                        >
                            {assignableRoles.map((role) => (
                                <option key={role} value={role}>
                                    {role}
                                </option>
                            ))}
                        </select>
                    </label>
                </div>
                {error && (
                    <p style={{ color: C.danger, fontSize: 13, marginTop: 12 }}>
                        {error}
                    </p>
                )}
                <button
                    type="button"
                    disabled={submitting || !name || !email || !workerRole}
                    onClick={createWorker}
                    style={{
                        marginTop: 14,
                        padding: '10px 16px',
                        borderRadius: 9,
                        border: 'none',
                        background: C.green,
                        color: '#fff',
                        fontWeight: 700,
                        fontSize: 13,
                        cursor: submitting ? 'wait' : 'pointer',
                        opacity: submitting ? 0.7 : 1,
                    }}
                >
                    {submitting ? 'Creating…' : 'Create worker'}
                </button>
            </Panel>

            <div style={{ height: 18 }} />

            <Panel title="Branch roster" loading={loading}>
                {workers.length === 0 ? (
                    <Empty text="No workers yet." />
                ) : (
                    workers.map((worker) => (
                        <div
                            key={worker.id}
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
                                    {worker.name}
                                </div>
                                <div style={{ fontSize: 12.5, color: C.muted }}>
                                    {worker.email}
                                </div>
                            </div>
                            <span
                                style={{
                                    fontSize: 12,
                                    fontWeight: 600,
                                    padding: '4px 10px',
                                    borderRadius: 999,
                                    background: C.greenTint,
                                    color: C.greenDark,
                                }}
                            >
                                {worker.worker_role ?? 'Worker'}
                            </span>
                            {worker.open_tasks_count > 0 && (
                                <span
                                    style={{
                                        fontSize: 12,
                                        fontWeight: 600,
                                        padding: '4px 10px',
                                        borderRadius: 999,
                                        background: C.amberTint,
                                        color: C.amber,
                                    }}
                                >
                                    {worker.open_tasks_count} open
                                </span>
                            )}
                        </div>
                    ))
                )}
            </Panel>
        </>
    );
}

function StatCard({ label, value }: { label: string; value: string }) {
    return (
        <div
            className="ops-stat"
            style={{
                background: C.card,
                border: `1px solid ${C.borderSoft}`,
                borderRadius: 14,
                padding: '16px 18px',
            }}
        >
            <div style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                {label}
            </div>
            <div
                style={{
                    fontSize: 26,
                    fontWeight: 800,
                    letterSpacing: '-0.02em',
                    marginTop: 6,
                }}
            >
                {value}
            </div>
        </div>
    );
}

/* ============================ MONEY (MANAGERS) ============================ */

type FinanceSummary = {
    collected_revenue: number;
    outstanding: number;
    pending_quotes: number;
    expenses_ytd: number;
    annual_budget: number;
    budget_remaining: number;
    operating_reserve: number;
    net_position: number;
};

type FinanceInvoice = {
    id: string;
    reference: string;
    title: string;
    amount: number;
    amount_paid: number;
    balance_due: number;
    status: string;
    status_label: string;
    due_at: string | null;
};

type FinanceExpense = {
    id: string;
    category_label: string;
    title: string;
    amount: number;
    incurred_at: string;
};

type FinancePayload = {
    profile: { annual_budget: number; operating_reserve: number; currency: string };
    summary: FinanceSummary;
    revenue_by_category: { category: string; label: string; total: number }[];
    invoices: FinanceInvoice[];
    expenses: FinanceExpense[];
    recent_payments: {
        id: string;
        amount: number;
        method_label: string;
        paid_at: string | null;
        invoice: { reference: string | null; title: string | null };
    }[];
};

function formatEuro(amount: number, currency = 'EUR'): string {
    return new Intl.NumberFormat('en-IE', {
        style: 'currency',
        currency,
        maximumFractionDigits: 0,
    }).format(amount);
}

function invoiceStatusColor(status: string): string {
    if (status === 'paid') return C.green;
    if (status === 'overdue') return C.danger;
    if (status === 'partial') return C.amber;
    return C.muted;
}

function MoneyView() {
    const { data, loading, reload } = useApi<{ data: FinancePayload }>(
        '/operations/finance',
    );
    const [payInvoiceId, setPayInvoiceId] = useState<string | null>(null);
    const [payAmount, setPayAmount] = useState('');
    const [expenseTitle, setExpenseTitle] = useState('');
    const [expenseAmount, setExpenseAmount] = useState('');
    const [expenseCategory, setExpenseCategory] = useState('utilities');
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const finance = data?.data;
    const summary = finance?.summary;
    const currency = finance?.profile.currency ?? 'EUR';

    const recordPayment = async () => {
        if (!payInvoiceId || !payAmount) return;
        setSubmitting(true);
        setError(null);
        try {
            const response = await postJson('/operations/finance/payments', {
                invoice_id: payInvoiceId,
                amount: payAmount,
                method: 'bank_transfer',
            });
            if (!response.ok) {
                const body = (await response.json()) as { message?: string };
                setError(body.message ?? 'Could not record payment.');
                return;
            }
            setPayInvoiceId(null);
            setPayAmount('');
            reload();
        } finally {
            setSubmitting(false);
        }
    };

    const recordExpense = async () => {
        if (!expenseTitle || !expenseAmount) return;
        setSubmitting(true);
        setError(null);
        try {
            const response = await postJson('/operations/finance/expenses', {
                category: expenseCategory,
                title: expenseTitle,
                amount: expenseAmount,
                incurred_at: new Date().toISOString().slice(0, 10),
            });
            if (!response.ok) {
                const body = (await response.json()) as { message?: string };
                setError(body.message ?? 'Could not record expense.');
                return;
            }
            setExpenseTitle('');
            setExpenseAmount('');
            reload();
        } finally {
            setSubmitting(false);
        }
    };

    const budgetUsedPct =
        summary && summary.annual_budget > 0
            ? Math.min(
                  100,
                  Math.round((summary.expenses_ytd / summary.annual_budget) * 100),
              )
            : 0;

    return (
        <>
            <Header
                title="Money"
                subtitle="Branch revenue, collections, budgets, and operating costs."
                onRefresh={reload}
            />

            {summary && (
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))',
                        gap: 12,
                        marginBottom: 18,
                    }}
                >
                    <StatCard
                        label="Collected"
                        value={formatEuro(summary.collected_revenue, currency)}
                    />
                    <StatCard
                        label="Outstanding"
                        value={formatEuro(summary.outstanding, currency)}
                    />
                    <StatCard
                        label="Open quotes"
                        value={formatEuro(summary.pending_quotes, currency)}
                    />
                    <StatCard
                        label="Net position"
                        value={formatEuro(summary.net_position, currency)}
                    />
                </div>
            )}

            {summary && (
                <Panel title="Annual budget" loading={loading}>
                    <div
                        style={{
                            display: 'flex',
                            justifyContent: 'space-between',
                            fontSize: 13,
                            marginBottom: 8,
                        }}
                    >
                        <span style={{ color: C.muted }}>
                            Spent {formatEuro(summary.expenses_ytd, currency)} of{' '}
                            {formatEuro(summary.annual_budget, currency)}
                        </span>
                        <span style={{ fontWeight: 700 }}>
                            {budgetUsedPct}% used ·{' '}
                            {formatEuro(summary.budget_remaining, currency)} left
                        </span>
                    </div>
                    <div
                        style={{
                            height: 10,
                            borderRadius: 999,
                            background: C.borderSoft,
                            overflow: 'hidden',
                        }}
                    >
                        <div
                            style={{
                                width: `${budgetUsedPct}%`,
                                height: '100%',
                                background: `linear-gradient(90deg, ${C.green}, ${C.greenDark})`,
                            }}
                        />
                    </div>
                    <p style={{ fontSize: 12.5, color: C.muted, marginTop: 10 }}>
                        Operating reserve:{' '}
                        {formatEuro(summary.operating_reserve, currency)}
                    </p>
                </Panel>
            )}

            <div style={{ height: 18 }} />

            {(finance?.revenue_by_category?.length ?? 0) > 0 && (
                <>
                    <Panel title="Quote pipeline by category" loading={loading}>
                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 10 }}>
                            {finance?.revenue_by_category.map((row) => (
                                <span
                                    key={row.category}
                                    style={{
                                        fontSize: 12,
                                        fontWeight: 600,
                                        padding: '8px 12px',
                                        borderRadius: 10,
                                        border: `1px solid ${C.border}`,
                                        background: C.card,
                                    }}
                                >
                                    {row.label}: {formatEuro(row.total, currency)}
                                </span>
                            ))}
                        </div>
                    </Panel>
                    <div style={{ height: 18 }} />
                </>
            )}

            <Panel title="Invoices & collections" loading={loading}>
                {(finance?.invoices ?? []).length === 0 ? (
                    <Empty text="No invoices yet." />
                ) : (
                    (finance?.invoices ?? []).map((invoice) => (
                        <div
                            key={invoice.id}
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                alignItems: 'center',
                                gap: 12,
                                padding: '12px 0',
                                borderTop: `1px solid ${C.borderSoft}`,
                            }}
                        >
                            <div style={{ flex: 1, minWidth: 180 }}>
                                <div style={{ fontSize: 14.5, fontWeight: 600 }}>
                                    {invoice.title}
                                </div>
                                <div style={{ fontSize: 12.5, color: C.muted }}>
                                    {invoice.reference} ·{' '}
                                    {formatEuro(invoice.amount, currency)}
                                    {invoice.balance_due > 0 &&
                                        ` · due ${formatEuro(invoice.balance_due, currency)}`}
                                </div>
                            </div>
                            <span
                                style={{
                                    fontSize: 12,
                                    fontWeight: 700,
                                    color: invoiceStatusColor(invoice.status),
                                }}
                            >
                                {invoice.status_label}
                            </span>
                            {invoice.balance_due > 0 && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setPayInvoiceId(invoice.id);
                                        setPayAmount(String(invoice.balance_due));
                                    }}
                                    style={{
                                        padding: '7px 12px',
                                        borderRadius: 8,
                                        border: `1px solid ${C.border}`,
                                        background: C.card,
                                        fontSize: 12,
                                        fontWeight: 600,
                                        cursor: 'pointer',
                                    }}
                                >
                                    Record payment
                                </button>
                            )}
                        </div>
                    ))
                )}
            </Panel>

            {payInvoiceId && (
                <>
                    <div style={{ height: 14 }} />
                    <Panel title="Record payment" loading={false}>
                        <div
                            style={{
                                display: 'grid',
                                gridTemplateColumns: '1fr auto',
                                gap: 10,
                                alignItems: 'end',
                            }}
                        >
                            <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                                    Amount ({currency})
                                </span>
                                <input
                                    value={payAmount}
                                    onChange={(e) => setPayAmount(e.target.value)}
                                    style={inputStyle}
                                />
                            </label>
                            <button
                                type="button"
                                disabled={submitting}
                                onClick={recordPayment}
                                style={{
                                    padding: '10px 16px',
                                    borderRadius: 9,
                                    border: 'none',
                                    background: C.green,
                                    color: '#fff',
                                    fontWeight: 700,
                                    fontSize: 13,
                                    cursor: 'pointer',
                                }}
                            >
                                Save payment
                            </button>
                        </div>
                    </Panel>
                </>
            )}

            <div style={{ height: 18 }} />

            <Panel title="Log operating expense" loading={false}>
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))',
                        gap: 12,
                    }}
                >
                    <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                            Title
                        </span>
                        <input
                            value={expenseTitle}
                            onChange={(e) => setExpenseTitle(e.target.value)}
                            style={inputStyle}
                        />
                    </label>
                    <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                            Amount
                        </span>
                        <input
                            value={expenseAmount}
                            onChange={(e) => setExpenseAmount(e.target.value)}
                            style={inputStyle}
                        />
                    </label>
                    <label style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: C.muted }}>
                            Category
                        </span>
                        <select
                            className="ops-select"
                            value={expenseCategory}
                            onChange={(e) => setExpenseCategory(e.target.value)}
                        >
                            <option value="staffing">Staffing</option>
                            <option value="utilities">Utilities</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="marketing">Marketing</option>
                            <option value="supplies">Supplies</option>
                            <option value="other">Other</option>
                        </select>
                    </label>
                </div>
                {error && (
                    <p style={{ color: C.danger, fontSize: 13, marginTop: 12 }}>
                        {error}
                    </p>
                )}
                <button
                    type="button"
                    disabled={submitting || !expenseTitle || !expenseAmount}
                    onClick={recordExpense}
                    style={{
                        marginTop: 14,
                        padding: '10px 16px',
                        borderRadius: 9,
                        border: 'none',
                        background: C.green,
                        color: '#fff',
                        fontWeight: 700,
                        fontSize: 13,
                        cursor: 'pointer',
                    }}
                >
                    Add expense
                </button>
            </Panel>

            <div style={{ height: 18 }} />

            <Panel title="Recent expenses" loading={loading}>
                {(finance?.expenses ?? []).length === 0 ? (
                    <Empty text="No expenses logged." />
                ) : (
                    (finance?.expenses ?? []).map((expense) => (
                        <div
                            key={expense.id}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 12,
                                padding: '12px 0',
                                borderTop: `1px solid ${C.borderSoft}`,
                            }}
                        >
                            <div style={{ flex: 1 }}>
                                <div style={{ fontSize: 14.5, fontWeight: 600 }}>
                                    {expense.title}
                                </div>
                                <div style={{ fontSize: 12.5, color: C.muted }}>
                                    {expense.category_label} · {expense.incurred_at}
                                </div>
                            </div>
                            <span style={{ fontSize: 14, fontWeight: 700 }}>
                                {formatEuro(expense.amount, currency)}
                            </span>
                        </div>
                    ))
                )}
            </Panel>
        </>
    );
}

const inputStyle: CSSProperties = {
    width: '100%',
    padding: '9px 11px',
    borderRadius: 8,
    border: `1px solid ${C.border}`,
    background: C.card,
    fontSize: 13,
    fontWeight: 500,
    color: C.ink,
};

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
