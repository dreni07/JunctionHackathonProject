import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

const C = {
    cream: '#F4F3EE',
    card: '#FFFFFF',
    border: '#E0DCD3',
    borderSoft: '#EAE7DC',
    green: '#10825B',
    greenDark: '#2A6F44',
    greenTint: '#D8E2DC',
    ink: '#1A1A1A',
    muted: '#6E6E6E',
    faint: '#9A958B',
};

const css = `
.fac-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:${C.cream};color:${C.ink}}
.fac-root *{box-sizing:border-box}
.fac-table{width:100%;border-collapse:collapse;font-size:13.5px}
.fac-table th{text-align:left;font-weight:600;color:${C.muted};background:${C.cream};padding:10px 14px;border-bottom:1px solid ${C.border};white-space:nowrap}
.fac-table td{padding:10px 14px;border-bottom:1px solid ${C.borderSoft};vertical-align:top}
.fac-table tr:last-child td{border-bottom:none}
.fac-table tbody tr:hover{background:rgba(16,130,91,0.035)}
.fac-link{color:${C.green};text-decoration:none;font-weight:600}
.fac-link:hover{text-decoration:underline}
`;

type Profile = {
    name: string;
    total_footprint_sqm: number;
    height_m: string;
    levels: number;
    access_points: string;
    allocation_rule: string;
    active_box_area_sqm: number;
    total_boxes: number;
    tumo_nodes: number;
    public_nodes: number;
    max_human_load: number;
    reference_baseline: string | null;
    source: string | null;
};

type Props = {
    profile: Profile | null;
    occupancyStandards: {
        functional_category: string;
        area_metric_sqm: string;
        allocation_rule: string;
    }[];
    levels: {
        level: number;
        label: string;
        active_boxes: number;
        box_footprint_sqm: number;
        tumo_nodes: number;
        public_nodes: number;
        max_human_load: number;
    }[];
    rooms: {
        room_code: string;
        box_ref: string;
        floor: number;
        zone_class: string;
        functional_type: string;
        area_sqm: number;
        capacity: number;
        workload_target: string;
    }[];
    zoneRules: {
        zone_classification: string;
        weekday_hours: string;
        weekend_hours: string;
        enforcement_protocol: string;
    }[];
    blackoutWindows: {
        scope: string;
        days: string;
        start_time: string;
        end_time: string;
        reason: string;
    }[];
    acousticRules: {
        event_target_profile: string;
        collision_profile: string;
        buffer_requirement: string;
    }[];
    infrastructureSpecs: {
        room_category: string;
        av_assets: string;
        climate_support: string;
        ingress_routing: string;
        power_kw: number;
    }[];
};

const fmt = (n: number) => n.toLocaleString('en-US');

