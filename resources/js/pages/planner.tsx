import { Head } from '@inertiajs/react';
import {
    ArrowUp,
    Bell,
    CalendarCheck,
    CalendarDays,
    ChevronDown,
    ChevronsUpDown,
    FileText,
    Folder,
    Home,
    LayoutGrid,
    LifeBuoy,
    ListChecks,
    type LucideIcon,
    Mail,
    MapPin,
    PanelLeft,
    PieChart,
    Plus,
    Search,
    Send,
    Settings,
    SlidersHorizontal,
    Sparkles,
    SquarePen,
    Triangle,
} from 'lucide-react';
import { useState } from 'react';

/* Landing-page palette — keep the studio consistent with the marketing site. */
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
};

const css = `
.pl-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif}
.pl-root *{box-sizing:border-box}
.pl-nav{transition:background .18s ease,color .18s ease}
.pl-nav:hover{background:${C.cream}}
.pl-nav.is-active{background:${C.greenTint};color:${C.green}}
.pl-nav.is-active svg{color:${C.green}}
.pl-rail-btn{transition:background .18s ease,color .18s ease}
.pl-rail-btn:hover{background:${C.cream}}
.pl-icon-btn{transition:background .18s ease,border-color .18s ease}
.pl-icon-btn:hover{background:${C.cream}}
.pl-chip{transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease}
.pl-chip:hover{border-color:${C.green};box-shadow:0 8px 20px -14px rgba(16,130,91,0.5);transform:translateY(-1px)}
.pl-send{transition:background .2s ease,transform .2s ease}
.pl-send:not(:disabled):hover{background:${C.greenDeep};transform:translateY(-1px)}
.pl-send:disabled{opacity:.45;cursor:not-allowed}
.pl-pill{transition:border-color .2s ease,background .2s ease}
.pl-pill:hover{border-color:${C.green}}
.pl-ghost:hover{background:${C.cream}}
.pl-composer{transition:border-color .2s ease,box-shadow .2s ease}
.pl-composer:focus-within{border-color:${C.green};box-shadow:0 0 0 4px rgba(16,130,91,0.1)}
.pl-input::placeholder{color:${C.faint}}
.pl-input{outline:none;border:none;resize:none;background:transparent;width:100%;font-family:inherit}
`;

const mainMenu: { label: string; icon: LucideIcon; active?: boolean }[] = [
    { label: 'Dashboard', icon: Home },
    { label: 'AI Planner', icon: Sparkles, active: true },
    { label: 'Spaces', icon: Search },
    { label: 'Requests', icon: Send },
];

const otherMenu: { label: string; icon: LucideIcon }[] = [
    { label: 'Approval Queue', icon: ListChecks },
    { label: 'Event Management', icon: CalendarDays },
    { label: 'Analytics', icon: PieChart },
];

const suggestions: { label: string; icon: LucideIcon; prompt: string }[] = [
    {
        label: 'Find a space',
        icon: MapPin,
        prompt: 'Find an available space at the Pyramid for my event — ',
    },
    {
        label: 'Draft a proposal',
        icon: FileText,
        prompt: 'Draft an event proposal the tenants are likely to approve for ',
    },
    {
        label: 'Check availability',
        icon: CalendarCheck,
        prompt: 'Check availability and conflicts for an event on ',
    },
];

