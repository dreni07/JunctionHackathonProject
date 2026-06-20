import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CalendarClock,
    CheckCircle2,
    Clock,
    Coins,
    MapPin,
    Radio,
    Users,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { PyramidMap } from '@/components/pyramid-map';

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
    danger: '#B4453A',
};

const STATE_COLOR: Record<string, string> = {
    pending: C.faint,
    started: '#3B82C4',
    ongoing: C.amber,
    on_process: '#7A5AF8',
    finished: C.green,
    cancelled: C.danger,
};

const css = `
.mv-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:${C.cream};color:${C.ink};min-height:100vh}
.mv-root *{box-sizing:border-box}
.mv-ring circle{transition:stroke-dashoffset .8s cubic-bezier(.2,.8,.2,1)}
@keyframes mv-live{0%,100%{opacity:1}50%{opacity:.35}}
.mv-live-dot{animation:mv-live 1.4s ease-in-out infinite}
@keyframes mv-pop{from{transform:scale(.9);opacity:.4}to{transform:scale(1);opacity:1}}
.mv-seg{animation:mv-pop .3s ease}
`;

type EventDetail = {
    id: string;
    title: string | null;
    description: string | null;
    event_type_label: string | null;
    status: string;
    status_label: string;
    start_time: string | null;
    end_time: string | null;
    attendees: number | null;
    price: number;
    venue: {
        name: string;
        zone_class: string;
        floor: number;
        capacity: number;
        box_ref: string | null;
        location_geometry: { x: number; y: number; level?: number } | null;
    } | null;
};

type ProgressTask = {
    id: string;
    name: string;
    phase: string;
    phase_label: string;
    state: string;
    state_label: string;
    worker: { id: number; name: string; worker_role: string | null } | null;
};

type Progress = {
    readiness: number;
    total: number;
    by_state: Record<string, number>;
    tasks: ProgressTask[];
};

function money(n: number): string {
    return '€' + Math.round(n).toLocaleString();
}

function fullDate(iso: string | null): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

type Phase = 'before' | 'live' | 'done';

function phaseOf(start: string | null, end: string | null): Phase {
    const now = Date.now();
    if (start && now < new Date(start).getTime()) return 'before';
    if (end && now > new Date(end).getTime()) return 'done';
    return 'live';
}

