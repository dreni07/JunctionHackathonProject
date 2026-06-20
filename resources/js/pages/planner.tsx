import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowUp,
    CalendarClock,
    Check,
    ChevronDown,
    Clock,
    Euro,
    FileText,
    Home as HomeIcon,
    Loader2,
    LogIn,
    LogOut,
    MapPin,
    MessageSquare,
    Mic,
    Sparkles,
    Tag,
    TriangleAlert,
    Triangle,
    Upload,
    UploadCloud,
    UserRoundPen,
    Users,
    Volume2,
    X,
    type LucideIcon,
} from 'lucide-react';
import { useEffect, useRef, useState, type CSSProperties } from 'react';
import { PyramidMap } from '@/components/pyramid-map';
import type { Auth } from '@/types';

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
    danger: '#B4453A',
    amber: '#8A6D1C',
};

const css = `
.pl-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif}
.pl-root *{box-sizing:border-box}
.pl-card{position:relative;overflow:hidden;transition:border-color .25s ease,box-shadow .35s cubic-bezier(.2,.8,.2,1),transform .35s cubic-bezier(.2,.8,.2,1)}
.pl-card::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--pl-accent,#10825B),color-mix(in srgb,var(--pl-accent,#10825B) 35%,transparent));opacity:.85}
.pl-card:hover{border-color:color-mix(in srgb,var(--pl-accent,#10825B) 45%,#E0DCD3)!important;box-shadow:0 28px 56px -28px color-mix(in srgb,var(--pl-accent,#10825B) 38%,transparent);transform:translateY(-4px)}
.pl-card:hover .pl-card-arrow{transform:translate(4px,-4px);opacity:1;color:var(--pl-accent,#10825B)}
.pl-card-featured{background:linear-gradient(165deg,#fff 0%,#F7FAF8 52%,#EEF5F1 100%)!important}
.pl-icon-btn{transition:background .18s ease,border-color .18s ease,color .18s ease}
.pl-icon-btn:hover{background:${C.cream}}
.pl-nav-link{transition:color .18s ease,background .18s ease}
.pl-nav-link:hover{background:${C.cream};color:${C.ink}}
.pl-send{transition:background .2s ease,transform .2s ease}
.pl-send:not(:disabled):hover{background:${C.greenDeep};transform:translateY(-1px)}
.pl-send:disabled{opacity:.4;cursor:not-allowed}
.pl-ghost{transition:background .18s ease,color .18s ease}
.pl-ghost:hover{background:${C.cream};color:${C.ink}}
.pl-input::placeholder{color:${C.faint}}
.pl-input{outline:none;border:none;resize:none;background:transparent;width:100%;font-family:inherit}
.pl-composer{transition:border-color .2s ease,box-shadow .2s ease}
.pl-composer:focus-within{border-color:${C.green};box-shadow:0 0 0 4px rgba(16,130,91,0.1)}
.pl-drop{transition:border-color .2s ease,background .2s ease}
.pl-drop.is-over,.pl-drop:hover{border-color:${C.green};background:rgba(16,130,91,0.04)}
.pl-hero-glow{position:absolute;border-radius:50%;filter:blur(60px);pointer-events:none}
.pl-hero-glow-a{top:-80px;left:-40px;width:320px;height:320px;background:rgba(16,130,91,0.16);animation:pl-float 9s ease-in-out infinite}
.pl-hero-glow-b{bottom:-120px;right:-60px;width:380px;height:380px;background:rgba(42,111,68,0.12);animation:pl-float 9s ease-in-out infinite;animation-delay:-3s}
@keyframes pl-float{0%,100%{transform:translateY(0)}50%{transform:translateY(18px)}}
.pl-hero-glow-a{top:-80px;left:-40px;width:320px;height:320px;background:rgba(16,130,91,0.16);animation:pl-float 9s ease-in-out infinite}
.pl-hero-glow-b{bottom:-120px;right:-60px;width:380px;height:380px;background:rgba(42,111,68,0.12);animation:pl-float 9s ease-in-out infinite;animation-delay:-3s}
@keyframes pl-float{0%,100%{transform:translateY(0)}50%{transform:translateY(18px)}}
.pl-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;border:1px solid color-mix(in srgb,var(--pl-accent,#10825B) 20%,transparent);background:color-mix(in srgb,var(--pl-accent,#10825B) 8%,#fff);color:var(--pl-accent,#10825B)}
.pl-bento{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:clamp(14px,2vw,20px);width:100%;max-width:920px}
.pl-bento-card{grid-column:span 4}
.pl-bento-featured{grid-column:span 12}
@media(max-width:860px){.pl-bento-card{grid-column:span 12}}
@media(max-width:640px){.pl-header-sub,.pl-header-divider{display:none!important}}
@media(min-width:641px){.pl-user-name{display:inline!important}}
@media(min-width:900px){.pl-header-action.pl-header-sub{display:inline-flex!important}}
@keyframes pl-rise{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.pl-rise{animation:pl-rise .55s cubic-bezier(.2,.8,.2,1) both}
@keyframes pl-pulse{0%{transform:scale(1);opacity:.55}70%{transform:scale(2.1);opacity:0}100%{opacity:0}}
@keyframes pl-spin{to{transform:rotate(360deg)}}
@keyframes pl-blink{0%,80%,100%{opacity:.2}40%{opacity:1}}
.pl-spin{animation:pl-spin 1s linear infinite}
@keyframes pl-breathe{0%,100%{transform:scale(1)}50%{transform:scale(1.07)}}
.pl-breathe{animation:pl-breathe 1.4s ease-in-out infinite}
.pl-ring{position:absolute;inset:0;margin:auto;width:108px;height:108px;border-radius:50%;background:conic-gradient(from 0deg, rgba(16,130,91,0) 0deg, rgba(16,130,91,0) 210deg, ${C.green} 340deg, rgba(16,130,91,0) 360deg);-webkit-mask:radial-gradient(farthest-side, transparent calc(100% - 3px), #000 calc(100% - 2px));mask:radial-gradient(farthest-side, transparent calc(100% - 3px), #000 calc(100% - 2px));animation:pl-spin 0.9s linear infinite}
@keyframes pl-bar{0%,100%{transform:scaleY(0.3)}50%{transform:scaleY(1)}}
.pl-mic-ping{position:absolute;inset:0;border-radius:inherit;box-shadow:0 0 0 0 rgba(16,130,91,0.45);animation:pl-micping 1.8s ease-out infinite}
@keyframes pl-micping{0%{box-shadow:0 0 0 0 rgba(16,130,91,0.4)}100%{box-shadow:0 0 0 22px rgba(16,130,91,0)}}
@keyframes pl-fade{from{opacity:0}to{opacity:1}}
@keyframes pl-modal-in{from{opacity:0;transform:translateY(10px) scale(.97)}to{opacity:1;transform:none}}
.pl-modal-bg{animation:pl-fade .2s ease}
.pl-modal-card{animation:pl-modal-in .26s cubic-bezier(.2,.8,.2,1)}
.pl-header{position:sticky;top:0;z-index:40;border-bottom:1px solid ${C.borderSoft};background:rgba(255,255,255,.78);backdrop-filter:blur(18px) saturate(1.2);-webkit-backdrop-filter:blur(18px) saturate(1.2);box-shadow:0 1px 0 rgba(255,255,255,.65) inset,0 8px 28px -22px rgba(26,26,26,.12)}
.pl-header-brand{transition:transform .22s ease,opacity .22s ease}
.pl-header-brand:hover{transform:translateY(-1px);opacity:.92}
.pl-header-nav{display:inline-flex;align-items:center;gap:4px;padding:5px;border-radius:999px;background:color-mix(in srgb,${C.cream} 88%,#fff);border:1px solid ${C.borderSoft}}
.pl-header-nav-item{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:999px;border:none;background:transparent;font-family:inherit;font-size:13px;font-weight:600;color:${C.muted};cursor:pointer;white-space:nowrap;transition:background .18s ease,color .18s ease,box-shadow .18s ease,transform .18s ease}
.pl-header-nav-item:hover:not(.is-active){color:${C.ink};background:rgba(255,255,255,.72)}
.pl-header-nav-item.is-active{background:${C.card};color:${C.green};box-shadow:0 4px 14px -8px rgba(16,130,91,.35),0 1px 0 rgba(255,255,255,.8) inset}
.pl-header-nav-item:active{transform:scale(.98)}
.pl-header-action{transition:background .18s ease,border-color .18s ease,color .18s ease,transform .18s ease,box-shadow .18s ease}
.pl-header-action:hover{background:${C.cream};border-color:color-mix(in srgb,${C.green} 28%,${C.border});transform:translateY(-1px)}
.pl-header-action.is-open{background:${C.greenTint};border-color:color-mix(in srgb,${C.green} 35%,${C.border})}
.pl-header-menu{position:absolute;right:0;top:calc(100% + 10px);min-width:260px;border-radius:18px;border:1px solid ${C.borderSoft};background:${C.card};box-shadow:0 24px 60px -28px rgba(26,26,26,.28);padding:8px;animation:pl-modal-in .22s cubic-bezier(.2,.8,.2,1);transform-origin:top right}
.pl-header-menu-item{display:flex;align-items:center;gap:10px;width:100%;padding:10px 12px;border-radius:12px;border:none;background:transparent;font-family:inherit;font-size:14px;font-weight:500;color:${C.ink};cursor:pointer;text-decoration:none;transition:background .15s ease,color .15s ease;text-align:left}
.pl-header-menu-item:hover{background:${C.cream}}
.pl-header-menu-item.is-danger{color:${C.danger}}
.pl-header-menu-item.is-danger:hover{background:rgba(180,69,58,.08)}
.pl-header-progress{height:6px;border-radius:999px;background:${C.cream};overflow:hidden}
.pl-header-progress-bar{height:100%;border-radius:999px;background:linear-gradient(90deg,${C.green},${C.greenDark});transition:width .35s ease}
.pl-header-nav-row{display:none;padding:0 clamp(14px,3vw,28px) 12px}
@media(max-width:820px){.pl-header-center{display:none!important}.pl-header-nav-row{display:block}}
@media(min-width:821px){.pl-header-nav-row{display:none!important}}
`;

