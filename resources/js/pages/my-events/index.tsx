import { Head, Link } from '@inertiajs/react';
import BrandLogo from '@/components/brand-logo';
import {
    ArrowLeft,
    ArrowUpRight,
    CalendarClock,
    Clock,
    Coins,
    LayoutGrid,
    MapPin,
    Sparkles,
    TrendingUp,
    Users,
} from 'lucide-react';
import { useEffect, useState } from 'react';

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
};

const css = `
.me-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:${C.cream};color:${C.ink};min-height:100vh}
.me-root *{box-sizing:border-box}
.me-stat{transition:box-shadow .25s ease,transform .2s ease}
.me-stat:hover{box-shadow:0 18px 40px -30px rgba(26,26,26,.35);transform:translateY(-2px)}
.me-card{transition:box-shadow .25s ease,transform .2s ease,border-color .2s ease}
.me-card:hover{box-shadow:0 22px 50px -30px rgba(16,130,91,.4);transform:translateY(-3px);border-color:${C.green}}
.me-card:hover .me-arrow{transform:translate(3px,-3px);color:${C.green}}
`;

type EventCard = {
    id: string;
    title: string | null;
    event_type_label: string | null;
    status: string;
    status_label: string;
    start_time: string | null;
    end_time: string | null;
    attendees: number | null;
    venue: string | null;
    price: number;
    duration_hours: number | null;
    is_finished: boolean;
    tasks_total: number;
    tasks_done: number;
};

type Stats = {
    events_total: number;
    upcoming_count: number;
    finished_count: number;
    total_spent: number;
    total_hours: number;
    total_guests: number;
    venues_used: number;
    favorite_venue: string | null;
    avg_spend: number;
    avg_guests: number;
    since: string | null;
    spend_by_type: { type: string; amount: number }[];
    next_event: {
        id: string;
        title: string | null;
        start_time: string | null;
        venue: string | null;
    } | null;
};

function money(n: number): string {
    return '€' + Math.round(n).toLocaleString();
}