export default function Planner() {
    const [input, setInput] = useState('');

    return (
        <div
            className="pl-root"
            style={{
                display: 'flex',
                height: '100vh',
                width: '100%',
                overflow: 'hidden',
                background: C.cream,
                color: C.ink,
            }}
        >
            <Head title="AI Planner">
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
                {/* Brand switcher */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 10,
                        padding: '6px 8px 16px',
                    }}
                >
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 32,
                            height: 32,
                            borderRadius: 9,
                            background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                        }}
                    >
                        <Triangle size={15} fill="#fff" color="#fff" />
                    </span>
                    <span
                        style={{
                            fontWeight: 800,
                            letterSpacing: '0.04em',
                            fontSize: 15,
                        }}
                    >
                        PIRAMIDA
                    </span>
                    <ChevronsUpDown
                        size={15}
                        color={C.faint}
                        style={{ marginLeft: 2 }}
                    />
                    <PanelLeft
                        size={16}
                        color={C.faint}
                        style={{ marginLeft: 'auto' }}
                    />
                </div>

                <SidebarGroup label="Main menu" items={mainMenu} />
                <div style={{ height: 18 }} />
                <SidebarGroup label="Other" items={otherMenu} />

                {/* Bottom */}
                <div style={{ marginTop: 'auto', paddingTop: 12 }}>
                    <NavRow icon={LifeBuoy} label="Support" />
                    <NavRow icon={Settings} label="Settings" />
                </div>
            </aside>

            {/* ===== TOOLS RAIL ===== */}
            <nav
                style={{
                    width: 56,
                    flex: 'none',
                    background: C.card,
                    borderRight: `1px solid ${C.borderSoft}`,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: 8,
                    padding: '18px 0',
                }}
            >
                <RailButton icon={PanelLeft} />
                <RailButton icon={SquarePen} active />
                <RailButton icon={Search} />
                <RailButton icon={Folder} />
                <div style={{ marginTop: 'auto' }}>
                    <RailButton icon={Triangle} />
                </div>
            </nav>

            {/* ===== MAIN ===== */}
            <main
                style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    minWidth: 0,
                }}
            >
                {/* Top bar */}
                <header
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        padding: '16px 26px',
                        borderBottom: `1px solid ${C.borderSoft}`,
                    }}
                >
                    <span style={{ fontSize: 16, fontWeight: 600 }}>
                        AI Planner
                    </span>
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 10,
                        }}
                    >
                        <button
                            className="pl-pill"
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: 8,
                                height: 36,
                                padding: '0 14px',
                                borderRadius: 9,
                                border: `1px solid ${C.border}`,
                                background: C.card,
                                fontSize: 13.5,
                                fontWeight: 600,
                                color: C.ink,
                                cursor: 'pointer',
                            }}
                        >
                            <LayoutGrid size={15} color={C.muted} />
                            Templates
                        </button>
                        <IconButton icon={Mail} />
                        <IconButton icon={Bell} />
                        <span
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: 36,
                                height: 36,
                                borderRadius: '50%',
                                background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                                color: '#fff',
                                fontSize: 13,
                                fontWeight: 700,
                            }}
                        >
                            PO
                        </span>
                    </div>
                </header>

                {/* Composer */}
                <div
                    style={{
                        flex: 1,
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        padding: '24px',
                        overflowY: 'auto',
                    }}
                >
                    <div
                        style={{
                            width: '100%',
                            maxWidth: 680,
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                        }}
                    >
                        {/* Orb */}
                        <div
                            style={{
                                position: 'relative',
                                width: 92,
                                height: 92,
                                marginBottom: 26,
                            }}
                        >
                            <div
                                style={{
                                    position: 'absolute',
                                    inset: -22,
                                    borderRadius: '50%',
                                    background:
                                        'radial-gradient(circle, rgba(16,130,91,0.38), transparent 70%)',
                                    filter: 'blur(18px)',
                                }}
                            />
                            <div
                                style={{
                                    position: 'relative',
                                    width: 92,
                                    height: 92,
                                    borderRadius: '50%',
                                    background:
                                        'radial-gradient(circle at 35% 28%, #6FC0A2 0%, #10825B 52%, #0C5A41 100%)',
                                    boxShadow:
                                        'inset 0 -9px 20px rgba(0,0,0,0.28), inset 0 7px 14px rgba(255,255,255,0.5)',
                                }}
                            />
                        </div>

                        <h1
                            style={{
                                fontSize: 32,
                                fontWeight: 700,
                                letterSpacing: '-0.02em',
                                textAlign: 'center',
                                marginBottom: 10,
                            }}
                        >
                            What event do you want to organize?
                        </h1>
                        <p
                            style={{
                                fontSize: 15.5,
                                color: C.muted,
                                textAlign: 'center',
                                maxWidth: 460,
                                marginBottom: 30,
                            }}
                        >
                            Piramida turns your idea into a ready-to-submit
                            event plan for the Pyramid of Tirana.
                        </p>

                        {/* Input card */}
                        <div
                            className="pl-composer"
                            style={{
                                width: '100%',
                                background: C.card,
                                border: `1px solid ${C.border}`,
                                borderRadius: 18,
                                boxShadow:
                                    '0 18px 40px -24px rgba(26,26,26,0.22)',
                                padding: '16px 18px 12px',
                            }}
                        >
                            <div
                                style={{
                                    display: 'flex',
                                    gap: 10,
                                    alignItems: 'flex-start',
                                }}
                            >
                                <Sparkles
                                    size={18}
                                    color={C.green}
                                    style={{ marginTop: 3, flex: 'none' }}
                                />
                                <textarea
                                    className="pl-input"
                                    rows={2}
                                    value={input}
                                    onChange={(e) => setInput(e.target.value)}
                                    placeholder="Describe the event you want to organize…"
                                    style={{
                                        fontSize: 15,
                                        color: C.ink,
                                        lineHeight: 1.5,
                                        padding: '2px 0',
                                    }}
                                />
                            </div>

                            <div
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 8,
                                    marginTop: 12,
                                }}
                            >
                                <ToolbarButton icon={Plus} />
                                <ToolbarButton icon={SlidersHorizontal} />
                                <button
                                    className="pl-pill"
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: 7,
                                        height: 32,
                                        padding: '0 11px',
                                        borderRadius: 8,
                                        border: `1px solid ${C.border}`,
                                        background: C.card,
                                        fontSize: 13,
                                        fontWeight: 600,
                                        color: C.ink,
                                        cursor: 'pointer',
                                    }}
                                >
                                    <LayoutGrid size={14} color={C.muted} />
                                    Piramida AI
                                    <ChevronDown size={14} color={C.muted} />
                                </button>

                                <button
                                    className="pl-send"
                                    type="button"
                                    disabled={input.trim() === ''}
                                    style={{
                                        marginLeft: 'auto',
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        width: 36,
                                        height: 36,
                                        borderRadius: 10,
                                        border: 'none',
                                        background: C.green,
                                        color: '#fff',
                                        cursor: 'pointer',
                                    }}
                                >
                                    <ArrowUp size={18} />
                                </button>
                            </div>
                        </div>

                        {/* Suggestions */}
                        <div
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                justifyContent: 'center',
                                gap: 10,
                                marginTop: 22,
                            }}
                        >
                            {suggestions.map((s) => (
                                <button
                                    key={s.label}
                                    type="button"
                                    className="pl-chip"
                                    onClick={() => setInput(s.prompt)}
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: 8,
                                        height: 38,
                                        padding: '0 16px',
                                        borderRadius: 999,
                                        border: `1px solid ${C.border}`,
                                        background: C.card,
                                        fontSize: 13.5,
                                        fontWeight: 500,
                                        color: C.ink,
                                        cursor: 'pointer',
                                    }}
                                >
                                    <s.icon size={15} color={C.green} />
                                    {s.label}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <footer
                    style={{
                        textAlign: 'center',
                        padding: '14px',
                        fontSize: 13,
                        color: C.faint,
                    }}
                >
                    Piramida AI can make mistakes.{' '}
                    <a
                        href="#"
                        style={{
                            color: C.muted,
                            textDecoration: 'underline',
                            textUnderlineOffset: 2,
                        }}
                    >
                        Check important info.
                    </a>
                </footer>
            </main>
        </div>
    );
}