type Mode = 'home' | 'voice' | 'chat' | 'upload';

const MODE_NAV: {
    mode: Mode;
    label: string;
    icon: LucideIcon;
}[] = [
    { mode: 'home', label: 'Studio', icon: HomeIcon },
    { mode: 'voice', label: 'Voice', icon: Mic },
    { mode: 'chat', label: 'Chat', icon: MessageSquare },
    { mode: 'upload', label: 'Brief', icon: Upload },
];

function PlannerHeader({
    mode,
    onModeChange,
    user,
    profileCompletion,
    initials,
}: {
    mode: Mode;
    onModeChange: (mode: Mode) => void;
    user: Auth['user'] | null;
    profileCompletion: number | null;
    initials: string | null;
}) {
    const [menuOpen, setMenuOpen] = useState(false);
    const menuRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!menuOpen) {
            return;
        }

        const onPointerDown = (event: MouseEvent) => {
            if (
                menuRef.current &&
                !menuRef.current.contains(event.target as Node)
            ) {
                setMenuOpen(false);
            }
        };

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setMenuOpen(false);
            }
        };

        document.addEventListener('mousedown', onPointerDown);
        document.addEventListener('keydown', onKeyDown);

        return () => {
            document.removeEventListener('mousedown', onPointerDown);
            document.removeEventListener('keydown', onKeyDown);
        };
    }, [menuOpen]);

    const modeNav = (
        <nav
            className="pl-header-nav"
            aria-label="Planner modes"
            role="tablist"
        >
            {MODE_NAV.map((item) => {
                const active = mode === item.mode;

                return (
                    <button
                        key={item.mode}
                        type="button"
                        role="tab"
                        aria-selected={active}
                        className={`pl-header-nav-item${active ? ' is-active' : ''}`}
                        onClick={() => onModeChange(item.mode)}
                    >
                        <item.icon size={15} />
                        {item.label}
                    </button>
                );
            })}
        </nav>
    );

    return (
        <header className="pl-header">
            <div
                style={{
                    position: 'relative',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    gap: 16,
                    padding: '12px clamp(14px,3vw,28px)',
                }}
            >
                <Link
                    href="/"
                    className="pl-header-brand"
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 12,
                        textDecoration: 'none',
                        color: C.ink,
                        minWidth: 0,
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
                            borderRadius: 12,
                            background: `linear-gradient(145deg, ${C.green}, ${C.greenDark})`,
                            boxShadow: '0 10px 24px -12px rgba(16,130,91,0.55)',
                        }}
                    >
                        <Triangle size={16} fill="#fff" color="#fff" />
                    </span>
                    <span style={{ minWidth: 0 }}>
                        <span
                            style={{
                                display: 'block',
                                fontWeight: 800,
                                letterSpacing: '0.06em',
                                fontSize: 13.5,
                                lineHeight: 1.1,
                            }}
                        >
                            PIRAMIDA
                        </span>
                        <span
                            className="pl-header-sub"
                            style={{
                                display: 'block',
                                fontSize: 12,
                                fontWeight: 600,
                                color: C.muted,
                                marginTop: 2,
                            }}
                        >
                            AI Event Planner
                        </span>
                    </span>
                </Link>

                <div
                    className="pl-header-center"
                    style={{
                        position: 'absolute',
                        left: '50%',
                        transform: 'translateX(-50%)',
                    }}
                >
                    {modeNav}
                </div>

                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 10,
                        marginLeft: 'auto',
                    }}
                >
                    {user ? (
                        <>
                            {profileCompletion !== null &&
                                profileCompletion < 100 && (
                                    <Link
                                        href="/profile/complete"
                                        className="pl-header-action pl-header-sub"
                                        style={{
                                            display: 'none',
                                            alignItems: 'center',
                                            gap: 8,
                                            padding: '8px 12px',
                                            borderRadius: 999,
                                            border: `1px solid ${C.borderSoft}`,
                                            background: C.card,
                                            color: C.green,
                                            fontSize: 12,
                                            fontWeight: 700,
                                            textDecoration: 'none',
                                        }}
                                    >
                                        <UserRoundPen size={14} />
                                        {profileCompletion}%
                                    </Link>
                                )}
                            <div
                                ref={menuRef}
                                style={{ position: 'relative' }}
                            >
                                <button
                                    type="button"
                                    aria-haspopup="menu"
                                    aria-expanded={menuOpen}
                                    className={`pl-header-action${menuOpen ? ' is-open' : ''}`}
                                    onClick={() => setMenuOpen((open) => !open)}
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: 10,
                                        padding: '6px 8px 6px 6px',
                                        borderRadius: 999,
                                        border: `1px solid ${C.borderSoft}`,
                                        background: C.card,
                                        cursor: 'pointer',
                                        fontFamily: 'inherit',
                                    }}
                                >
                                    <span
                                        style={{
                                            position: 'relative',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            width: 36,
                                            height: 36,
                                            borderRadius: '50%',
                                            overflow: 'hidden',
                                            background: user.avatar
                                                ? undefined
                                                : `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                                            color: '#fff',
                                            fontSize: 12,
                                            fontWeight: 700,
                                        }}
                                    >
                                        {user.avatar ? (
                                            <img
                                                src={user.avatar}
                                                alt=""
                                                style={{
                                                    width: '100%',
                                                    height: '100%',
                                                    objectFit: 'cover',
                                                }}
                                            />
                                        ) : (
                                            initials
                                        )}
                                        {profileCompletion !== null &&
                                            profileCompletion < 100 && (
                                                <span
                                                    style={{
                                                        position: 'absolute',
                                                        right: -1,
                                                        bottom: -1,
                                                        width: 10,
                                                        height: 10,
                                                        borderRadius: '50%',
                                                        background: C.amber,
                                                        border: `2px solid ${C.card}`,
                                                    }}
                                                    title="Profile incomplete"
                                                />
                                            )}
                                    </span>
                                    <span
                                        className="pl-user-name"
                                        style={{
                                            fontSize: 13,
                                            fontWeight: 600,
                                            color: C.ink,
                                            maxWidth: 120,
                                            overflow: 'hidden',
                                            textOverflow: 'ellipsis',
                                            whiteSpace: 'nowrap',
                                        }}
                                    >
                                        {user.name}
                                    </span>
                                    <ChevronDown
                                        size={15}
                                        color={C.muted}
                                        style={{
                                            transition: 'transform .2s ease',
                                            transform: menuOpen
                                                ? 'rotate(180deg)'
                                                : undefined,
                                        }}
                                    />
                                </button>

                                {menuOpen && (
                                    <div
                                        className="pl-header-menu"
                                        role="menu"
                                    >
                                        <div
                                            style={{
                                                padding: '10px 12px 12px',
                                                borderBottom: `1px solid ${C.borderSoft}`,
                                                marginBottom: 6,
                                            }}
                                        >
                                            <div
                                                style={{
                                                    fontSize: 14,
                                                    fontWeight: 700,
                                                }}
                                            >
                                                {user.name}
                                            </div>
                                            <div
                                                style={{
                                                    fontSize: 12.5,
                                                    color: C.muted,
                                                    marginTop: 2,
                                                }}
                                            >
                                                {user.email}
                                            </div>
                                            {profileCompletion !== null &&
                                                profileCompletion < 100 && (
                                                    <div
                                                        style={{ marginTop: 12 }}
                                                    >
                                                        <div
                                                            style={{
                                                                display: 'flex',
                                                                justifyContent:
                                                                    'space-between',
                                                                fontSize: 11.5,
                                                                fontWeight: 600,
                                                                color: C.muted,
                                                                marginBottom: 6,
                                                            }}
                                                        >
                                                            <span>
                                                                Profile
                                                            </span>
                                                            <span
                                                                style={{
                                                                    color: C.green,
                                                                }}
                                                            >
                                                                {profileCompletion}%
                                                            </span>
                                                        </div>
                                                        <div className="pl-header-progress">
                                                            <div
                                                                className="pl-header-progress-bar"
                                                                style={{
                                                                    width: `${profileCompletion}%`,
                                                                }}
                                                            />
                                                        </div>
                                                    </div>
                                                )}
                                        </div>
                                        <Link
                                            href="/profile/complete"
                                            className="pl-header-menu-item"
                                            role="menuitem"
                                            onClick={() => setMenuOpen(false)}
                                        >
                                            <UserRoundPen size={16} />
                                            {profileCompletion !== null &&
                                            profileCompletion < 100
                                                ? 'Complete profile'
                                                : 'Edit profile'}
                                        </Link>
                                        <button
                                            type="button"
                                            className="pl-header-menu-item is-danger"
                                            role="menuitem"
                                            onClick={() => {
                                                setMenuOpen(false);
                                                router.post('/logout');
                                            }}
                                        >
                                            <LogOut size={16} />
                                            Sign out
                                        </button>
                                    </div>
                                )}
                            </div>
                        </>
                    ) : (
                        <>
                            <Link
                                href="/login"
                                className="pl-header-action"
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: 7,
                                    padding: '9px 16px',
                                    borderRadius: 999,
                                    border: `1px solid ${C.border}`,
                                    background: C.card,
                                    color: C.ink,
                                    fontSize: 13,
                                    fontWeight: 600,
                                    textDecoration: 'none',
                                }}
                            >
                                <LogIn size={15} />
                                Sign in
                            </Link>
                            <Link
                                href="/register"
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: 7,
                                    padding: '9px 16px',
                                    borderRadius: 999,
                                    border: 'none',
                                    background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                                    color: '#fff',
                                    fontSize: 13,
                                    fontWeight: 700,
                                    textDecoration: 'none',
                                    boxShadow:
                                        '0 12px 28px -12px rgba(16,130,91,0.55)',
                                    transition:
                                        'transform .2s ease, box-shadow .2s ease',
                                }}
                                onMouseEnter={(e) => {
                                    e.currentTarget.style.transform =
                                        'translateY(-1px)';
                                }}
                                onMouseLeave={(e) => {
                                    e.currentTarget.style.transform = '';
                                }}
                            >
                                Get started
                            </Link>
                        </>
                    )}
                </div>
            </div>

            <div className="pl-header-nav-row">{modeNav}</div>
        </header>
    );
}

function ProfileCompletionBanner({ completion }: { completion: number }) {
    const [dismissed, setDismissed] = useState(false);

    if (dismissed || completion >= 100) {
        return null;
    }

    return (
        <div
            style={{
                display: 'flex',
                alignItems: 'center',
                gap: 14,
                margin: '0 clamp(18px,3vw,28px)',
                padding: '12px 16px',
                borderRadius: 12,
                border: `1px solid ${C.greenTint}`,
                background: C.card,
                boxShadow: '0 10px 28px -24px rgba(16,130,91,0.35)',
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
                    background: C.greenTint,
                    color: C.green,
                }}
            >
                <UserRoundPen size={17} />
            </span>
            <div style={{ minWidth: 0, flex: 1 }}>
                <div style={{ fontSize: 14, fontWeight: 700 }}>
                    Complete your profile
                </div>
                <div style={{ fontSize: 13, color: C.muted, marginTop: 2 }}>
                    Add a photo and a few details — your profile is {completion}%
                    complete.
                </div>
            </div>
            <Link
                href="/profile/complete"
                style={{
                    flex: 'none',
                    padding: '8px 14px',
                    borderRadius: 999,
                    background: C.green,
                    color: '#fff',
                    fontSize: 13,
                    fontWeight: 700,
                    textDecoration: 'none',
                }}
            >
                Complete now
            </Link>
            <button
                type="button"
                onClick={() => setDismissed(true)}
                aria-label="Dismiss"
                style={{
                    flex: 'none',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    width: 28,
                    height: 28,
                    border: 'none',
                    borderRadius: 8,
                    background: 'transparent',
                    color: C.muted,
                    cursor: 'pointer',
                }}
            >
                <X size={16} />
            </button>
        </div>
    );
}

/* CSRF-aware fetch helper (Laravel reads the X-XSRF-TOKEN header). */
function csrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function postForm(url: string, body: FormData): Promise<Response> {
    return fetch(url, {
        method: 'POST',
        body,
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-XSRF-TOKEN': csrfToken() },
    });
}

async function postJson(url: string, payload: unknown): Promise<Response> {
    return fetch(url, {
        method: 'POST',
        body: JSON.stringify(payload),
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': csrfToken(),
        },
    });
}

type AgentMode = 'advisor' | 'intake';

type AgentResult = {
    reply: string;
    review: EventDetails | null;
    submitted: { id: string; status: string; price?: number | null } | null;
    ended: boolean;
};

/**
 * The single entry point into the planner's agentic model — the same advisor →
 * intake loop, tools, and venue/pricing review for EVERY surface (voice, chat,
 * document). `modeRef` carries which agent is in charge across turns.
 */
async function runPlannerAgent(
    messages: { role: 'user' | 'assistant'; content: string }[],
    modeRef: { current: AgentMode },
): Promise<AgentResult> {
    const response = await postJson('/planner/agent', {
        messages: messages.map((m) => ({ role: m.role, content: m.content })),
        mode: modeRef.current,
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(data.message || 'Agent error.');
    }
    if (data.mode === 'advisor' || data.mode === 'intake') {
        modeRef.current = data.mode;
    }
    return {
        reply: String(data.reply || ''),
        review: data.review ?? null,
        submitted: data.submitted ?? null,
        ended: Boolean(data.ended),
    };
}

export default function Planner() {
    const [mode, setMode] = useState<Mode>('home');
    // Text extracted from an uploaded brief, handed straight to the chat agent.
    const [chatSeed, setChatSeed] = useState<string | null>(null);
    const { auth } = usePage<{ auth: Auth }>().props;
    const user = auth.user;
    const initials = user?.name
        ? user.name
              .split(' ')
              .map((part) => part[0])
              .slice(0, 2)
              .join('')
              .toUpperCase()
        : null;

    return (
        <div
            className="pl-root"
            style={{
                display: 'flex',
                flexDirection: 'column',
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

            <PlannerHeader
                mode={mode}
                onModeChange={setMode}
                user={user}
                profileCompletion={auth.profileCompletion}
                initials={initials}
            />

            {user && auth.profileCompletion !== null && auth.profileCompletion < 100 && (
                <ProfileCompletionBanner completion={auth.profileCompletion} />
            )}

            {/* ===== BODY ===== */}
            <div
                style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    minHeight: 0,
                }}
            >
                {mode === 'home' && (
                    <Home
                        onSelect={(m) => {
                            setChatSeed(null);
                            setMode(m);
                        }}
                    />
                )}
                {mode === 'voice' && (
                    <VoiceMode onExit={() => setMode('home')} />
                )}
                {mode === 'chat' && (
                    <ChatMode
                        initialMessage={chatSeed ?? undefined}
                        initialMode={chatSeed ? 'intake' : 'advisor'}
                    />
                )}
                {mode === 'upload' && (
                    <UploadMode
                        onExtracted={(text) => {
                            setChatSeed(text);
                            setMode('chat');
                        }}
                    />
                )}
            </div>
        </div>
    );
}

/* ============================ HOME ============================ */

const actions: {
    mode: Mode;
    icon: LucideIcon;
    num: string;
    tag: string;
    title: string;
    subtitle: string;
    accent: string;
    featured?: boolean;
}[] = [
    {
        mode: 'voice',
        icon: Mic,
        num: '01',
        tag: 'Recommended',
        title: 'Talk to Kleopatra',
        subtitle:
            'Describe your event out loud — venue, timing, and budget in one natural conversation.',
        accent: C.green,
        featured: true,
    },
    {
        mode: 'chat',
        icon: MessageSquare,
        num: '02',
        tag: 'Chat',
        title: 'Plan in writing',
        subtitle:
            'Type details at your pace, refine answers, and iterate before you submit.',
        accent: C.greenDark,
    },
    {
        mode: 'upload',
        icon: Upload,
        num: '03',
        tag: 'Brief',
        title: 'Upload a brief',
        subtitle:
            'Drop a PDF or image and let the agent extract the event requirements for you.',
        accent: '#8A6D1C',
    },
];

function Home({ onSelect }: { onSelect: (mode: Mode) => void }) {
    return (
        <div
            style={{
                flex: 1,
                position: 'relative',
                overflowY: 'auto',
                padding: 'clamp(24px,4vw,48px)',
            }}
        >
            <div className="pl-hero-glow pl-hero-glow-a" />
            <div className="pl-hero-glow pl-hero-glow-b" />

            <div
                style={{
                    position: 'relative',
                    zIndex: 1,
                    maxWidth: 980,
                    margin: '0 auto',
                }}
            >
                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'minmax(0,1fr) auto',
                        gap: 28,
                        alignItems: 'center',
                        marginBottom: 36,
                    }}
                >
                    <div>
                        <span className="pl-chip" style={{ ['--pl-accent' as string]: C.green, marginBottom: 16 }}>
                            <Sparkles size={12} />
                            AI Event Studio
                        </span>
                        <h1
                            style={{
                                fontSize: 'clamp(32px,5vw,46px)',
                                fontWeight: 800,
                                letterSpacing: '-0.03em',
                                lineHeight: 1.08,
                                marginBottom: 12,
                            }}
                        >
                            Plan your next event at the Pyramid
                        </h1>
                        <p
                            style={{
                                fontSize: 'clamp(15px,2vw,17px)',
                                color: C.muted,
                                lineHeight: 1.65,
                                maxWidth: 560,
                            }}
                        >
                            Choose how you want to start. Kleopatra listens,
                            chats, or reads your brief — then recommends venues,
                            pricing, and a path to submit.
                        </p>
                    </div>
                    <div style={{ justifySelf: 'center' }}>
                        <Orb size={108} phase="idle" />
                    </div>
                </div>

                <div
                    className="pl-bento"
                    style={{
                        display: 'grid',
                        gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
                        gap: 18,
                    }}
                >
                    {actions.map((action, index) => (
                        <button
                            key={action.mode}
                            type="button"
                            className={`pl-card pl-rise${action.featured ? ' pl-card-featured' : ''}`}
                            onClick={() => onSelect(action.mode)}
                            style={{
                                ['--pl-accent' as string]: action.accent,
                                animationDelay: `${index * 0.08}s`,
                                gridColumn: action.featured
                                    ? 'span 2'
                                    : 'auto',
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'flex-start',
                                gap: 18,
                                padding: '26px 24px 22px',
                                borderRadius: 22,
                                border: `1px solid ${C.border}`,
                                background: C.card,
                                cursor: 'pointer',
                                textAlign: 'left',
                                boxShadow:
                                    '0 12px 36px -28px rgba(26,26,26,0.28)',
                            }}
                        >
                            <div
                                style={{
                                    display: 'flex',
                                    width: '100%',
                                    alignItems: 'flex-start',
                                    justifyContent: 'space-between',
                                    gap: 12,
                                }}
                            >
                                <div
                                    style={{
                                        display: 'flex',
                                        flexDirection: 'column',
                                        gap: 10,
                                    }}
                                >
                                    <span
                                        style={{
                                            fontSize: 12,
                                            fontWeight: 700,
                                            letterSpacing: '0.12em',
                                            textTransform: 'uppercase',
                                            color: C.faint,
                                        }}
                                    >
                                        {action.num}
                                    </span>
                                    <span
                                        className="pl-chip"
                                        style={{
                                            ['--pl-accent' as string]:
                                                action.accent,
                                        }}
                                    >
                                        {action.tag}
                                    </span>
                                </div>
                                <span
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        width: 48,
                                        height: 48,
                                        borderRadius: 14,
                                        background: `color-mix(in srgb, ${action.accent} 12%, #fff)`,
                                        color: action.accent,
                                        flexShrink: 0,
                                    }}
                                >
                                    <action.icon size={22} />
                                </span>
                            </div>

                            <div style={{ width: '100%' }}>
                                <div
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'space-between',
                                        gap: 10,
                                    }}
                                >
                                    <span
                                        style={{
                                            fontSize: 'clamp(18px,2.2vw,22px)',
                                            fontWeight: 800,
                                            letterSpacing: '-0.02em',
                                        }}
                                    >
                                        {action.title}
                                    </span>
                                    <ArrowUp
                                        className="pl-card-arrow"
                                        size={18}
                                        color={action.accent}
                                        style={{
                                            transform: 'rotate(45deg)',
                                            opacity: 0.55,
                                            transition:
                                                'transform .25s ease, opacity .25s ease',
                                            flexShrink: 0,
                                        }}
                                    />
                                </div>
                                <p
                                    style={{
                                        marginTop: 8,
                                        fontSize: 14.5,
                                        lineHeight: 1.6,
                                        color: C.muted,
                                        maxWidth: action.featured ? 640 : 320,
                                    }}
                                >
                                    {action.subtitle}
                                </p>
                            </div>
                        </button>
                    ))}
                </div>

                <p
                    style={{
                        marginTop: 28,
                        textAlign: 'center',
                        fontSize: 13,
                        color: C.faint,
                    }}
                >
                    Powered by Kleopatra · Pyramid venue intelligence · Real
                    availability data
                </p>
            </div>
        </div>
    );
}

