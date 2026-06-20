import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Ban,
    Layers,
    MapPin as MapPinIcon,
    Ruler,
    Trash2,
    TriangleAlert,
    Users,
    Wrench,
    X,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { type MapPin, PyramidMap } from '@/components/pyramid-map';

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
    danger: '#B4453A',
    dangerTint: 'rgba(180,69,58,0.1)',
};

const css = `
.vm-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:${C.cream};color:${C.ink};min-height:100vh}
.vm-root *{box-sizing:border-box}
.vm-floor{transition:background .15s ease,border-color .15s ease,transform .12s ease}
.vm-floor:hover{transform:translateY(-1px)}
.vm-btn{transition:background .15s ease,border-color .15s ease,opacity .15s ease}
.vm-btn:not(:disabled):hover{background:${C.cream}}
.vm-overlay{animation:vm-fade .18s ease}
@keyframes vm-fade{from{opacity:0}to{opacity:1}}
@keyframes vm-spin{to{transform:rotate(360deg)}}
.vm-spin{animation:vm-spin 1s linear infinite}
`;

type Status = { type: string; type_label: string; describe: string } | null;

type Unavailability = {
    id: string;
    type: string;
    type_label: string;
    starts_at: string | null;
    ends_at: string | null;
    reason: string | null;
    describe: string;
};

type Venue = {
    id: string;
    name: string;
    box_ref: string | null;
    room_code: string | null;
    zone_class: string | null;
    functional_type: string | null;
    floor: number;
    capacity: number;
    area_sqm: number | null;
    location_geometry: { x: number; y: number; level?: number } | null;
    level: number;
    is_unavailable: boolean;
    current_status: Status;
    unavailabilities: Unavailability[];
};

function csrf(): string {
    const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return m ? decodeURIComponent(m[1]) : '';
}

async function api(url: string, method: string, body?: unknown): Promise<Response> {
    return fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': csrf(),
        },
        body: body ? JSON.stringify(body) : undefined,
    });
}

