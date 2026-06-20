import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Check,
    ImageUp,
    MapPin as MapPinIcon,
    Trash2,
} from 'lucide-react';
import { useMemo, useRef, useState } from 'react';
import { type MapPin, PyramidMap } from '@/components/pyramid-map';

const C = {
    cream: '#F4F3EE',
    card: '#FFFFFF',
    border: '#E0DCD3',
    borderSoft: '#EAE7DC',
    green: '#10825B',
    greenTint: '#D8E2DC',
    ink: '#1A1A1A',
    muted: '#6E6E6E',
    faint: '#9A958B',
};

type Geometry = { x: number; y: number } | null;

type Space = {
    id: string;
    name: string;
    box_ref: string | null;
    room_code: string | null;
    zone_class: string | null;
    floor: number;
    capacity: number;
    location_geometry: Geometry;
};

function csrf(): string {
    const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return m ? decodeURIComponent(m[1]) : '';
}

async function saveGeometry(spaceId: string, geometry: Geometry): Promise<void> {
    await fetch(`/operations/spaces/${spaceId}/geometry`, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': csrf(),
        },
        body: JSON.stringify(geometry ?? { x: null, y: null }),
    });
}

export default function MapCalibration({
    spaces,
    planUrl,
}: {
    spaces: Space[];
    planUrl: string | null;
}) {
    const [items, setItems] = useState<Space[]>(spaces);
    const [selectedId, setSelectedId] = useState<string | null>(
        spaces[0]?.id ?? null,
    );
    const [search, setSearch] = useState('');
    const [plan, setPlan] = useState<string | null>(planUrl);
    const [uploading, setUploading] = useState(false);
    const planInput = useRef<HTMLInputElement>(null);

    const uploadPlan = async (file: File) => {
        setUploading(true);
        const body = new FormData();
        body.append('plan', file);

        try {
            const res = await fetch('/operations/map-calibration/plan', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-XSRF-TOKEN': csrf() },
                body,
            });
            const json = await res.json();
            if (json?.data?.plan_url) {
                setPlan(json.data.plan_url);
            }
        } finally {
            setUploading(false);
        }
    };

    const pinnedCount = items.filter((s) => s.location_geometry).length;

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();

        if (!q) {
            return items;
        }

        return items.filter((s) =>
            [s.name, s.box_ref, s.room_code]
                .filter(Boolean)
                .some((v) => v!.toLowerCase().includes(q)),
        );
    }, [items, search]);

    const pins: MapPin[] = items
        .filter((s) => s.location_geometry)
        .map((s) => ({
            id: s.id,
            x: s.location_geometry!.x,
            y: s.location_geometry!.y,
            label: s.box_ref ?? s.name,
            tone: s.id === selectedId ? 'highlight' : 'default',
        }));

    const updateLocal = (id: string, geometry: Geometry) => {
        setItems((prev) =>
            prev.map((s) =>
                s.id === id ? { ...s, location_geometry: geometry } : s,
            ),
        );
    };

    const place = async (point: { x: number; y: number }) => {
        if (!selectedId) {
            return;
        }

        updateLocal(selectedId, point);
        await saveGeometry(selectedId, point);

        // Jump to the next un-pinned venue to keep the flow fast.
        const next = items.find(
            (s) => s.id !== selectedId && !s.location_geometry,
        );
        if (next) {
            setSelectedId(next.id);
        }
    };

    const clear = async (id: string) => {
        updateLocal(id, null);
        await saveGeometry(id, null);
    };

    const selected = items.find((s) => s.id === selectedId) ?? null;

    return (
        <div
            style={{
                fontFamily:
                    "'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif",
                background: C.cream,
                color: C.ink,
                minHeight: '100vh',
                display: 'flex',
            }}
        >
            <Head title="Map calibration">
                <link
                    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap"
                    rel="stylesheet"
                />
            </Head>

            {/* ===== venue list ===== */}
            <aside
                style={{
                    width: 320,
                    flex: 'none',
                    background: C.card,
                    borderRight: `1px solid ${C.borderSoft}`,
                    display: 'flex',
                    flexDirection: 'column',
                    height: '100vh',
                    position: 'sticky',
                    top: 0,
                }}
            >
                <div style={{ padding: '18px 18px 12px' }}>
                    <Link
                        href="/operations"
                        style={{
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: 6,
                            fontSize: 13,
                            color: C.muted,
                            textDecoration: 'none',
                            marginBottom: 12,
                        }}
                    >
                        <ArrowLeft size={15} /> Operations
                    </Link>
                    <h1 style={{ fontSize: 19, fontWeight: 800, margin: 0 }}>
                        Map calibration
                    </h1>
                    <p
                        style={{
                            fontSize: 12.5,
                            color: C.muted,
                            margin: '4px 0 0',
                        }}
                    >
                        Pick a venue, then click its spot on the plan.{' '}
                        <strong style={{ color: C.green }}>
                            {pinnedCount}/{items.length}
                        </strong>{' '}
                        pinned.
                    </p>
                    <input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search venues…"
                        style={{
                            marginTop: 12,
                            width: '100%',
                            padding: '8px 11px',
                            borderRadius: 9,
                            border: `1px solid ${C.border}`,
                            fontSize: 13,
                            fontFamily: 'inherit',
                            outline: 'none',
                        }}
                    />
                </div>

                <div style={{ overflowY: 'auto', flex: 1, padding: '0 10px 16px' }}>
                    {filtered.map((space) => {
                        const isSelected = space.id === selectedId;
                        const pinned = !!space.location_geometry;

                        return (
                            <button
                                key={space.id}
                                type="button"
                                onClick={() => setSelectedId(space.id)}
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 10,
                                    width: '100%',
                                    textAlign: 'left',
                                    padding: '9px 10px',
                                    marginBottom: 4,
                                    borderRadius: 9,
                                    border: `1px solid ${isSelected ? C.green : 'transparent'}`,
                                    background: isSelected ? C.greenTint : 'transparent',
                                    cursor: 'pointer',
                                    fontFamily: 'inherit',
                                }}
                            >
                                <span
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        width: 22,
                                        height: 22,
                                        flex: 'none',
                                        borderRadius: '50%',
                                        background: pinned ? C.green : C.cream,
                                        color: pinned ? '#fff' : C.faint,
                                        border: pinned ? 'none' : `1px solid ${C.border}`,
                                    }}
                                >
                                    {pinned ? (
                                        <Check size={13} />
                                    ) : (
                                        <MapPinIcon size={12} />
                                    )}
                                </span>
                                <span style={{ minWidth: 0, flex: 1 }}>
                                    <span
                                        style={{
                                            display: 'block',
                                            fontSize: 13,
                                            fontWeight: 600,
                                            overflow: 'hidden',
                                            textOverflow: 'ellipsis',
                                            whiteSpace: 'nowrap',
                                        }}
                                    >
                                        {space.name}
                                    </span>
                                    <span
                                        style={{
                                            display: 'block',
                                            fontSize: 11.5,
                                            color: C.faint,
                                        }}
                                    >
                                        {space.box_ref ?? space.room_code ?? '—'}
                                        {' · floor '}
                                        {space.floor} · {space.capacity} cap
                                    </span>
                                </span>
                                {pinned && (
                                    <span
                                        role="button"
                                        tabIndex={0}
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            void clear(space.id);
                                        }}
                                        title="Clear pin"
                                        style={{
                                            flex: 'none',
                                            color: C.faint,
                                            display: 'flex',
                                        }}
                                    >
                                        <Trash2 size={14} />
                                    </span>
                                )}
                            </button>
                        );
                    })}
                </div>
            </aside>

            {/* ===== plan ===== */}
            <main style={{ flex: 1, padding: 24, overflow: 'auto' }}>
                <div
                    style={{
                        background: C.card,
                        border: `1px solid ${C.border}`,
                        borderRadius: 14,
                        padding: 16,
                        marginBottom: 14,
                        display: 'flex',
                        alignItems: 'center',
                        gap: 10,
                        fontSize: 13.5,
                    }}
                >
                    <MapPinIcon size={16} color={C.green} />
                    <span style={{ flex: 1 }}>
                        {!plan ? (
                            <span style={{ color: C.muted }}>
                                Upload the Pyramid floor plan to start placing
                                venues.
                            </span>
                        ) : selected ? (
                            <span>
                                Placing <strong>{selected.name}</strong>
                                {selected.box_ref
                                    ? ` (${selected.box_ref})`
                                    : ''}{' '}
                                — click its location on the plan.
                            </span>
                        ) : (
                            <span style={{ color: C.muted }}>
                                Select a venue from the list to place it.
                            </span>
                        )}
                    </span>
                    <input
                        ref={planInput}
                        type="file"
                        accept="image/*"
                        style={{ display: 'none' }}
                        onChange={(e) => {
                            const f = e.target.files?.[0];
                            if (f) {
                                void uploadPlan(f);
                            }
                        }}
                    />
                    <button
                        type="button"
                        className="ops-btn"
                        onClick={() => planInput.current?.click()}
                        disabled={uploading}
                        style={{
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: 7,
                            padding: '7px 12px',
                            borderRadius: 8,
                            border: `1px solid ${C.border}`,
                            background: C.card,
                            fontSize: 13,
                            fontWeight: 600,
                            color: C.ink,
                            cursor: uploading ? 'not-allowed' : 'pointer',
                            fontFamily: 'inherit',
                        }}
                    >
                        <ImageUp size={15} />
                        {uploading
                            ? 'Uploading…'
                            : plan
                              ? 'Replace plan'
                              : 'Upload plan'}
                    </button>
                </div>

                {plan ? (
                    <PyramidMap
                        src={plan}
                        pins={pins}
                        onPick={place}
                        style={{
                            background: C.card,
                            border: `1px solid ${C.border}`,
                            borderRadius: 14,
                            padding: 8,
                        }}
                    />
                ) : (
                    <button
                        type="button"
                        onClick={() => planInput.current?.click()}
                        style={{
                            width: '100%',
                            minHeight: 360,
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                            justifyContent: 'center',
                            gap: 12,
                            border: `2px dashed ${C.border}`,
                            borderRadius: 16,
                            background: C.card,
                            color: C.muted,
                            cursor: 'pointer',
                            fontFamily: 'inherit',
                        }}
                    >
                        <ImageUp size={34} color={C.green} />
                        <span style={{ fontSize: 15, fontWeight: 600 }}>
                            Upload the Pyramid floor plan
                        </span>
                        <span style={{ fontSize: 13, color: C.faint }}>
                            PNG or JPG — then click each venue's spot to pin it.
                        </span>
                    </button>
                )}
            </main>
        </div>
    );
}