/* Kleopatra — the agent's avatar, one image per conversation state. */
const KLEOPATRA = {
    hello: '/assets/kleopatra-hello.png?v=2', // greeting / speaking
    thinking: '/assets/kleopatra-thinking.png?v=2', // deciding what to say
    listening: '/assets/kleopatra-listening.png?v=2', // waiting for you to finish
};

function kleopatraSrc(phase: ConvoPhase): string {
    if (phase === 'listening') return KLEOPATRA.listening;
    if (phase === 'thinking') return KLEOPATRA.thinking;

    return KLEOPATRA.hello; // speaking, idle, error
}

function kleopatraFrameStyle(size: number): CSSProperties {
    return {
        position: 'relative',
        width: size,
        height: size,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'transparent',
        borderRadius: 0,
        boxShadow: 'none',
    };
}

function kleopatraImageStyle(size: number): CSSProperties {
    return {
        width: size,
        height: size,
        objectFit: 'contain',
        background: 'transparent',
        border: 'none',
        borderRadius: 0,
        boxShadow: 'none',
    };
}

/** Kleopatra portrait used wherever the agent is represented. */
function Orb({ size = 92, phase }: { size?: number; phase?: ConvoPhase }) {
    return (
        <div style={kleopatraFrameStyle(size)}>
            <img
                src={kleopatraSrc(phase ?? 'idle')}
                alt="Kleopatra"
                style={kleopatraImageStyle(size)}
            />
        </div>
    );
}