export default function MyEventShow({
    event,
    progress: initialProgress,
}: {
    event: EventDetail;
    progress: Progress;
}) {
    const [progress, setProgress] = useState<Progress>(initialProgress);

    // Poll readiness for a live feel.
    useEffect(() => {
        let active = true;
        const tick = () => {
            fetch(`/my-events/${event.id}/progress`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            })
                .then((r) => (r.ok ? r.json() : null))
                .then((j) => {
                    if (active && j?.data) setProgress(j.data);
                })
                .catch(() => {});
        };
        const id = window.setInterval(tick, 5000);

        return () => {
            active = false;
            window.clearInterval(id);
        };
    }, [event.id]);

    const phases = ['setup', 'during', 'teardown'];
    const grouped = phases
        .map((p) => ({
            key: p,
            label:
                progress.tasks.find((t) => t.phase === p)?.phase_label ??
                (p === 'setup' ? 'Setup' : p === 'during' ? 'During event' : 'Teardown'),
            tasks: progress.tasks.filter((t) => t.phase === p),
        }))
        .filter((g) => g.tasks.length > 0);

    return (
        <div className="mv-root">
            <Head title={event.title ?? 'Event'}>
                <link
                    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap"
                    rel="stylesheet"
                />
            </Head>
            <style dangerouslySetInnerHTML={{ __html: css }} />

            <header
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 14,
                    padding: '16px 26px',
                    borderBottom: `1px solid ${C.borderSoft}`,
                    background: C.card,
                }}
            >
                <Link
                    href="/my-events"
                    style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 6,
                        fontSize: 13.5,
                        color: C.muted,
                        textDecoration: 'none',
                    }}
                >
                    <ArrowLeft size={15} /> My events
                </Link>
            </header>

            <div style={{ maxWidth: 1040, margin: '0 auto', padding: '28px 24px 60px' }}>
                {/* title */}
                <div style={{ marginBottom: 20 }}>
                    <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: C.green }}>
                        {event.event_type_label ?? 'Event'}
                    </div>
                    <h1 style={{ fontSize: 30, fontWeight: 800, margin: '4px 0 0', letterSpacing: '-0.02em' }}>
                        {event.title ?? 'Your event'}
                    </h1>
                </div>

                {/* countdown */}
                <Countdown start={event.start_time} end={event.end_time} />

                {/* two columns: details + readiness */}
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: '1fr 1fr',
                        gap: 16,
                        marginTop: 16,
                    }}
                >
                    {/* details */}
                    <div style={{ background: C.card, border: `1px solid ${C.border}`, borderRadius: 18, padding: 22 }}>
                        <SectionTitle>Event details</SectionTitle>
                        <DetailRow icon={CalendarClock} label="When" value={fullDate(event.start_time)} />
                        <DetailRow icon={Clock} label="Until" value={fullDate(event.end_time)} />
                        <DetailRow
                            icon={MapPin}
                            label="Venue"
                            value={
                                event.venue
                                    ? `${event.venue.name} · ${event.venue.zone_class} · floor ${event.venue.floor}`
                                    : 'To be confirmed'
                            }
                        />
                        <DetailRow icon={Users} label="Guests" value={`${event.attendees ?? '—'}${event.venue ? ` of ${event.venue.capacity} capacity` : ''}`} />
                        <DetailRow icon={Coins} label="Investment" value={money(event.price)} last />
                        {event.description && (
                            <p style={{ fontSize: 13.5, color: C.muted, lineHeight: 1.55, marginTop: 14, marginBottom: 0 }}>
                                {event.description}
                            </p>
                        )}
                    </div>

                    {/* readiness */}
                    <div style={{ background: C.card, border: `1px solid ${C.border}`, borderRadius: 18, padding: 22, display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                        <div style={{ alignSelf: 'flex-start', display: 'flex', alignItems: 'center', gap: 8, width: '100%' }}>
                            <SectionTitle>Event readiness</SectionTitle>
                            <span className="mv-live-dot" style={{ marginLeft: 'auto', display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 11.5, fontWeight: 700, color: C.green }}>
                                <Radio size={13} /> LIVE
                            </span>
                        </div>
                        <ReadinessRing value={progress.readiness} />
                        <div style={{ fontSize: 13, color: C.muted, textAlign: 'center', marginTop: 4 }}>
                            {progress.total > 0
                                ? `${progress.by_state.finished ?? 0} of ${progress.total} tasks done`
                                : 'No tasks assigned yet'}
                        </div>
                    </div>
                </div>

                {/* where it is in the Pyramid */}
                {event.venue && (
                    <div style={{ background: C.card, border: `1px solid ${C.border}`, borderRadius: 18, padding: 22, marginTop: 16 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 14 }}>
                            <SectionTitle>
                                {phaseOf(event.start_time, event.end_time) === 'done'
                                    ? 'Where your event was held'
                                    : 'Where your event will be held'}
                            </SectionTitle>
                            <span
                                style={{
                                    marginLeft: 'auto',
                                    fontSize: 12,
                                    fontWeight: 700,
                                    color: C.green,
                                    background: C.greenTint,
                                    padding: '3px 10px',
                                    borderRadius: 999,
                                }}
                            >
                                {event.venue.name} · floor{' '}
                                {event.venue.location_geometry?.level ??
                                    event.venue.floor}
                            </span>
                        </div>
                        <PyramidMap
                            src={`/assets/pyramid-plan-${event.venue.location_geometry?.level ?? 1}.png`}
                            pins={
                                event.venue.location_geometry
                                    ? [
                                          {
                                              id: 'venue',
                                              x: event.venue.location_geometry.x,
                                              y: event.venue.location_geometry.y,
                                              label:
                                                  event.venue.box_ref ??
                                                  event.venue.name,
                                              tone: 'highlight',
                                          },
                                      ]
                                    : []
                            }
                            style={{ borderRadius: 14 }}
                        />
                    </div>
                )}

                {/* task board */}
                <div style={{ background: C.card, border: `1px solid ${C.border}`, borderRadius: 18, padding: 22, marginTop: 16 }}>
                    <SectionTitle>The Pyramid team is preparing your event</SectionTitle>
                    {grouped.length === 0 ? (
                        <div style={{ color: C.muted, fontSize: 14, marginTop: 8 }}>
                            Tasks will appear here once the team starts preparing.
                        </div>
                    ) : (
                        <div style={{ marginTop: 6 }}>
                            {grouped.map((g) => (
                                <div key={g.key} style={{ marginBottom: 18 }}>
                                    <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.05em', textTransform: 'uppercase', color: C.faint, marginBottom: 8 }}>
                                        {g.label}
                                    </div>
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                                        {g.tasks.map((t) => {
                                            const done = t.state === 'finished';
                                            return (
                                                <div
                                                    key={t.id}
                                                    style={{
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        gap: 11,
                                                        padding: '11px 13px',
                                                        borderRadius: 11,
                                                        background: done ? 'rgba(16,130,91,0.06)' : C.cream,
                                                        border: `1px solid ${done ? 'rgba(16,130,91,0.18)' : C.borderSoft}`,
                                                    }}
                                                >
                                                    <CheckCircle2
                                                        size={18}
                                                        color={done ? C.green : C.border}
                                                        fill={done ? C.green : 'none'}
                                                        style={{ flex: 'none' }}
                                                    />
                                                    <div style={{ flex: 1, minWidth: 0 }}>
                                                        <div style={{ fontSize: 13.5, fontWeight: 600, textDecoration: done ? 'line-through' : 'none', color: done ? C.muted : C.ink }}>
                                                            {t.name}
                                                        </div>
                                                        {t.worker && (
                                                            <div style={{ fontSize: 11.5, color: C.faint }}>
                                                                {t.worker.name}
                                                                {t.worker.worker_role ? ` · ${t.worker.worker_role}` : ''}
                                                            </div>
                                                        )}
                                                    </div>
                                                    <span
                                                        style={{
                                                            flex: 'none',
                                                            fontSize: 11.5,
                                                            fontWeight: 700,
                                                            color: STATE_COLOR[t.state] ?? C.faint,
                                                            background: '#fff',
                                                            border: `1px solid ${C.borderSoft}`,
                                                            padding: '3px 9px',
                                                            borderRadius: 999,
                                                        }}
                                                    >
                                                        {t.state_label}
                                                    </span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function Countdown({ start, end }: { start: string | null; end: string | null }) {
    const [, force] = useState(0);
    const ref = useRef<number | null>(null);

    useEffect(() => {
        ref.current = window.setInterval(() => force((n) => n + 1), 1000);

        return () => {
            if (ref.current) window.clearInterval(ref.current);
        };
    }, []);

    const phase = phaseOf(start, end);

    if (phase === 'live') {
        return (
            <Banner
                bg={`linear-gradient(135deg, ${C.green}, ${C.greenDeep})`}
                title="Happening now"
                subtitle="Your event is live at the Pyramid."
                live
            />
        );
    }
    if (phase === 'done') {
        return (
            <Banner
                bg={`linear-gradient(135deg, ${C.greenDark}, #244F38)`}
                title="Completed"
                subtitle="Thanks for hosting with the Pyramid of Tirana."
            />
        );
    }

    const remaining = Math.max(0, new Date(start!).getTime() - Date.now());
    const days = Math.floor(remaining / 86400000);
    const hrs = Math.floor((remaining % 86400000) / 3600000);
    const mins = Math.floor((remaining % 3600000) / 60000);
    const secs = Math.floor((remaining % 60000) / 1000);

    const segs: [number, string][] = [
        [days, 'Days'],
        [hrs, 'Hours'],
        [mins, 'Minutes'],
        [secs, 'Seconds'],
    ];

    return (
        <div
            style={{
                background: `linear-gradient(135deg, ${C.green}, ${C.greenDeep})`,
                borderRadius: 20,
                padding: '26px 24px',
                color: '#fff',
            }}
        >
            <div style={{ fontSize: 12.5, fontWeight: 700, letterSpacing: '0.1em', textTransform: 'uppercase', opacity: 0.85, marginBottom: 16, textAlign: 'center' }}>
                Counting down to your event
            </div>
            <div style={{ display: 'flex', gap: 12, justifyContent: 'center' }}>
                {segs.map(([v, label]) => (
                    <div
                        key={label}
                        style={{
                            background: 'rgba(255,255,255,0.12)',
                            borderRadius: 14,
                            padding: '14px 6px',
                            minWidth: 78,
                            textAlign: 'center',
                            backdropFilter: 'blur(4px)',
                        }}
                    >
                        <div
                            className="mv-seg"
                            key={`${label}-${v}`}
                            style={{ fontSize: 36, fontWeight: 800, lineHeight: 1, fontVariantNumeric: 'tabular-nums' }}
                        >
                            {String(v).padStart(2, '0')}
                        </div>
                        <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: '0.06em', textTransform: 'uppercase', opacity: 0.8, marginTop: 6 }}>
                            {label}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function Banner({
    bg,
    title,
    subtitle,
    live,
}: {
    bg: string;
    title: string;
    subtitle: string;
    live?: boolean;
}) {
    return (
        <div style={{ background: bg, borderRadius: 20, padding: '30px 24px', color: '#fff', textAlign: 'center' }}>
            <div style={{ display: 'inline-flex', alignItems: 'center', gap: 9, fontSize: 26, fontWeight: 800 }}>
                {live && <span className="mv-live-dot" style={{ width: 12, height: 12, borderRadius: '50%', background: '#fff' }} />}
                {title}
            </div>
            <div style={{ fontSize: 14, opacity: 0.9, marginTop: 6 }}>{subtitle}</div>
        </div>
    );
}

function ReadinessRing({ value }: { value: number }) {
    const r = 62;
    const circ = 2 * Math.PI * r;
    const offset = circ - (value / 100) * circ;

    return (
        <div style={{ position: 'relative', width: 160, height: 160, margin: '14px 0 8px' }}>
            <svg className="mv-ring" width="160" height="160" viewBox="0 0 160 160">
                <circle cx="80" cy="80" r={r} fill="none" stroke={C.cream} strokeWidth="14" />
                <circle
                    cx="80"
                    cy="80"
                    r={r}
                    fill="none"
                    stroke={C.green}
                    strokeWidth="14"
                    strokeLinecap="round"
                    strokeDasharray={circ}
                    strokeDashoffset={offset}
                    transform="rotate(-90 80 80)"
                />
            </svg>
            <div style={{ position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
                <span style={{ fontSize: 38, fontWeight: 800, letterSpacing: '-0.02em', fontVariantNumeric: 'tabular-nums' }}>
                    {value}%
                </span>
                <span style={{ fontSize: 12, color: C.faint, fontWeight: 600 }}>ready</span>
            </div>
        </div>
    );
}

function SectionTitle({ children }: { children: React.ReactNode }) {
    return (
        <div style={{ fontSize: 13, fontWeight: 700, color: C.ink, marginBottom: 14 }}>
            {children}
        </div>
    );
}

function DetailRow({
    icon: Icon,
    label,
    value,
    last,
}: {
    icon: typeof MapPin;
    label: string;
    value: string;
    last?: boolean;
}) {
    return (
        <div
            style={{
                display: 'flex',
                alignItems: 'flex-start',
                gap: 12,
                padding: '11px 0',
                borderBottom: last ? 'none' : `1px solid ${C.borderSoft}`,
            }}
        >
            <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 32, height: 32, borderRadius: 9, background: C.greenTint, color: C.green, flex: 'none' }}>
                <Icon size={16} />
            </span>
            <div style={{ minWidth: 0 }}>
                <div style={{ fontSize: 11.5, fontWeight: 600, letterSpacing: '0.04em', textTransform: 'uppercase', color: C.faint }}>
                    {label}
                </div>
                <div style={{ fontSize: 14, fontWeight: 500, marginTop: 1 }}>{value}</div>
            </div>
        </div>
    );
}