function shortDate(iso: string | null): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString(undefined, {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function timeRange(start: string | null, end: string | null): string {
    if (!start) return '';
    const fmt = (i: string) =>
        new Date(i).toLocaleTimeString(undefined, {
            hour: '2-digit',
            minute: '2-digit',
        });
    return end ? `${fmt(start)} – ${fmt(end)}` : fmt(start);
}

function daysUntil(iso: string | null): number | null {
    if (!iso) return null;
    return Math.ceil((new Date(iso).getTime() - Date.now()) / 86400000);
}

export default function MyEventsIndex({
    upcoming,
    finished,
    stats,
}: {
    upcoming: EventCard[];
    finished: EventCard[];
    stats: Stats;
}) {
    const [tab, setTab] = useState<'upcoming' | 'finished'>(
        upcoming.length > 0 ? 'upcoming' : 'finished',
    );
    const list = tab === 'upcoming' ? upcoming : finished;
    const sinceYear = stats.since ? new Date(stats.since).getFullYear() : null;
    const maxSpend = Math.max(1, ...stats.spend_by_type.map((s) => s.amount));

    return (
        <div className="me-root">
            <Head title="My events">
                <link
                    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap"
                    rel="stylesheet"
                />
            </Head>
            <style dangerouslySetInnerHTML={{ __html: css }} />

            {/* top bar */}
            <header
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '16px 26px',
                    borderBottom: `1px solid ${C.borderSoft}`,
                    background: C.card,
                }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
                    <BrandLogo height={32} />
                    <span style={{ color: C.faint, fontSize: 14 }}>
                        · My events
                    </span>
                </div>
                <Link
                    href="/planner"
                    style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 6,
                        fontSize: 13.5,
                        color: C.muted,
                        textDecoration: 'none',
                    }}
                >
                    <ArrowLeft size={15} /> Planner
                </Link>
            </header>

            <div style={{ maxWidth: 1140, margin: '0 auto', padding: '30px 24px 60px' }}>
                {/* heading */}
                <div style={{ marginBottom: 24 }}>
                    <h1 style={{ fontSize: 30, fontWeight: 800, margin: 0, letterSpacing: '-0.02em' }}>
                        Your events at the Pyramid
                    </h1>
                    <p style={{ fontSize: 15, color: C.muted, margin: '6px 0 0' }}>
                        {stats.events_total > 0
                            ? `${stats.events_total} event${stats.events_total === 1 ? '' : 's'} organized${sinceYear ? ` · with us since ${sinceYear}` : ''}.`
                            : 'Once you book an event, it shows up here.'}
                    </p>
                </div>

                {/* hero stats */}
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))',
                        gap: 14,
                        marginBottom: 16,
                    }}
                >
                    <StatCard icon={LayoutGrid} label="Events organized" value={String(stats.events_total)} sub={`${stats.upcoming_count} upcoming · ${stats.finished_count} done`} />
                    <StatCard icon={Coins} label="Total invested" value={money(stats.total_spent)} sub={`avg ${money(stats.avg_spend)} / event`} accent />
                    <StatCard icon={Clock} label="Hours hosted" value={`${stats.total_hours}h`} sub="across all events" />
                    <StatCard icon={Users} label="Guests welcomed" value={stats.total_guests.toLocaleString()} sub={`avg ${stats.avg_guests} / event`} />
                </div>

                {/* secondary row: next event + venues + spend-by-type */}
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: '1.1fr 1fr',
                        gap: 14,
                        marginBottom: 30,
                    }}
                >
                    {/* next event */}
                    <div
                        style={{
                            background: `linear-gradient(135deg, ${C.green}, ${C.greenDeep})`,
                            borderRadius: 18,
                            padding: 22,
                            color: '#fff',
                            display: 'flex',
                            flexDirection: 'column',
                            justifyContent: 'space-between',
                            minHeight: 150,
                        }}
                    >
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 12.5, fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', opacity: 0.85 }}>
                            <Sparkles size={15} /> Next event
                        </div>
                        {stats.next_event ? (
                            <>
                                <div>
                                    <div style={{ fontSize: 22, fontWeight: 800, marginTop: 10 }}>
                                        {stats.next_event.title}
                                    </div>
                                    <div style={{ fontSize: 13.5, opacity: 0.9, marginTop: 2 }}>
                                        {shortDate(stats.next_event.start_time)}
                                        {stats.next_event.venue ? ` · ${stats.next_event.venue}` : ''}
                                    </div>
                                </div>
                                <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginTop: 12 }}>
                                    <span style={{ fontSize: 34, fontWeight: 800, lineHeight: 1 }}>
                                        {Math.max(0, daysUntil(stats.next_event.start_time) ?? 0)}
                                    </span>
                                    <span style={{ fontSize: 14, opacity: 0.9 }}>days to go</span>
                                    <Link
                                        href={`/my-events/${stats.next_event.id}`}
                                        style={{
                                            marginLeft: 'auto',
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            gap: 5,
                                            background: 'rgba(255,255,255,0.16)',
                                            color: '#fff',
                                            textDecoration: 'none',
                                            padding: '8px 13px',
                                            borderRadius: 10,
                                            fontSize: 13,
                                            fontWeight: 600,
                                        }}
                                    >
                                        Open <ArrowUpRight size={14} />
                                    </Link>
                                </div>
                            </>
                        ) : (
                            <div style={{ fontSize: 15, opacity: 0.9, marginTop: 10 }}>
                                No upcoming events — ready to plan the next one?
                            </div>
                        )}
                    </div>

                    {/* spend by type */}
                    <div
                        style={{
                            background: C.card,
                            border: `1px solid ${C.border}`,
                            borderRadius: 18,
                            padding: 22,
                        }}
                    >
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 12.5, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: C.faint, marginBottom: 14 }}>
                            <TrendingUp size={15} color={C.green} /> Spend by event type
                        </div>
                        {stats.spend_by_type.length === 0 ? (
                            <div style={{ color: C.muted, fontSize: 14 }}>No spend yet.</div>
                        ) : (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 11 }}>
                                {stats.spend_by_type.slice(0, 5).map((s) => (
                                    <div key={s.type}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13, marginBottom: 4 }}>
                                            <span style={{ fontWeight: 600 }}>{s.type}</span>
                                            <span style={{ color: C.muted }}>{money(s.amount)}</span>
                                        </div>
                                        <div style={{ height: 7, borderRadius: 4, background: C.cream, overflow: 'hidden' }}>
                                            <div style={{ height: '100%', width: `${(s.amount / maxSpend) * 100}%`, background: `linear-gradient(90deg, ${C.green}, ${C.greenDark})`, borderRadius: 4 }} />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                        {stats.favorite_venue && (
                            <div style={{ marginTop: 16, paddingTop: 14, borderTop: `1px solid ${C.borderSoft}`, fontSize: 13, color: C.muted, display: 'flex', alignItems: 'center', gap: 7 }}>
                                <MapPin size={14} color={C.green} />
                                Most-used venue: <strong style={{ color: C.ink }}>{stats.favorite_venue}</strong> · {stats.venues_used} venue{stats.venues_used === 1 ? '' : 's'} total
                            </div>
                        )}
                    </div>
                </div>

                {/* tabs */}
                <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
                    {(['upcoming', 'finished'] as const).map((t) => (
                        <button
                            key={t}
                            type="button"
                            onClick={() => setTab(t)}
                            style={{
                                padding: '8px 16px',
                                borderRadius: 999,
                                border: `1px solid ${tab === t ? C.green : C.border}`,
                                background: tab === t ? C.greenTint : C.card,
                                color: tab === t ? C.green : C.muted,
                                fontSize: 13.5,
                                fontWeight: 600,
                                cursor: 'pointer',
                                fontFamily: 'inherit',
                            }}
                        >
                            {t === 'upcoming'
                                ? `Upcoming (${upcoming.length})`
                                : `Finished (${finished.length})`}
                        </button>
                    ))}
                </div>

                {/* event grid */}
                {list.length === 0 ? (
                    <div
                        style={{
                            background: C.card,
                            border: `1px dashed ${C.border}`,
                            borderRadius: 16,
                            padding: '50px 20px',
                            textAlign: 'center',
                            color: C.muted,
                        }}
                    >
                        No {tab} events.
                    </div>
                ) : (
                    <div
                        style={{
                            display: 'grid',
                            gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))',
                            gap: 16,
                        }}
                    >
                        {list.map((e) => (
                            <EventCardView key={e.id} event={e} />
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

function StatCard({
    icon: Icon,
    label,
    value,
    sub,
    accent,
}: {
    icon: typeof Coins;
    label: string;
    value: string;
    sub?: string;
    accent?: boolean;
}) {
    return (
        <div
            className="me-stat"
            style={{
                background: C.card,
                border: `1px solid ${accent ? C.green : C.border}`,
                borderRadius: 16,
                padding: '18px 20px',
            }}
        >
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    width: 38,
                    height: 38,
                    borderRadius: 11,
                    background: accent ? C.green : C.greenTint,
                    color: accent ? '#fff' : C.green,
                    marginBottom: 12,
                }}
            >
                <Icon size={19} />
            </div>
            <div style={{ fontSize: 26, fontWeight: 800, letterSpacing: '-0.02em' }}>
                {value}
            </div>
            <div style={{ fontSize: 12.5, fontWeight: 600, color: C.ink, marginTop: 2 }}>
                {label}
            </div>
            {sub && (
                <div style={{ fontSize: 12, color: C.faint, marginTop: 2 }}>{sub}</div>
            )}
        </div>
    );
}

function EventCardView({ event }: { event: EventCard }) {
    const days = daysUntil(event.start_time);
    const readiness =
        event.tasks_total > 0
            ? Math.round((event.tasks_done / event.tasks_total) * 100)
            : null;

    return (
        <Link
            href={`/my-events/${event.id}`}
            className="me-card"
            style={{
                display: 'block',
                background: C.card,
                border: `1px solid ${C.border}`,
                borderRadius: 16,
                padding: 20,
                textDecoration: 'none',
                color: 'inherit',
            }}
        >
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 10 }}>
                <div style={{ minWidth: 0 }}>
                    <div style={{ fontSize: 11.5, fontWeight: 700, letterSpacing: '0.05em', textTransform: 'uppercase', color: C.green }}>
                        {event.event_type_label ?? 'Event'}
                    </div>
                    <div style={{ fontSize: 17.5, fontWeight: 700, marginTop: 3, lineHeight: 1.2 }}>
                        {event.title ?? 'Untitled event'}
                    </div>
                </div>
                <ArrowUpRight className="me-arrow" size={18} color={C.faint} style={{ flex: 'none', transition: 'transform .2s ease, color .2s ease' }} />
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 6, marginTop: 14, fontSize: 13, color: C.muted }}>
                <Row icon={CalendarClock} text={`${shortDate(event.start_time)} · ${timeRange(event.start_time, event.end_time)}`} />
                <Row icon={MapPin} text={event.venue ?? 'Venue to be confirmed'} />
                <Row icon={Users} text={`${event.attendees ?? '—'} guests · ${money(event.price)}`} />
            </div>

            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 16 }}>
                {event.is_finished ? (
                    <span style={{ fontSize: 12.5, fontWeight: 700, color: C.muted, background: C.cream, padding: '4px 10px', borderRadius: 999 }}>
                        {event.status_label}
                    </span>
                ) : days !== null && days >= 0 ? (
                    <span style={{ fontSize: 12.5, fontWeight: 700, color: C.green, background: C.greenTint, padding: '4px 10px', borderRadius: 999 }}>
                        {days === 0 ? 'Today' : `in ${days} day${days === 1 ? '' : 's'}`}
                    </span>
                ) : (
                    <span style={{ fontSize: 12.5, fontWeight: 700, color: C.amber, background: 'rgba(138,109,28,0.12)', padding: '4px 10px', borderRadius: 999 }}>
                        In progress
                    </span>
                )}
                {!event.is_finished && readiness !== null && (
                    <span style={{ fontSize: 12.5, color: C.faint }}>
                        {readiness}% ready
                    </span>
                )}
            </div>
        </Link>
    );
}

function Row({ icon: Icon, text }: { icon: typeof MapPin; text: string }) {
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <Icon size={14} color={C.faint} style={{ flex: 'none' }} />
            <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {text}
            </span>
        </div>
    );
}