/* ============================ VOICE ============================ */

type ConvoPhase = 'idle' | 'listening' | 'thinking' | 'speaking' | 'error';

type VoiceTurn = { role: 'user' | 'assistant'; content: string };

type EventDetails = {
    title?: string;
    event_type?: string;
    description?: string;
    attendees?: number;
    preferred_start_at?: string;
    preferred_end_at?: string;
    venue?: {
        name?: string;
        room_code?: string;
        box_ref?: string | null;
        floor?: number;
        capacity?: number;
        functional_type?: string;
        confidence?: number;
        location_geometry?: { x: number; y: number; level?: number } | null;
    } | null;
    pricing?: {
        price_per_sqm?: number;
        total?: number;
        sample_size?: number;
        basis?: string;
        agreed?: boolean;
        suggested_total?: number;
    } | null;
    reason?: 'ok' | 'over_capacity' | 'all_booked';
    max_capacity?: number | null;
};

function VoiceMode({ onExit }: { onExit: () => void }) {
    const [phase, setPhase] = useState<ConvoPhase>('idle');
    const [turns, setTurns] = useState<VoiceTurn[]>([]);
    const [error, setError] = useState('');
    const [review, setReview] = useState<EventDetails | null>(null);
    const [submitted, setSubmitted] = useState<{
        id: string;
        status: string;
        price?: number | null;
    } | null>(null);
    const [ended, setEnded] = useState(false);
    const [reviewOpen, setReviewOpen] = useState(false);

    const activeRef = useRef(false);
    const pendingEndRef = useRef(false);
    // Which agent owns the conversation: starts with the advisor, flips to the
    // booking (intake) agent once the visitor wants to organize for real.
    const agentModeRef = useRef<'advisor' | 'intake'>('advisor');
    const streamRef = useRef<MediaStream | null>(null);
    const audioCtxRef = useRef<AudioContext | null>(null);
    const analyserRef = useRef<AnalyserNode | null>(null);
    const recorderRef = useRef<MediaRecorder | null>(null);
    const chunksRef = useRef<Blob[]>([]);
    const vadRef = useRef<ReturnType<typeof setInterval> | null>(null);
    const sourceRef = useRef<AudioBufferSourceNode | null>(null);
    const fallbackAudioRef = useRef<HTMLAudioElement | null>(null);
    const historyRef = useRef<VoiceTurn[]>([]);
    const mimeRef = useRef<{ type: string; ext: string }>({
        type: '',
        ext: 'webm',
    });
    const endingRef = useRef(false);
    const transcriptRef = useRef<HTMLDivElement | null>(null);

    const pickMime = (): { type: string; ext: string } => {
        const candidates = [
            { type: 'audio/webm;codecs=opus', ext: 'webm' },
            { type: 'audio/webm', ext: 'webm' },
            { type: 'audio/mp4', ext: 'mp4' },
            { type: 'audio/ogg;codecs=opus', ext: 'ogg' },
        ];
        for (const candidate of candidates) {
            if (
                typeof MediaRecorder !== 'undefined' &&
                MediaRecorder.isTypeSupported(candidate.type)
            ) {
                return candidate;
            }
        }
        return { type: '', ext: 'webm' };
    };

    const level = (): number => {
        const analyser = analyserRef.current;
        if (!analyser) return 0;
        const buffer = new Uint8Array(analyser.fftSize);
        analyser.getByteTimeDomainData(buffer);
        let sum = 0;
        for (let i = 0; i < buffer.length; i++) {
            const value = (buffer[i] - 128) / 128;
            sum += value * value;
        }
        return Math.sqrt(sum / buffer.length);
    };

    const setMicEnabled = (enabled: boolean) => {
        streamRef.current?.getAudioTracks().forEach((track) => {
            track.enabled = enabled;
        });
    };

    const clearVad = () => {
        if (vadRef.current) {
            clearInterval(vadRef.current);
            vadRef.current = null;
        }
    };

    const endUtterance = () => {
        if (endingRef.current) return;
        endingRef.current = true;
        clearVad();
        const recorder = recorderRef.current;
        if (recorder && recorder.state !== 'inactive') recorder.stop();
    };

    const resumeListening = () => {
        if (activeRef.current) startListening();
    };

    const startListening = () => {
        if (!activeRef.current) return;
        endingRef.current = false;
        chunksRef.current = [];
        setMicEnabled(true);
        setPhase('listening');

        const recorder = new MediaRecorder(
            streamRef.current as MediaStream,
            mimeRef.current.type ? { mimeType: mimeRef.current.type } : undefined,
        );
        recorderRef.current = recorder;
        recorder.ondataavailable = (event) => {
            if (event.data.size > 0) chunksRef.current.push(event.data);
        };
        recorder.onstop = () => {
            const blob = new Blob(chunksRef.current, {
                type: mimeRef.current.type || 'audio/webm',
            });
            void handleUtterance(blob);
        };
        recorder.start();

        // Wait this long after you stop talking before the agent replies, so
        // you have room to pause and breathe mid-thought.
        const SILENCE_MS = 4500;
        const MAX_MS = 30000;
        const THRESHOLD = 0.02;
        const turnStart = performance.now();
        let lastVoice = turnStart;
        let voiceFrames = 0;
        let speechStarted = false;

        vadRef.current = setInterval(() => {
            if (!activeRef.current) return;
            const now = performance.now();
            if (level() > THRESHOLD) {
                voiceFrames++;
                lastVoice = now;
                if (voiceFrames >= 3) speechStarted = true;
            } else {
                voiceFrames = 0;
            }
            if (speechStarted && now - lastVoice >= SILENCE_MS) {
                endUtterance();
            } else if (speechStarted && now - turnStart >= MAX_MS) {
                endUtterance();
            }
        }, 60);
    };

    const transcribe = async (blob: Blob): Promise<string> => {
        const form = new FormData();
        form.append('audio', blob, `speech.${mimeRef.current.ext}`);
        // Pin the language so Whisper doesn't mis-detect short clips as
        // Arabic/another language and "transcribe" English into the wrong script.
        form.append('language', 'en');
        const response = await postForm('/speech/transcribe', form);
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Transcription failed.');
        }
        return String(data.text || '');
    };

    const agentTurn = (history: VoiceTurn[]): Promise<AgentResult> =>
        runPlannerAgent(history, agentModeRef);

    // After the agent finishes speaking, either hang up (if the agent chose to
    // end the call) or go back to listening.
    const afterSpeak = () => {
        if (pendingEndRef.current) {
            finishCall();
        } else {
            resumeListening();
        }
    };

    const speak = async (text: string): Promise<void> => {
        if (!activeRef.current) return;
        setPhase('speaking');
        try {
            const response = await postJson('/speech/speak', {
                text: text.slice(0, 4000),
            });
            if (!response.ok) {
                afterSpeak();
                return;
            }

            const bytes = await response.arrayBuffer();
            const ctx = audioCtxRef.current;

            // Play through the AudioContext the user's click already unlocked.
            // It's exempt from the HTMLAudioElement autoplay block that was
            // silently swallowing playback after the STT/LLM round-trip.
            if (ctx) {
                try {
                    if (ctx.state === 'suspended') await ctx.resume();
                    const buffer = await ctx.decodeAudioData(bytes.slice(0));
                    const source = ctx.createBufferSource();
                    source.buffer = buffer;
                    source.connect(ctx.destination);
                    source.onended = () => afterSpeak();
                    sourceRef.current = source;
                    source.start();
                    return;
                } catch {
                    // fall through to the HTMLAudio fallback
                }
            }

            const url = URL.createObjectURL(
                new Blob([bytes], { type: 'audio/mpeg' }),
            );
            const audio = new Audio(url);
            fallbackAudioRef.current = audio;
            audio.onended = () => {
                URL.revokeObjectURL(url);
                afterSpeak();
            };
            audio.onerror = () => {
                URL.revokeObjectURL(url);
                afterSpeak();
            };
            await audio.play().catch(() => afterSpeak());
        } catch {
            afterSpeak();
        }
    };

    const handleUtterance = async (blob: Blob) => {
        if (!activeRef.current) return;
        setMicEnabled(false);
        setPhase('thinking');

        let text = '';
        try {
            text = (await transcribe(blob)).trim();
        } catch (e) {
            setError(e instanceof Error ? e.message : 'Transcription failed.');
            resumeListening();
            return;
        }

        if (text === '') {
            resumeListening();
            return;
        }
        setError('');

        const withUser: VoiceTurn[] = [
            ...historyRef.current,
            { role: 'user', content: text },
        ];
        historyRef.current = withUser;
        setTurns(withUser);

        let reply = '';
        try {
            const result = await agentTurn(withUser);
            reply = result.reply.trim();
            if (result.submitted) {
                setSubmitted(result.submitted);
                setReview(null);
                setReviewOpen(false);
            } else if (result.review) {
                setReview(result.review);
                setReviewOpen(true);
            }
            // The agent chose to hang up — end the call after this farewell.
            if (result.ended) {
                pendingEndRef.current = true;
            }
        } catch {
            reply = '';
        }
        reply =
            reply ||
            'Sorry, I had trouble responding. Could you say that again?';

        const withReply: VoiceTurn[] = [
            ...historyRef.current,
            { role: 'assistant', content: reply },
        ];
        historyRef.current = withReply;
        setTurns(withReply);

        await speak(reply);
    };

    const begin = async () => {
        setError('');
        setTurns([]);
        setReview(null);
        setReviewOpen(false);
        setSubmitted(null);
        setEnded(false);
        pendingEndRef.current = false;
        historyRef.current = [];
        mimeRef.current = pickMime();
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                },
            });
            streamRef.current = stream;
            const context = new AudioContext();
            audioCtxRef.current = context;
            if (context.state === 'suspended') {
                await context.resume();
            }
            const source = context.createMediaStreamSource(stream);
            const analyser = context.createAnalyser();
            analyser.fftSize = 1024;
            source.connect(analyser);
            analyserRef.current = analyser;
            activeRef.current = true;
            startListening();
        } catch {
            setError(
                'Microphone access was blocked. Allow mic permission and try again.',
            );
            setPhase('error');
        }
    };

    const end = () => {
        activeRef.current = false;
        clearVad();
        const recorder = recorderRef.current;
        if (recorder && recorder.state !== 'inactive') {
            try {
                recorder.stop();
            } catch {
                // already stopped
            }
        }
        if (sourceRef.current) {
            try {
                sourceRef.current.onended = null;
                sourceRef.current.stop();
            } catch {
                // already stopped
            }
            sourceRef.current = null;
        }
        fallbackAudioRef.current?.pause();
        streamRef.current?.getTracks().forEach((track) => track.stop());
        void audioCtxRef.current?.close().catch(() => {});
        streamRef.current = null;
        audioCtxRef.current = null;
        analyserRef.current = null;
        setPhase('idle');
    };

    // The agent hung up: tear everything down and show the wrap-up screen.
    const finishCall = () => {
        pendingEndRef.current = false;
        end();
        setEnded(true);
    };

    useEffect(() => {
        // Preload Kleopatra's three faces so switching states is instant.
        Object.values(KLEOPATRA).forEach((src) => {
            const img = new Image();
            img.src = src;
        });
    }, []);

    useEffect(() => {
        // Don't auto-start — the user taps the mic to begin. Just clean up.
        return () => end();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        transcriptRef.current?.scrollTo({
            top: transcriptRef.current.scrollHeight,
            behavior: 'smooth',
        });
    }, [turns, phase]);

    if (ended) {
        return (
            <div
                style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: 18,
                    padding: 24,
                }}
            >
                <span
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        width: 84,
                        height: 84,
                        borderRadius: '50%',
                        background: C.greenTint,
                        color: C.green,
                    }}
                >
                    <Check size={38} />
                </span>
                <div style={{ textAlign: 'center' }}>
                    <p style={{ fontSize: 19, fontWeight: 700 }}>
                        The planner wrapped up the call
                    </p>
                    <p
                        style={{
                            marginTop: 6,
                            fontSize: 14.5,
                            color: C.muted,
                            maxWidth: 360,
                        }}
                    >
                        Everything's been taken care of. You can start a new
                        conversation whenever you like.
                    </p>
                </div>
                <button
                    type="button"
                    onClick={onExit}
                    style={primaryButton()}
                >
                    Back to start
                </button>
            </div>
        );
    }

    if (phase === 'error' && !activeRef.current) {
        return (
            <div
                style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: 18,
                    padding: 24,
                }}
            >
                <p
                    style={{
                        maxWidth: 420,
                        textAlign: 'center',
                        color: C.muted,
                    }}
                >
                    {error}
                </p>
                <button
                    type="button"
                    onClick={() => void begin()}
                    style={primaryButton()}
                >
                    <Mic size={16} />
                    Try again
                </button>
            </div>
        );
    }

    if (!activeRef.current && phase === 'idle') {
        return (
            <div
                style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: 20,
                    padding: 24,
                }}
            >
                <button
                    type="button"
                    onClick={() => void begin()}
                    aria-label="Start the conversation"
                    style={{
                        position: 'relative',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        width: 120,
                        height: 120,
                        padding: 0,
                        border: 'none',
                        cursor: 'pointer',
                        background: 'transparent',
                        borderRadius: 0,
                        boxShadow: 'none',
                    }}
                >
                    <img
                        src={KLEOPATRA.hello}
                        alt="Kleopatra"
                        style={kleopatraImageStyle(120)}
                    />
                    <span
                        style={{
                            position: 'absolute',
                            right: 4,
                            bottom: 4,
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 36,
                            height: 36,
                            borderRadius: '50%',
                            background: C.green,
                            color: '#fff',
                            border: '3px solid #fff',
                        }}
                    >
                        <Mic size={16} />
                    </span>
                </button>
                <div style={{ textAlign: 'center' }}>
                    <p style={{ fontSize: 19, fontWeight: 700 }}>
                        Ready when you are
                    </p>
                    <p
                        style={{
                            marginTop: 6,
                            fontSize: 14.5,
                            color: C.muted,
                            maxWidth: 340,
                        }}
                    >
                        Tap the microphone and tell me about the event you'd like
                        to organize.
                    </p>
                </div>
            </div>
        );
    }

    const statusLabel =
        phase === 'listening'
            ? "Listening — I'm all ears"
            : phase === 'thinking'
              ? 'Thinking…'
              : phase === 'speaking'
                ? 'Planner is speaking'
                : 'Connecting…';

    return (
        <div
            style={{
                flex: 1,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                minHeight: 0,
                padding: '8px 24px 24px',
            }}
        >
            <div
                style={{
                    position: 'relative',
                    width: 132,
                    height: 132,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    marginTop: 8,
                }}
            >
                {phase === 'listening' &&
                    [0, 0.6, 1.2].map((delay) => (
                        <span
                            key={delay}
                            style={{
                                position: 'absolute',
                                inset: 14,
                                borderRadius: '50%',
                                background: 'rgba(16,130,91,0.3)',
                                animation: `pl-pulse 2.2s ease-out ${delay}s infinite`,
                            }}
                        />
                    ))}
                {phase === 'thinking' && <span className="pl-ring" />}
                <span
                    className={
                        phase === 'speaking' || phase === 'thinking'
                            ? 'pl-breathe'
                            : undefined
                    }
                    style={kleopatraFrameStyle(104)}
                >
                    {phase === 'listening' && <span className="pl-mic-ping" />}
                    <img
                        src={kleopatraSrc(phase)}
                        alt="Kleopatra"
                        style={kleopatraImageStyle(104)}
                    />
                </span>
            </div>

            {(phase === 'listening' || phase === 'speaking') && (
                <SoundBars />
            )}

            {phase === 'thinking' ? (
                <div
                    style={{
                        marginTop: 18,
                        height: 24,
                        display: 'flex',
                        alignItems: 'center',
                        gap: 6,
                    }}
                >
                    {[0, 0.18, 0.36].map((d) => (
                        <span
                            key={d}
                            style={{
                                width: 9,
                                height: 9,
                                borderRadius: '50%',
                                background: C.green,
                                animation: `pl-blink 1s ${d}s infinite`,
                            }}
                        />
                    ))}
                </div>
            ) : (
                <p style={{ marginTop: 18, fontSize: 17, fontWeight: 600 }}>
                    {statusLabel}
                </p>
            )}
            <p
                style={{
                    marginTop: 6,
                    fontSize: 13,
                    color: C.faint,
                    minHeight: 18,
                }}
            >
                {phase === 'speaking'
                    ? 'Mic muted while I speak'
                    : phase === 'listening'
                      ? 'Just talk — I’ll reply when you pause'
                      : phase === 'thinking'
                        ? 'Putting your reply together'
                        : ' '}
            </p>
            {error !== '' && (
                <p style={{ marginTop: 4, fontSize: 13, color: C.danger }}>
                    {error}
                </p>
            )}

            {submitted ? (
                <div
                    style={{
                        width: '100%',
                        maxWidth: 620,
                        marginTop: 16,
                        display: 'flex',
                        alignItems: 'center',
                        gap: 12,
                        padding: '16px 18px',
                        borderRadius: 16,
                        border: `1px solid ${C.greenTint}`,
                        background: 'rgba(16,130,91,0.06)',
                    }}
                >
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 36,
                            height: 36,
                            borderRadius: '50%',
                            background: C.green,
                            color: '#fff',
                            flex: 'none',
                        }}
                    >
                        <Check size={20} />
                    </span>
                    <div>
                        <p style={{ fontWeight: 700 }}>
                            Event request submitted
                            {submitted.price != null
                                ? ` · €${Math.round(submitted.price).toLocaleString()}`
                                : ''}
                        </p>
                        <p style={{ fontSize: 13, color: C.muted }}>
                            Reference {submitted.id}
                        </p>
                    </div>
                </div>
            ) : null}

            {reviewOpen && review && !submitted && (
                <ReviewModal
                    review={review}
                    onClose={() => setReviewOpen(false)}
                />
            )}

            <div
                ref={transcriptRef}
                style={{
                    width: '100%',
                    maxWidth: 620,
                    flex: 1,
                    minHeight: 0,
                    overflowY: 'auto',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 10,
                    margin: '18px 0',
                }}
            >
                {turns.length === 0 && (
                    <p
                        style={{
                            margin: 'auto',
                            color: C.faint,
                            fontSize: 14,
                        }}
                    >
                        Say hello to get started…
                    </p>
                )}
                {turns.map((turn, index) => (
                    <div
                        key={index}
                        style={{
                            display: 'flex',
                            justifyContent:
                                turn.role === 'user' ? 'flex-end' : 'flex-start',
                        }}
                    >
                        <div
                            style={{
                                maxWidth: '84%',
                                padding: '10px 14px',
                                borderRadius: 14,
                                fontSize: 14.5,
                                lineHeight: 1.5,
                                whiteSpace: 'pre-wrap',
                                background:
                                    turn.role === 'user' ? C.greenTint : C.card,
                                border:
                                    turn.role === 'user'
                                        ? 'none'
                                        : `1px solid ${C.border}`,
                                color: C.ink,
                            }}
                        >
                            {turn.content}
                        </div>
                    </div>
                ))}
            </div>

            <button
                type="button"
                onClick={() => {
                    end();
                    onExit();
                }}
                style={{
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: 8,
                    padding: '11px 22px',
                    borderRadius: 999,
                    border: `1px solid ${C.border}`,
                    background: C.card,
                    color: C.danger,
                    fontSize: 14.5,
                    fontWeight: 600,
                    cursor: 'pointer',
                }}
            >
                <X size={17} />
                End conversation
            </button>
        </div>
    );
}