function SidebarGroup({
    label,
    items,
}: {
    label: string;
    items: { label: string; icon: LucideIcon; active?: boolean }[];
}) {
    return (
        <div>
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
                {label}
            </p>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                {items.map((item) => (
                    <NavRow
                        key={item.label}
                        icon={item.icon}
                        label={item.label}
                        active={item.active}
                    />
                ))}
            </div>
        </div>
    );
}

function NavRow({
    icon: Icon,
    label,
    active = false,
}: {
    icon: LucideIcon;
    label: string;
    active?: boolean;
}) {
    return (
        <button
            type="button"
            className={`pl-nav${active ? 'is-active' : ''}`}
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
                color: active ? C.green : '#3A3A3A',
                cursor: 'pointer',
                textAlign: 'left',
            }}
        >
            <Icon size={17} color={active ? C.green : C.muted} />
            {label}
        </button>
    );
}

function RailButton({
    icon: Icon,
    active = false,
}: {
    icon: LucideIcon;
    active?: boolean;
}) {
    return (
        <button
            type="button"
            className="pl-rail-btn"
            style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: 36,
                height: 36,
                borderRadius: 9,
                border: 'none',
                background: active ? C.green : 'transparent',
                color: active ? '#fff' : C.muted,
                cursor: 'pointer',
            }}
        >
            <Icon size={17} color={active ? '#fff' : C.muted} />
        </button>
    );
}

function IconButton({ icon: Icon }: { icon: LucideIcon }) {
    return (
        <button
            type="button"
            className="pl-icon-btn"
            style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: 36,
                height: 36,
                borderRadius: 9,
                border: `1px solid ${C.border}`,
                background: C.card,
                cursor: 'pointer',
            }}
        >
            <Icon size={16} color={C.muted} />
        </button>
    );
}

function ToolbarButton({ icon: Icon }: { icon: LucideIcon }) {
    return (
        <button
            type="button"
            className="pl-ghost"
            style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: 32,
                height: 32,
                borderRadius: 8,
                border: 'none',
                background: 'transparent',
                cursor: 'pointer',
            }}
        >
            <Icon size={17} color={C.muted} />
        </button>
    );
}