export default function VenueMap({
    floors,
    venues: initialVenues,
    maxLevels,
}: {
    floors: Record<number, string | null>;
    venues: Venue[];
    maxLevels: number;
}) {
    const [venues, setVenues] = useState<Venue[]>(initialVenues);
    const [level, setLevel] = useState<number>(1);
    const [openId, setOpenId] = useState<string | null>(null);

    const plan = floors[level] ?? null;
    const onFloor = venues.filter(
        (v) => v.location_geometry && v.level === level,
    );

    const pins: MapPin[] = onFloor.map((v) => ({
        id: v.id,
        x: v.location_geometry!.x,
        y: v.location_geometry!.y,
        label: v.box_ref ?? v.name,
        tone: v.is_unavailable ? 'danger' : 'default',
    }));

    const open = venues.find((v) => v.id === openId) ?? null;
    const unavailableCount = onFloor.filter((v) => v.is_unavailable).length;

    const applyVenue = (updated: Venue) =>
        setVenues((prev) => prev.map((v) => (v.id === updated.id ? updated : v)));

    return (
        <div className="vm-root">
            <Head title="Floor Explorer">
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
                    href="/operations"
                    style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 6,
                        fontSize: 13.5,
                        color: C.muted,
                        textDecoration: 'none',
                    }}
                >
                    <ArrowLeft size={15} /> Operations
                </Link>
                <div style={{ fontWeight: 800, letterSpacing: '0.04em' }}>
                    Floor Explorer
                </div>
            </header>

            <div style={{ maxWidth: 1180, margin: '0 auto', padding: '22px 24px 50px' }}>
                {/* floor selector + legend */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 12,
                        flexWrap: 'wrap',
                        marginBottom: 16,
                    }}
                >
                    <span
                        style={{
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: 7,
                            fontSize: 13,
                            fontWeight: 700,
                            color: C.muted,
                        }}
                    >
                        <Layers size={16} color={C.green} /> Floor
                    </span>
                    {Array.from({ length: maxLevels }, (_, i) => i + 1).map((lvl) => {
                        const active = level === lvl;
                        const hasImage = !!floors[lvl];
                        return (
                            <button
                                key={lvl}
                                type="button"
                                className="vm-floor"
                                onClick={() => setLevel(lvl)}
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: 7,
                                    padding: '8px 16px',
                                    borderRadius: 10,
                                    border: `1px solid ${active ? C.green : C.border}`,
                                    background: active ? C.green : C.card,
                                    color: active ? '#fff' : hasImage ? C.ink : C.faint,
                                    fontSize: 14,
                                    fontWeight: 700,
                                    cursor: 'pointer',
                                    fontFamily: 'inherit',
                                }}
                            >
                                Floor {lvl}
                                {!hasImage && (
                                    <span style={{ fontSize: 11, fontWeight: 500 }}>
                                        (no plan)
                                    </span>
                                )}
                            </button>
                        );
                    })}

                    <div
                        style={{
                            marginLeft: 'auto',
                            display: 'flex',
                            alignItems: 'center',
                            gap: 16,
                            fontSize: 12.5,
                            color: C.muted,
                        }}
                    >
                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                            <span style={dot(C.green)} /> Available
                        </span>
                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                            <span style={dot(C.danger)} /> Blocked / out of service
                        </span>
                    </div>
                </div>

                {/* the focused floor */}
                <div
                    style={{
                        background: C.card,
                        border: `1px solid ${C.border}`,
                        borderRadius: 18,
                        padding: 16,
                        boxShadow: '0 18px 40px -34px rgba(26,26,26,0.3)',
                    }}
                >
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'space-between',
                            marginBottom: 12,
                            padding: '0 4px',
                        }}
                    >
                        <div style={{ fontSize: 15, fontWeight: 700 }}>
                            Floor {level} ·{' '}
                            <span style={{ color: C.muted, fontWeight: 500 }}>
                                {onFloor.length} venue{onFloor.length === 1 ? '' : 's'}
                                {unavailableCount > 0 && (
                                    <span style={{ color: C.danger, fontWeight: 700 }}>
                                        {' '}
                                        · {unavailableCount} unavailable
                                    </span>
                                )}
                            </span>
                        </div>
                        <div style={{ fontSize: 12.5, color: C.faint }}>
                            Click a venue to manage it
                        </div>
                    </div>

                    {plan ? (
                        <PyramidMap
                            src={plan}
                            pins={pins}
                            onPinClick={setOpenId}
                            style={{
                                background: C.cream,
                                border: `1px solid ${C.borderSoft}`,
                                borderRadius: 14,
                                padding: 8,
                            }}
                        />
                    ) : (
                        <div
                            style={{
                                minHeight: 360,
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'center',
                                justifyContent: 'center',
                                gap: 10,
                                border: `2px dashed ${C.border}`,
                                borderRadius: 14,
                                color: C.muted,
                                textAlign: 'center',
                                padding: 24,
                            }}
                        >
                            <MapPinIcon size={30} color={C.faint} />
                            <div style={{ fontSize: 15, fontWeight: 600 }}>
                                No plan uploaded for floor {level}
                            </div>
                            <Link
                                href="/operations/map-calibration"
                                style={{ fontSize: 13, color: C.green, fontWeight: 600 }}
                            >
                                Upload it in Map setup →
                            </Link>
                        </div>
                    )}
                </div>
            </div>

            {open && (
                <VenueModal
                    venue={open}
                    onClose={() => setOpenId(null)}
                    onUpdated={applyVenue}
                />
            )}
        </div>
    );
}