/* ============================ CHAT ============================ */

type ChatMessage = {
    role: 'user' | 'assistant';
    content: string;
    tools?: string[];
};

function ChatMode({
    initialInput = '',
    initialMessage,
    initialMode = 'advisor',
}: {
    initialInput?: string;
    initialMessage?: string;
    initialMode?: AgentMode;
}) {
    const [messages, setMessages] = useState<ChatMessage[]>([]);
    const [input, setInput] = useState(initialInput);
    const [sending, setSending] = useState(false);
    const [speakingIndex, setSpeakingIndex] = useState<number | null>(null);
    const [review, setReview] = useState<EventDetails | null>(null);
    const [reviewOpen, setReviewOpen] = useState(false);
    const [submitted, setSubmitted] = useState<{
        id: string;
        status: string;
        price?: number | null;
    } | null>(null);
    const scrollRef = useRef<HTMLDivElement | null>(null);
    // Same agent state as the voice flow: starts with the advisor, hands over
    // to the booking (intake) agent when the visitor wants to organize.
    const agentModeRef = useRef<AgentMode>(initialMode);
    const seededRef = useRef(false);

    useEffect(() => {
        scrollRef.current?.scrollTo({
            top: scrollRef.current.scrollHeight,
            behavior: 'smooth',
        });
    }, [messages, sending]);

    // Run one turn of the SAME planner agent the voice mode uses.
    const turn = async (next: ChatMessage[]) => {
        setMessages(next);
        setSending(true);
        try {
            const result = await runPlannerAgent(next, agentModeRef);
            if (result.submitted) {
                setSubmitted(result.submitted);
                setReview(null);
                setReviewOpen(false);
            } else if (result.review) {
                setReview(result.review);
                setReviewOpen(true);
            }
            setMessages((current) => [
                ...current,
                {
                    role: 'assistant',
                    content:
                        result.reply ||
                        'Sorry, I had trouble responding. Could you try again?',
                },
            ]);
        } catch {
            setMessages((current) => [
                ...current,
                { role: 'assistant', content: 'Network error. Please try again.' },
            ]);
        } finally {
            setSending(false);
        }
    };

    const send = async () => {
        const text = input.trim();
        if (text === '' || sending) {
            return;
        }
        setInput('');
        await turn([...messages, { role: 'user', content: text }]);
    };

    // Document flow: auto-send the extracted brief so the agent reads it and
    // starts asking for whatever is still missing — reusing the same loop.
    useEffect(() => {
        if (initialMessage && !seededRef.current) {
            seededRef.current = true;
            void turn([{ role: 'user', content: initialMessage }]);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [initialMessage]);

    const speak = async (text: string, index: number) => {
        try {
            setSpeakingIndex(index);
            const response = await postJson('/speech/speak', { text });

            if (!response.ok) {
                setSpeakingIndex(null);

                return;
            }

            const blob = await response.blob();
            const audio = new Audio(URL.createObjectURL(blob));
            audio.onended = () => setSpeakingIndex(null);
            void audio.play();
        } catch {
            setSpeakingIndex(null);
        }
    };

    return (
        <div
            style={{
                flex: 1,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                minHeight: 0,
                padding: '12px 0 0',
            }}
        >
            <div
                ref={scrollRef}
                style={{
                    flex: 1,
                    width: '100%',
                    maxWidth: 720,
                    overflowY: 'auto',
                    padding: '12px 24px',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 14,
                }}
            >
                {messages.length === 0 && (
                    <div
                        style={{
                            margin: 'auto',
                            textAlign: 'center',
                            color: C.muted,
                        }}
                    >
                        <Orb size={60} />
                        <p style={{ marginTop: 16, fontSize: 15.5 }}>
                            Ask the planner anything about your event.
                        </p>
                    </div>
                )}

                {messages.map((message, index) => (
                    <div
                        key={index}
                        style={{
                            display: 'flex',
                            justifyContent:
                                message.role === 'user'
                                    ? 'flex-end'
                                    : 'flex-start',
                        }}
                    >
                        <div
                            style={{
                                maxWidth: '82%',
                                padding: '12px 15px',
                                borderRadius: 16,
                                fontSize: 15,
                                lineHeight: 1.55,
                                whiteSpace: 'pre-wrap',
                                background:
                                    message.role === 'user'
                                        ? C.greenTint
                                        : C.card,
                                border:
                                    message.role === 'user'
                                        ? 'none'
                                        : `1px solid ${C.border}`,
                                color: C.ink,
                                borderBottomRightRadius:
                                    message.role === 'user' ? 4 : 16,
                                borderBottomLeftRadius:
                                    message.role === 'assistant' ? 4 : 16,
                            }}
                        >
                            {message.content}
                            {message.role === 'assistant' && (
                                <div
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        flexWrap: 'wrap',
                                        gap: 6,
                                        marginTop: 8,
                                    }}
                                >
                                    <button
                                        type="button"
                                        className="pl-ghost"
                                        onClick={() =>
                                            void speak(message.content, index)
                                        }
                                        title="Read aloud"
                                        style={{
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            gap: 5,
                                            padding: '4px 8px',
                                            borderRadius: 7,
                                            border: 'none',
                                            background: 'transparent',
                                            color: C.muted,
                                            fontSize: 12.5,
                                            cursor: 'pointer',
                                        }}
                                    >
                                        {speakingIndex === index ? (
                                            <Loader2
                                                className="pl-spin"
                                                size={13}
                                            />
                                        ) : (
                                            <Volume2 size={13} />
                                        )}
                                        Listen
                                    </button>
                                    {message.tools?.map((tool) => (
                                        <span
                                            key={tool}
                                            style={{
                                                fontSize: 11,
                                                fontWeight: 600,
                                                color: C.green,
                                                background: C.greenTint,
                                                padding: '2px 8px',
                                                borderRadius: 999,
                                            }}
                                        >
                                            {tool}
                                        </span>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                ))}

                {sending && (
                    <div style={{ display: 'flex', gap: 5, padding: '4px 2px' }}>
                        {[0, 0.2, 0.4].map((delay) => (
                            <span
                                key={delay}
                                style={{
                                    width: 7,
                                    height: 7,
                                    borderRadius: '50%',
                                    background: C.faint,
                                    animation: `pl-blink 1.2s ${delay}s infinite`,
                                }}
                            />
                        ))}
                    </div>
                )}
            </div>

            {/* Composer */}
            <div
                style={{
                    width: '100%',
                    maxWidth: 720,
                    padding: '12px 24px 22px',
                }}
            >
                <div
                    className="pl-composer"
                    style={{
                        display: 'flex',
                        alignItems: 'flex-end',
                        gap: 10,
                        background: C.card,
                        border: `1px solid ${C.border}`,
                        borderRadius: 16,
                        padding: '10px 10px 10px 16px',
                        boxShadow: '0 14px 34px -26px rgba(26,26,26,0.3)',
                    }}
                >
                    <textarea
                        className="pl-input"
                        rows={1}
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter' && !e.shiftKey) {
                                e.preventDefault();
                                void send();
                            }
                        }}
                        placeholder="Message the planner…"
                        style={{
                            fontSize: 15,
                            lineHeight: 1.5,
                            padding: '6px 0',
                            maxHeight: 140,
                        }}
                    />
                    <button
                        type="button"
                        className="pl-send"
                        disabled={input.trim() === '' || sending}
                        onClick={() => void send()}
                        style={{
                            flex: 'none',
                            display: 'inline-flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 38,
                            height: 38,
                            borderRadius: 11,
                            border: 'none',
                            background: C.green,
                            color: '#fff',
                            cursor: 'pointer',
                        }}
                    >
                        <ArrowUp size={18} />
                    </button>
                </div>
                {submitted && (
                    <div
                        style={{
                            marginTop: 10,
                            padding: '10px 14px',
                            borderRadius: 12,
                            background: 'rgba(16,130,91,0.08)',
                            color: C.green,
                            fontSize: 13.5,
                            fontWeight: 600,
                            textAlign: 'center',
                        }}
                    >
                        Your event request was submitted. We'll be in touch!
                    </div>
                )}
            </div>

            {reviewOpen && review && !submitted && (
                <ReviewModal
                    review={review}
                    onClose={() => setReviewOpen(false)}
                />
            )}
        </div>
    );
}

/* ============================ UPLOAD ============================ */

function UploadMode({
    onExtracted,
}: {
    onExtracted: (text: string) => void;
}) {
    const [file, setFile] = useState<File | null>(null);
    const [over, setOver] = useState(false);
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState('');
    const inputRef = useRef<HTMLInputElement | null>(null);

    // Read the document's text, then hand it straight to the same planner agent
    // (in the chat) so it can work from the brief and ask for anything missing.
    const upload = async () => {
        if (!file || uploading) {
            return;
        }

        setUploading(true);
        setError('');

        const isPdf =
            file.type === 'application/pdf' ||
            file.name.toLowerCase().endsWith('.pdf');
        const form = new FormData();
        form.append(isPdf ? 'document' : 'image', file);

        try {
            const response = await postForm(
                isPdf ? '/ocr/document' : '/ocr',
                form,
            );
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Could not read the document.');
            }
            const text = String(data.response || '').trim();
            if (text === '') {
                throw new Error("We couldn't find any text in that file.");
            }
            onExtracted(
                `Here is my event brief — please read it and set up the event:\n\n${text}`,
            );
        } catch (e) {
            setError(
                e instanceof Error ? e.message : 'Could not read the document.',
            );
            setUploading(false);
        }
    };

    return (
        <div
            style={{
                flex: 1,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '24px',
            }}
        >
            <div style={{ width: '100%', maxWidth: 560 }}>
                <h2
                    style={{
                        fontSize: 22,
                        fontWeight: 700,
                        textAlign: 'center',
                        marginBottom: 6,
                    }}
                >
                    Upload an event brief
                </h2>
                <p
                    style={{
                        textAlign: 'center',
                        color: C.muted,
                        marginBottom: 22,
                    }}
                >
                    We'll read it so the planner can work from your document.
                </p>

                <div
                    className={`pl-drop${over ? ' is-over' : ''}`}
                    onClick={() => inputRef.current?.click()}
                    onDragOver={(e) => {
                        e.preventDefault();
                        setOver(true);
                    }}
                    onDragLeave={() => setOver(false)}
                    onDrop={(e) => {
                        e.preventDefault();
                        setOver(false);
                        const dropped = e.dataTransfer.files?.[0];
                        if (dropped) {
                            setFile(dropped);
                        }
                    }}
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        gap: 12,
                        padding: '44px 24px',
                        borderRadius: 16,
                        border: `2px dashed ${C.border}`,
                        background: C.card,
                        cursor: 'pointer',
                        textAlign: 'center',
                    }}
                >
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 52,
                            height: 52,
                            borderRadius: 14,
                            background: C.greenTint,
                            color: C.green,
                        }}
                    >
                        <UploadCloud size={24} />
                    </span>
                    <span style={{ fontSize: 15.5, fontWeight: 600 }}>
                        Drop a file here, or click to browse
                    </span>
                    <span style={{ fontSize: 13, color: C.faint }}>
                        PDF or image · up to 20MB
                    </span>
                    <input
                        ref={inputRef}
                        type="file"
                        accept=".pdf,image/*"
                        hidden
                        onChange={(e) =>
                            setFile(e.target.files?.[0] ?? null)
                        }
                    />
                </div>

                {file && (
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 12,
                            marginTop: 16,
                            padding: '12px 14px',
                            borderRadius: 12,
                            border: `1px solid ${C.border}`,
                            background: C.card,
                        }}
                    >
                        <FileText size={20} color={C.green} />
                        <span style={{ flex: 1, minWidth: 0 }}>
                            <span
                                style={{
                                    display: 'block',
                                    fontSize: 14.5,
                                    fontWeight: 500,
                                    overflow: 'hidden',
                                    textOverflow: 'ellipsis',
                                    whiteSpace: 'nowrap',
                                }}
                            >
                                {file.name}
                            </span>
                            <span style={{ fontSize: 12.5, color: C.faint }}>
                                {(file.size / 1024 / 1024).toFixed(2)} MB
                            </span>
                        </span>
                        <button
                            type="button"
                            className="pl-ghost"
                            onClick={() => setFile(null)}
                            style={{
                                display: 'flex',
                                padding: 6,
                                borderRadius: 8,
                                border: 'none',
                                background: 'transparent',
                                color: C.muted,
                                cursor: 'pointer',
                            }}
                        >
                            <X size={16} />
                        </button>
                    </div>
                )}

                <button
                    type="button"
                    className="pl-send"
                    disabled={!file || uploading}
                    onClick={() => void upload()}
                    style={{
                        ...primaryButton(!file || uploading),
                        width: '100%',
                        marginTop: 18,
                        justifyContent: 'center',
                    }}
                >
                    {uploading ? (
                        <Loader2 className="pl-spin" size={17} />
                    ) : (
                        <Upload size={17} />
                    )}
                    {uploading ? 'Reading…' : 'Read & start planning'}
                </button>

                {error && (
                    <div
                        style={{
                            marginTop: 12,
                            fontSize: 13,
                            color: C.danger,
                            textAlign: 'center',
                        }}
                    >
                        {error}
                    </div>
                )}
            </div>
        </div>
    );
}