export default function Facility({
    profile,
    occupancyStandards,
    levels,
    rooms,
    zoneRules,
    blackoutWindows,
    acousticRules,
    infrastructureSpecs,
}: Props) {
    return (
        <div className="fac-root" style={{ minHeight: '100vh', width: '100%' }}>
            <Head title="Pyramid facility data">
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

            {/* Top bar */}
            <header
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '16px clamp(20px,5vw,56px)',
                    borderBottom: `1px solid ${C.borderSoft}`,
                    background: C.card,
                }}
            >
                <Link
                    href="/"
                    style={{
                        display: 'flex',
                        alignItems: 'baseline',
                        gap: 8,
                        textDecoration: 'none',
                        color: C.ink,
                    }}
                >
                    <span style={{ fontWeight: 800, letterSpacing: '0.06em' }}>
                        PIRAMIDA
                    </span>
                    <span style={{ color: C.faint, fontSize: 13 }}>
                        Facility data
                    </span>
                </Link>
                <Link href="/planner" className="fac-link">
                    Open planner →
                </Link>
            </header>

            <div
                style={{
                    maxWidth: 1120,
                    margin: '0 auto',
                    padding: 'clamp(28px,5vw,52px) clamp(20px,5vw,56px) 72px',
                }}
            >
                {/* Hero / profile */}
                {profile && (
                    <section style={{ marginBottom: 44 }}>
                        <p
                            style={{
                                fontSize: 12,
                                fontWeight: 700,
                                letterSpacing: '0.14em',
                                textTransform: 'uppercase',
                                color: C.green,
                                marginBottom: 10,
                            }}
                        >
                            Data Schema & Operational Constraints
                        </p>
                        <h1
                            style={{
                                fontSize: 'clamp(28px,4vw,40px)',
                                fontWeight: 800,
                                letterSpacing: '-0.02em',
                                marginBottom: 10,
                            }}
                        >
                            {profile.name}
                        </h1>
                        <p
                            style={{
                                color: C.muted,
                                maxWidth: 620,
                                lineHeight: 1.6,
                                marginBottom: 22,
                            }}
                        >
                            {profile.allocation_rule}. Access via{' '}
                            {profile.access_points.toLowerCase()}.
                        </p>

                        <div
                            style={{
                                display: 'grid',
                                gridTemplateColumns:
                                    'repeat(auto-fit, minmax(150px, 1fr))',
                                gap: 14,
                            }}
                        >
                            {[
                                {
                                    label: 'Total footprint',
                                    value: `${fmt(profile.total_footprint_sqm)} m²`,
                                },
                                {
                                    label: 'Active box area',
                                    value: `${fmt(profile.active_box_area_sqm)} m²`,
                                },
                                {
                                    label: 'Height',
                                    value: `${profile.height_m} m`,
                                },
                                { label: 'Levels', value: profile.levels },
                                {
                                    label: 'Rooms (boxes)',
                                    value: profile.total_boxes,
                                },
                                {
                                    label: 'TUMO / Public',
                                    value: `${profile.tumo_nodes} / ${profile.public_nodes}`,
                                },
                                {
                                    label: 'Max human load',
                                    value: fmt(profile.max_human_load),
                                },
                            ].map((stat) => (
                                <div
                                    key={stat.label}
                                    style={{
                                        background: C.card,
                                        border: `1px solid ${C.border}`,
                                        borderRadius: 14,
                                        padding: '16px 18px',
                                    }}
                                >
                                    <div
                                        style={{
                                            fontSize: 22,
                                            fontWeight: 800,
                                            letterSpacing: '-0.01em',
                                        }}
                                    >
                                        {stat.value}
                                    </div>
                                    <div
                                        style={{
                                            fontSize: 12.5,
                                            color: C.muted,
                                            marginTop: 2,
                                        }}
                                    >
                                        {stat.label}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>
                )}

                <Section
                    title="Room inventory"
                    subtitle={`All ${rooms.length} colored boxes across 6 levels (Table 1.2).`}
                >
                    <Table
                        head={[
                            'Room',
                            'Box',
                            'Floor',
                            'Zone',
                            'Type',
                            'Area',
                            'Capacity',
                            'Primary workload',
                        ]}
                        rows={rooms.map((r) => [
                            r.room_code,
                            r.box_ref,
                            r.floor,
                            <Zone key={r.room_code} value={r.zone_class} />,
                            r.functional_type,
                            `${r.area_sqm} m²`,
                            r.capacity,
                            r.workload_target,
                        ])}
                    />
                </Section>

                <Section
                    title="Level statistics"
                    subtitle="Aggregated structural statistics by level (Table 1.3)."
                >
                    <Table
                        head={[
                            'Level',
                            'Label',
                            'Active boxes',
                            'Footprint',
                            'TUMO nodes',
                            'Public nodes',
                            'Max human load',
                        ]}
                        rows={levels.map((l) => [
                            l.level,
                            l.label,
                            l.active_boxes,
                            `${fmt(l.box_footprint_sqm)} m²`,
                            l.tumo_nodes,
                            l.public_nodes,
                            fmt(l.max_human_load),
                        ])}
                    />
                </Section>

                <Section
                    title="Occupancy & safety standards"
                    subtitle="Human occupancy density per functional category (Table 1.1)."
                >
                    <Table
                        head={[
                            'Functional category',
                            'Safety area / person',
                            'Allocation rule',
                        ]}
                        rows={occupancyStandards.map((s) => [
                            s.functional_category,
                            `${s.area_metric_sqm} m²`,
                            s.allocation_rule,
                        ])}
                    />
                </Section>

                <Section
                    title="Zone operating hours"
                    subtitle="Temporal bounds and AI enforcement per zone (Table 2.1)."
                >
                    <Table
                        head={[
                            'Zone',
                            'Weekday hours',
                            'Weekend hours',
                            'Enforcement protocol',
                        ]}
                        rows={zoneRules.map((z) => [
                            z.zone_classification,
                            z.weekday_hours,
                            z.weekend_hours,
                            z.enforcement_protocol,
                        ])}
                    />
                </Section>

                <Section
                    title="Blackout windows"
                    subtitle="Maintenance/security intervals when no events may be scheduled (§2.4)."
                >
                    <Table
                        head={['Scope', 'Days', 'From', 'To', 'Reason']}
                        rows={blackoutWindows.map((b) => [
                            b.scope,
                            b.days,
                            b.start_time,
                            b.end_time,
                            b.reason,
                        ])}
                    />
                </Section>

                <Section
                    title="Acoustic proximity rules"
                    subtitle="Sound-isolation buffers between incompatible activities (Table 2.2)."
                >
                    <Table
                        head={[
                            'Event profile',
                            'Conflicts with',
                            'Required buffer',
                        ]}
                        rows={acousticRules.map((a) => [
                            a.event_target_profile,
                            a.collision_profile,
                            a.buffer_requirement,
                        ])}
                    />
                </Section>

                <Section
                    title="Infrastructure matrix"
                    subtitle="AV, climate, ingress, and power per room category (Table 3.1)."
                >
                    <Table
                        head={[
                            'Room category',
                            'AV & digital assets',
                            'Climate',
                            'Ingress',
                            'Power',
                        ]}
                        rows={infrastructureSpecs.map((i) => [
                            i.room_category,
                            i.av_assets,
                            i.climate_support,
                            i.ingress_routing,
                            `${i.power_kw} kW`,
                        ])}
                    />
                </Section>

                {profile?.source && (
                    <p
                        style={{
                            marginTop: 32,
                            fontSize: 12.5,
                            color: C.faint,
                        }}
                    >
                        {profile.reference_baseline} · {profile.source}
                    </p>
                )}
            </div>
        </div>
    );
}

function Section({
    title,
    subtitle,
    children,
}: {
    title: string;
    subtitle: string;
    children: ReactNode;
}) {
    return (
        <section style={{ marginBottom: 36 }}>
            <h2
                style={{
                    fontSize: 19,
                    fontWeight: 700,
                    letterSpacing: '-0.01em',
                }}
            >
                {title}
            </h2>
            <p style={{ fontSize: 13.5, color: C.muted, margin: '4px 0 14px' }}>
                {subtitle}
            </p>
            <div
                style={{
                    background: C.card,
                    border: `1px solid ${C.border}`,
                    borderRadius: 14,
                    overflow: 'hidden',
                }}
            >
                <div style={{ overflowX: 'auto' }}>{children}</div>
            </div>
        </section>
    );
}

function Table({ head, rows }: { head: string[]; rows: ReactNode[][] }) {
    return (
        <table className="fac-table">
            <thead>
                <tr>
                    {head.map((h) => (
                        <th key={h}>{h}</th>
                    ))}
                </tr>
            </thead>
            <tbody>
                {rows.map((row, ri) => (
                    <tr key={ri}>
                        {row.map((cell, ci) => (
                            <td key={ci}>{cell}</td>
                        ))}
                    </tr>
                ))}
            </tbody>
        </table>
    );
}

function Zone({ value }: { value: string }) {
    const isTumo = value === 'TUMO';

    return (
        <span
            style={{
                display: 'inline-block',
                padding: '2px 9px',
                borderRadius: 999,
                fontSize: 12,
                fontWeight: 600,
                color: isTumo ? C.green : '#8A6D1C',
                background: isTumo ? C.greenTint : 'rgba(138,109,28,0.12)',
            }}
        >
            {value}
        </span>
    );
}