function VenueModal({
    venue,
    onClose,
    onUpdated,
}: {
    venue: Venue;
    onClose: () => void;
    onUpdated: (v: Venue) => void;
}) {
    const [type, setType] = useState<'blocked' | 'broken'>('broken');
    const [from, setFrom] = useState('');
    const [to, setTo] = useState('');
    const [reason, setReason] = useState('');
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');

    const submit = async () => {
        setBusy(true);
        setError('');
        const res = await api(
            `/operations/venue-map/spaces/${venue.id}/unavailability`,
            'POST',
            {
                type,
                starts_at: from || null,
                ends_at: to || null,
                reason: reason || null,
            },
        );
        setBusy(false);
        if (res.ok) {
            const json = await res.json();
            onUpdated(json.data);
            setFrom('');
            setTo('');
            setReason('');
        } else {
            setError('Could not save. Check the dates and try again.');
        }
    };

    const lift = async (id: string) => {
        const res = await api(
            `/operations/venue-map/spaces/${venue.id}/unavailability/${id}`,
            'DELETE',
        );
        if (res.ok) {
            const json = await res.json();
            onUpdated(json.data);
        }
    };

    const details: [string, string][] = [
        ['Floor', String(venue.floor)],
        ['Capacity', `${venue.capacity}`],
        ['Area', venue.area_sqm ? `${venue.area_sqm} m²` : '—'],
        ['Zone', venue.zone_class ?? '—'],
        ['Type', venue.functional_type ?? '—'],
        ['Code', venue.room_code ?? venue.box_ref ?? '—'],
    ];

    return (
        <div
            className="vm-overlay"
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
                    maxHeight: '90vh',
                    overflowY: 'auto',
                    background: C.card,
                    borderRadius: 20,
                    border: `1px solid ${C.border}`,
                    boxShadow: '0 40px 90px -34px rgba(26,26,26,0.55)',
                    padding: '24px 24px 22px',
                }}
            >
                {/* header */}
                <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
                    <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ fontSize: 20, fontWeight: 800 }}>
                            {venue.name}
                        </div>
                        <div style={{ fontSize: 13, color: C.muted, marginTop: 2 }}>
                            {venue.box_ref ? `${venue.box_ref} · ` : ''}Floor{' '}
                            {venue.floor}
                        </div>
                    </div>
                    {venue.is_unavailable ? (
                        <span
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: 5,
                                padding: '5px 11px',
                                borderRadius: 999,
                                fontSize: 12.5,
                                fontWeight: 700,
                                color: C.danger,
                                background: C.dangerTint,
                                flex: 'none',
                            }}
                        >
                            <TriangleAlert size={13} />
                            {venue.current_status?.type_label ?? 'Unavailable'}
                        </span>
                    ) : (
                        <span
                            style={{
                                padding: '5px 11px',
                                borderRadius: 999,
                                fontSize: 12.5,
                                fontWeight: 700,
                                color: C.green,
                                background: C.greenTint,
                                flex: 'none',
                            }}
                        >
                            Available
                        </span>
                    )}
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Close"
                        style={{
                            flex: 'none',
                            width: 32,
                            height: 32,
                            borderRadius: 9,
                            border: `1px solid ${C.border}`,
                            background: C.card,
                            color: C.muted,
                            cursor: 'pointer',
                        }}
                    >
                        <X size={16} />
                    </button>
                </div>

                {/* details */}
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'repeat(3, 1fr)',
                        gap: 8,
                        marginTop: 16,
                    }}
                >
                    {details.map(([label, value]) => (
                        <div
                            key={label}
                            style={{
                                background: C.cream,
                                borderRadius: 10,
                                padding: '9px 11px',
                            }}
                        >
                            <div style={smallLabel}>{label}</div>
                            <div style={{ fontSize: 13.5, fontWeight: 600, marginTop: 1 }}>
                                {value}
                            </div>
                        </div>
                    ))}
                </div>

                {/* active spells */}
                {venue.unavailabilities.length > 0 && (
                    <div style={{ marginTop: 18 }}>
                        <div style={smallLabel}>Blocks & out-of-service</div>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 7, marginTop: 8 }}>
                            {venue.unavailabilities.map((u) => (
                                <div
                                    key={u.id}
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 10,
                                        padding: '9px 12px',
                                        borderRadius: 10,
                                        border: `1px solid ${C.borderSoft}`,
                                        background:
                                            u.type === 'broken'
                                                ? C.dangerTint
                                                : C.cream,
                                    }}
                                >
                                    {u.type === 'broken' ? (
                                        <Wrench size={15} color={C.danger} />
                                    ) : (
                                        <Ban size={15} color={C.muted} />
                                    )}
                                    <div style={{ flex: 1, minWidth: 0 }}>
                                        <div style={{ fontSize: 13, fontWeight: 600 }}>
                                            {u.describe}
                                        </div>
                                        {u.reason && (
                                            <div style={{ fontSize: 12, color: C.muted }}>
                                                {u.reason}
                                            </div>
                                        )}
                                    </div>
                                    <button
                                        type="button"
                                        className="vm-btn"
                                        onClick={() => lift(u.id)}
                                        title="Lift / make available"
                                        style={{
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            gap: 5,
                                            padding: '6px 10px',
                                            borderRadius: 8,
                                            border: `1px solid ${C.border}`,
                                            background: C.card,
                                            color: C.ink,
                                            fontSize: 12.5,
                                            fontWeight: 600,
                                            cursor: 'pointer',
                                            fontFamily: 'inherit',
                                        }}
                                    >
                                        <Trash2 size={13} /> Lift
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* new action */}
                <div
                    style={{
                        marginTop: 18,
                        paddingTop: 18,
                        borderTop: `1px solid ${C.borderSoft}`,
                    }}
                >
                    <div style={smallLabel}>Make unavailable</div>
                    <div style={{ display: 'flex', gap: 8, marginTop: 8 }}>
                        {(
                            [
                                ['broken', 'Out of service', Wrench],
                                ['blocked', 'Block', Ban],
                            ] as const
                        ).map(([val, lbl, Icon]) => (
                            <button
                                key={val}
                                type="button"
                                onClick={() => setType(val)}
                                style={{
                                    flex: 1,
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    gap: 7,
                                    padding: '10px',
                                    borderRadius: 10,
                                    border: `1px solid ${type === val ? C.green : C.border}`,
                                    background: type === val ? C.greenTint : C.card,
                                    color: type === val ? C.greenDeep : C.muted,
                                    fontSize: 13.5,
                                    fontWeight: 700,
                                    cursor: 'pointer',
                                    fontFamily: 'inherit',
                                }}
                            >
                                <Icon size={15} />
                                {lbl}
                            </button>
                        ))}
                    </div>

                    <div style={{ display: 'flex', gap: 10, marginTop: 12 }}>
                        <div style={{ flex: 1 }}>
                            <div style={smallLabel}>From (optional)</div>
                            <input
                                type="date"
                                value={from}
                                onChange={(e) => setFrom(e.target.value)}
                                style={inputStyle}
                            />
                        </div>
                        <div style={{ flex: 1 }}>
                            <div style={smallLabel}>To (optional)</div>
                            <input
                                type="date"
                                value={to}
                                onChange={(e) => setTo(e.target.value)}
                                style={inputStyle}
                            />
                        </div>
                    </div>
                    <div style={{ fontSize: 11.5, color: C.faint, marginTop: 6 }}>
                        Leave both dates empty to make it unavailable indefinitely.
                    </div>

                    <div style={{ marginTop: 12 }}>
                        <div style={smallLabel}>Reason (optional)</div>
                        <input
                            value={reason}
                            onChange={(e) => setReason(e.target.value)}
                            placeholder={
                                type === 'broken'
                                    ? 'e.g. AC unit failed, awaiting parts'
                                    : 'e.g. reserved for internal use'
                            }
                            style={inputStyle}
                        />
                    </div>

                    {error && (
                        <div style={{ color: C.danger, fontSize: 13, marginTop: 10 }}>
                            {error}
                        </div>
                    )}

                    <button
                        type="button"
                        onClick={submit}
                        disabled={busy}
                        style={{
                            width: '100%',
                            marginTop: 16,
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
                        {busy ? (
                            <span className="vm-spin" style={{ width: 15, height: 15, border: '2px solid rgba(255,255,255,0.5)', borderTopColor: '#fff', borderRadius: '50%' }} />
                        ) : (
                            <TriangleAlert size={16} />
                        )}
                        {type === 'broken'
                            ? 'Mark out of service'
                            : 'Block this venue'}
                    </button>
                </div>
            </div>
        </div>
    );
}

function dot(color: string): React.CSSProperties {
    return {
        width: 10,
        height: 10,
        borderRadius: '50%',
        background: color,
        border: '2px solid #fff',
        boxShadow: '0 1px 3px rgba(0,0,0,.25)',
        display: 'inline-block',
    };
}

const smallLabel: React.CSSProperties = {
    fontSize: 11,
    fontWeight: 700,
    letterSpacing: '0.04em',
    textTransform: 'uppercase',
    color: C.faint,
};

const inputStyle: React.CSSProperties = {
    width: '100%',
    marginTop: 5,
    padding: '9px 11px',
    borderRadius: 9,
    border: `1px solid ${C.border}`,
    fontSize: 13.5,
    fontFamily: 'inherit',
    outline: 'none',
    color: C.ink,
    background: C.card,
};