/* ============================ shared button styles ============================ */

function SoundBars() {
    const bars = [0, 1, 2, 3, 4, 5, 6];

    return (
        <div
            style={{
                marginTop: 16,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: 5,
                height: 28,
            }}
        >
            {bars.map((i) => (
                <span
                    key={i}
                    style={{
                        width: 4,
                        height: 28,
                        borderRadius: 2,
                        background: C.green,
                        transformOrigin: 'center',
                        animation: `pl-bar ${0.7 + (i % 3) * 0.18}s ease-in-out ${i * 0.11}s infinite`,
                    }}
                />
            ))}
        </div>
    );
}

function ReviewModal({
    review,
    onClose,
}: {
    review: EventDetails;
    onClose: () => void;
}) {
    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', onKey);

        return () => window.removeEventListener('keydown', onKey);
    }, [onClose]);

    const hasVenue = !!review.venue;

    const items: { icon: LucideIcon; label: string; value: string }[] = [
        {
            icon: Tag,
            label: 'Event type',
            value: formatEventType(review.event_type) ?? '—',
        },
        {
            icon: CalendarClock,
            label: 'Starts',
            value: formatDateTime(review.preferred_start_at) ?? '—',
        },
        {
            icon: Clock,
            label: 'Ends',
            value: formatDateTime(review.preferred_end_at) ?? '—',
        },
        {
            icon: Users,
            label: 'Attendees',
            value:
                review.attendees != null
                    ? `${review.attendees} people`
                    : '—',
        },
    ];

    if (review.venue) {
        items.push({
            icon: MapPin,
            label: 'Venue',
            value: `${review.venue.name}${
                review.venue.capacity ? ` · holds ${review.venue.capacity}` : ''
            }${
                review.venue.confidence != null
                    ? ` · ${review.venue.confidence}% match`
                    : ''
            }`,
        });
    }
    if (review.pricing) {
        const agreed = review.pricing.agreed === true;
        const wasPrice =
            agreed && review.pricing.suggested_total != null
                ? ` · was €${Math.round(review.pricing.suggested_total).toLocaleString()}`
                : '';
        items.push({
            icon: Euro,
            label: agreed ? 'Agreed price' : 'Suggested price',
            value: `€${Math.round(review.pricing.total ?? 0).toLocaleString()}${
                review.pricing.price_per_sqm != null
                    ? ` · €${review.pricing.price_per_sqm}/m²`
                    : ''
            }${wasPrice}`,
        });
    }
    if (review.description) {
        items.push({
            icon: FileText,
            label: 'About',
            value: review.description,
        });
    }

    return (
        <div
            className="pl-modal-bg"
            onClick={onClose}
            style={{
                position: 'fixed',
                inset: 0,
                zIndex: 50,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: 20,
                background: 'rgba(18,20,18,0.55)',
                backdropFilter: 'blur(3px)',
            }}
        >
            <div
                className="pl-modal-card"
                onClick={(e) => e.stopPropagation()}
                style={{
                    width: '100%',
                    maxWidth: 460,
                    maxHeight: '86vh',
                    overflowY: 'auto',
                    background: C.card,
                    borderRadius: 24,
                    boxShadow: '0 40px 90px -30px rgba(10,20,15,0.6)',
                    padding: '26px 26px 22px',
                }}
            >
                {/* Header */}
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
                        <p
                            style={{
                                fontSize: 11.5,
                                fontWeight: 700,
                                letterSpacing: '0.08em',
                                textTransform: 'uppercase',
                                color: C.green,
                                marginBottom: 6,
                            }}
                        >
                            Review your event request
                        </p>
                        <h2
                            style={{
                                fontSize: 23,
                                fontWeight: 800,
                                letterSpacing: '-0.02em',
                                lineHeight: 1.15,
                            }}
                        >
                            {review.title || 'Your event'}
                        </h2>
                    </div>
                    <button
                        type="button"
                        className="pl-ghost"
                        onClick={onClose}
                        aria-label="Back to the agent"
                        style={{
                            flex: 'none',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 34,
                            height: 34,
                            borderRadius: 10,
                            border: `1px solid ${C.border}`,
                            background: C.card,
                            color: C.muted,
                            cursor: 'pointer',
                        }}
                    >
                        <X size={17} />
                    </button>
                </div>

                {/* Timeline */}
                <div>
                    {items.map((item, index) => {
                        const last = index === items.length - 1;

                        return (
                            <div
                                key={item.label}
                                style={{ display: 'flex', gap: 14 }}
                            >
                                <div
                                    style={{
                                        display: 'flex',
                                        flexDirection: 'column',
                                        alignItems: 'center',
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
                                            borderRadius: 11,
                                            background: C.greenTint,
                                            color: C.green,
                                        }}
                                    >
                                        <item.icon size={18} />
                                    </span>
                                    {!last && (
                                        <span
                                            style={{
                                                flex: 1,
                                                width: 2,
                                                background: C.borderSoft,
                                                marginTop: 4,
                                                marginBottom: 4,
                                            }}
                                        />
                                    )}
                                </div>
                                <div
                                    style={{
                                        paddingBottom: last ? 0 : 18,
                                        paddingTop: 3,
                                        minWidth: 0,
                                    }}
                                >
                                    <div
                                        style={{
                                            fontSize: 11.5,
                                            fontWeight: 600,
                                            letterSpacing: '0.04em',
                                            textTransform: 'uppercase',
                                            color: C.faint,
                                        }}
                                    >
                                        {item.label}
                                    </div>
                                    <div
                                        style={{
                                            fontSize: 15.5,
                                            fontWeight: 500,
                                            color: C.ink,
                                            marginTop: 2,
                                            lineHeight: 1.45,
                                        }}
                                    >
                                        {item.value}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Where the venue sits in the Pyramid */}
                {review.venue?.location_geometry && (
                    <div style={{ marginTop: 18 }}>
                        <div
                            style={{
                                fontSize: 11.5,
                                fontWeight: 600,
                                letterSpacing: '0.04em',
                                textTransform: 'uppercase',
                                color: C.faint,
                                marginBottom: 8,
                            }}
                        >
                            Where it is in the Pyramid
                            {review.venue.location_geometry.level
                                ? ` · floor ${review.venue.location_geometry.level}`
                                : ''}
                        </div>
                        <PyramidMap
                            src={`/assets/pyramid-plan-${review.venue.location_geometry.level ?? 1}.png`}
                            pins={[
                                {
                                    id: 'venue',
                                    x: review.venue.location_geometry.x,
                                    y: review.venue.location_geometry.y,
                                    label:
                                        review.venue.box_ref ??
                                        review.venue.name,
                                    tone: 'highlight',
                                },
                            ]}
                            style={{
                                border: `1px solid ${C.border}`,
                                borderRadius: 14,
                                padding: 6,
                                background: C.card,
                            }}
                        />
                    </div>
                )}

                {/* Footer */}
                {hasVenue ? (
                    <div
                        style={{
                            marginTop: 8,
                            padding: '12px 14px',
                            borderRadius: 12,
                            background: 'rgba(16,130,91,0.07)',
                            fontSize: 13,
                            fontWeight: 600,
                            color: C.green,
                            textAlign: 'center',
                        }}
                    >
                        Say “send the event request” to confirm, or tell the
                        planner what to change.
                    </div>
                ) : (
                    <div
                        style={{
                            marginTop: 8,
                            display: 'flex',
                            gap: 10,
                            padding: '12px 14px',
                            borderRadius: 12,
                            background:
                                review.reason === 'over_capacity'
                                    ? 'rgba(180,69,58,0.08)'
                                    : 'rgba(138,109,28,0.1)',
                            color:
                                review.reason === 'over_capacity'
                                    ? C.danger
                                    : '#8A6D1C',
                        }}
                    >
                        <TriangleAlert size={17} style={{ flex: 'none' }} />
                        <span style={{ fontSize: 13, lineHeight: 1.5 }}>
                            {review.reason === 'over_capacity'
                                ? `No single space fits ${review.attendees} people${
                                      review.max_capacity
                                          ? ` — the largest holds about ${review.max_capacity}`
                                          : ''
                                  }. Try a smaller guest count, or split it across days.`
                                : 'Every suitable space is booked at that time. Try a different day or time.'}
                        </span>
                    </div>
                )}

                <button
                    type="button"
                    className="pl-ghost"
                    onClick={onClose}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        gap: 8,
                        width: '100%',
                        marginTop: 16,
                        padding: '12px 20px',
                        borderRadius: 12,
                        border: `1px solid ${C.border}`,
                        background: C.card,
                        color: C.ink,
                        fontSize: 15,
                        fontWeight: 600,
                        cursor: 'pointer',
                    }}
                >
                    <ArrowLeft size={16} />
                    Back to the agent
                </button>
            </div>
        </div>
    );
}

function DetailRow({ label, value }: { label: string; value?: string }) {
    if (!value) {
        return null;
    }

    return (
        <div style={{ display: 'flex', gap: 12, padding: '5px 0', fontSize: 14 }}>
            <span style={{ width: 84, flex: 'none', color: C.faint }}>
                {label}
            </span>
            <span style={{ color: C.ink }}>{value}</span>
        </div>
    );
}

function formatEventType(value?: string): string | undefined {
    if (!value) {
        return undefined;
    }

    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function formatDateTime(value?: string): string | undefined {
    if (!value) {
        return undefined;
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function primaryButton(disabled = false): React.CSSProperties {
    return {
        display: 'inline-flex',
        alignItems: 'center',
        gap: 8,
        padding: '12px 22px',
        borderRadius: 11,
        border: 'none',
        background: C.green,
        color: '#fff',
        fontSize: 15,
        fontWeight: 600,
        cursor: disabled ? 'not-allowed' : 'pointer',
        opacity: disabled ? 0.45 : 1,
    };
}
