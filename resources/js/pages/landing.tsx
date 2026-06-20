import { Head } from '@inertiajs/react';
import { useEffect, type CSSProperties } from 'react';
import BrandLogo from '@/components/brand-logo';

type Props = {
    heroOverlay?: 'soft' | 'medium' | 'strong';
};

const HERO_IMAGE = '/assets/pyramid.webp';
const CARD_IMAGE = '/assets/pyramid-card.webp';

const steps = [
    {
        num: '01',
        tag: 'Profile',
        title: 'List your organization',
        body: 'Create a profile for your company or organization in minutes — your team, your mission, the kind of events you run.',
        accent: '#10825B',
    },
    {
        num: '02',
        tag: 'Intelligence',
        title: 'Get bid recommendations',
        body: 'Our agents suggest strong bids drawn from applications that have actually been approved before — no more guesswork.',
        accent: '#2A6F44',
    },
    {
        num: '03',
        tag: 'Confidence',
        title: 'Submit with a rating',
        body: 'Before you apply, agents score how likely your bid is to pass with the tenants, so you only send applications worth sending.',
        accent: '#8A6D1C',
    },
];

const css = `
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:#F4F3EE;color:#1A1A1A;-webkit-font-smoothing:antialiased}
::selection{background:#10825B;color:#fff}
@keyframes riseIn{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}

/* Fancy CTA */
.cta-btn{position:relative;display:inline-flex;align-items:center;gap:13px;padding:18px 44px;border-radius:999px;background:#F4F3EE;color:#10231A;font-family:'Hanken Grotesk',sans-serif;font-weight:700;font-size:15px;letter-spacing:0.14em;text-transform:uppercase;text-decoration:none;overflow:hidden;isolation:isolate;box-shadow:0 12px 34px -12px rgba(0,0,0,0.55);transition:color .45s ease,transform .4s cubic-bezier(.2,.8,.2,1),box-shadow .4s ease}
.cta-btn::before{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(120deg,#10825B 0%,#2A6F44 100%);transform:translateX(-101%);transition:transform .5s cubic-bezier(.16,.84,.32,1)}
.cta-btn::after{content:"";position:absolute;left:50%;top:50%;width:0;height:0;z-index:-1;border-radius:999px;background:radial-gradient(circle,rgba(255,255,255,0.35),transparent 70%);transform:translate(-50%,-50%);transition:width .55s ease,height .55s ease}
.cta-btn:hover{color:#fff;transform:translateY(-4px);box-shadow:0 22px 48px -14px rgba(16,130,91,0.6)}
.cta-btn:hover::before{transform:translateX(0)}
.cta-btn:hover::after{width:240px;height:240px}
.cta-btn:active{transform:translateY(-1px) scale(0.99)}
.cta-btn .arrow{font-size:18px;line-height:1;transition:transform .4s cubic-bezier(.2,.8,.2,1)}
.cta-btn:hover .arrow{transform:translateX(6px)}

/* Secondary hero CTA (outline, no link yet) */
.cta-btn-secondary{position:relative;display:inline-flex;align-items:center;gap:10px;padding:18px 36px;border-radius:999px;background:transparent;color:#fff;font-family:'Hanken Grotesk',sans-serif;font-weight:700;font-size:15px;letter-spacing:0.14em;text-transform:uppercase;border:1.5px solid rgba(255,255,255,0.72);cursor:pointer;transition:color .35s ease,transform .4s cubic-bezier(.2,.8,.2,1),box-shadow .4s ease,border-color .35s ease,background .35s ease}
.cta-btn-secondary:hover{color:#10231A;background:#F4F3EE;border-color:#F4F3EE;transform:translateY(-4px);box-shadow:0 18px 40px -14px rgba(0,0,0,0.45)}
.cta-btn-secondary:active{transform:translateY(-1px) scale(0.99)}
.hero-cta-row{display:flex;flex-wrap:wrap;align-items:center;gap:14px}

/* Hover behaviours ported from style-hover */
.nav-link:hover{color:#fff}
.nav-link{white-space:nowrap}
.lp-nav-links{flex-shrink:0}
.lp-logo{flex-shrink:0}
.lp-logo span{white-space:nowrap}
/* Responsive navbar — stop the logo + links overlapping on small screens */
@media (max-width:680px){
  .lp-nav{padding:20px 22px !important;gap:14px}
  .lp-logo-sub{display:none !important}
  .lp-nav-links{gap:18px !important}
}
@media (max-width:430px){
  .lp-nav{flex-wrap:wrap;padding:16px 18px !important}
  .lp-logo-title{font-size:18px !important}
  .nav-link{font-size:13.5px !important}
}
.step-panel:hover{transform:translateY(-10px);box-shadow:0 32px 64px -28px rgba(16,130,91,0.28)}
.step-panel:hover .step-panel-arrow{transform:translateX(5px);opacity:1}
.step-row:hover{padding-left:14px}
.footer-cta:hover{transform:translateY(-2px);box-shadow:0 14px 30px -12px rgba(0,0,0,0.55);background:#fff}
.footer-link:hover{color:#fff}

/* How-it-works journey */
.steps-journey{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:clamp(18px,2.5vw,28px);position:relative;align-items:stretch}
.steps-journey::before{content:"";position:absolute;top:58px;left:12%;right:12%;height:2px;background:linear-gradient(90deg,transparent 0%,#C9C3B4 18%,#10825B 50%,#C9C3B4 82%,transparent 100%);opacity:.55;pointer-events:none;z-index:0}
.step-panel{position:relative;display:flex;flex-direction:column;min-height:100%;padding:38px 30px 34px;border-radius:26px;background:linear-gradient(165deg,#fff 0%,#F9F8F4 55%,#F2F0E8 100%);border:1px solid rgba(255,255,255,0.95);box-shadow:0 10px 40px -22px rgba(26,26,26,0.18),inset 0 1px 0 rgba(255,255,255,0.85);overflow:hidden;transition:transform .45s cubic-bezier(.2,.8,.2,1),box-shadow .45s ease;z-index:1}
.step-panel-accent{position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,var(--step-accent,#10825B),color-mix(in srgb,var(--step-accent,#10825B) 40%,transparent))}
.step-panel-num{font-size:clamp(52px,7vw,68px);font-weight:800;line-height:1;letter-spacing:-0.05em;color:color-mix(in srgb,var(--step-accent,#10825B) 14%,transparent);margin-bottom:18px}
.step-panel-tag{display:inline-flex;align-items:center;width:fit-content;padding:6px 12px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--step-accent,#10825B);background:color-mix(in srgb,var(--step-accent,#10825B) 10%,#fff);border:1px solid color-mix(in srgb,var(--step-accent,#10825B) 18%,transparent);margin-bottom:16px}
.step-panel-title{font-size:clamp(20px,2.2vw,24px);font-weight:800;letter-spacing:-0.02em;color:#1A1A1A;margin-bottom:12px;line-height:1.15}
.step-panel-body{font-size:15.5px;line-height:1.65;color:#5C5A54;text-wrap:pretty;flex:1}
.step-panel-foot{display:flex;align-items:center;justify-content:space-between;margin-top:26px;padding-top:18px;border-top:1px solid rgba(201,195,180,0.55)}
.step-panel-index{font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#9A958A}
.step-panel-arrow{font-size:18px;line-height:1;color:var(--step-accent,#10825B);opacity:.55;transition:transform .35s cubic-bezier(.2,.8,.2,1),opacity .35s ease}
@media(max-width:960px){.steps-journey{grid-template-columns:1fr;max-width:520px;margin:0 auto}.steps-journey::before{display:none}}

/* Scroll reveal (progressive enhancement: hidden only once JS confirms it runs) */
html.js-reveal .reveal{opacity:0;transform:translateY(32px)}
html.js-reveal .reveal.is-visible{opacity:1;transform:none}
@media (prefers-reduced-motion:reduce){html.js-reveal .reveal{opacity:1;transform:none}}
`;

export default function Landing({
    heroOverlay = 'medium',
}: Props) {
    useEffect(() => {
        if (!('IntersectionObserver' in window)) {
            return; /* leave content visible */
        }

        document.documentElement.classList.add('js-reveal');
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) {
                        const d = parseInt(
                            e.target.getAttribute('data-reveal') || '0',
                            10,
                        );
                        setTimeout(() => {
                            e.target.classList.add('is-visible');
                        }, d * 150);
                        io.unobserve(e.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -10% 0px' },
        );
        document.querySelectorAll('.reveal').forEach((el) => io.observe(el));

        return () => {
            io.disconnect();
            document.documentElement.classList.remove('js-reveal');
        };
    }, []);

    return (
        <>
            <Head title="Piramida Spaces">
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
                <link rel="preload" as="image" href={HERO_IMAGE} />
            </Head>
            <style dangerouslySetInnerHTML={{ __html: css }} />

            <div style={{ width: '100%', overflowX: 'hidden' }}>
                {/* ===== HERO ===== */}
                <section
                    style={{
                        position: 'relative',
                        width: '100%',
                        height: '100vh',
                        minHeight: '680px',
                        overflow: 'hidden',
                        background: '#0a0e12',
                    }}
                >
                    <div
                        style={{
                            position: 'absolute',
                            inset: 0,
                            backgroundImage: `url('${HERO_IMAGE}')`,
                            backgroundSize: 'cover',
                            backgroundPosition: 'center 38%',
                            transform: 'scale(1.03)',
                        }}
                    />

                    {heroOverlay === 'soft' && (
                        <div
                            style={{
                                position: 'absolute',
                                inset: 0,
                                background:
                                    'radial-gradient(125% 95% at 50% 26%, rgba(0,0,0,0) 46%, rgba(8,10,12,0.30) 100%), linear-gradient(to bottom, rgba(8,12,16,0.30) 0%, rgba(8,12,16,0) 20%, rgba(8,12,16,0) 56%, rgba(10,14,18,0.42) 82%, rgba(6,8,10,0.80) 100%)',
                            }}
                        />
                    )}
                    {heroOverlay === 'medium' && (
                        <div
                            style={{
                                position: 'absolute',
                                inset: 0,
                                background:
                                    'radial-gradient(122% 92% at 50% 28%, rgba(0,0,0,0) 40%, rgba(8,10,12,0.40) 100%), linear-gradient(to bottom, rgba(8,12,16,0.42) 0%, rgba(8,12,16,0) 22%, rgba(8,12,16,0) 50%, rgba(10,14,18,0.56) 78%, rgba(6,8,10,0.92) 100%)',
                            }}
                        />
                    )}
                    {heroOverlay === 'strong' && (
                        <div
                            style={{
                                position: 'absolute',
                                inset: 0,
                                background:
                                    'radial-gradient(118% 90% at 50% 30%, rgba(0,0,0,0) 34%, rgba(6,8,10,0.52) 100%), linear-gradient(to bottom, rgba(6,10,14,0.55) 0%, rgba(8,12,16,0.05) 24%, rgba(8,12,16,0.06) 46%, rgba(8,12,16,0.68) 74%, rgba(4,6,8,0.97) 100%)',
                            }}
                        />
                    )}

                    <nav
                        className="lp-nav"
                        style={{
                            position: 'absolute',
                            top: 0,
                            left: 0,
                            right: 0,
                            zIndex: 10,
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'space-between',
                            padding: '30px clamp(24px,6vw,80px)',
                            animation: 'fadeIn 0.9s ease both',
                        }}
                    >
                        <a
                            href="#top"
                            className="lp-logo"
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                textDecoration: 'none',
                            }}
                        >
                            <BrandLogo height={44} />
                        </a>
                        <div
                            className="lp-nav-links"
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 'clamp(20px,3vw,40px)',
                            }}
                        >
                            <a
                                href="#how"
                                className="nav-link"
                                style={{
                                    fontSize: '15px',
                                    fontWeight: 500,
                                    color: 'rgba(255,255,255,0.82)',
                                    textDecoration: 'none',
                                    transition: 'color .25s ease',
                                }}
                            >
                                How it works
                            </a>
                            <a
                                href="#tenants"
                                className="nav-link"
                                style={{
                                    fontSize: '15px',
                                    fontWeight: 500,
                                    color: 'rgba(255,255,255,0.82)',
                                    textDecoration: 'none',
                                    transition: 'color .25s ease',
                                }}
                            >
                                Tenants
                            </a>
                        </div>
                    </nav>

                    <div
                        style={{
                            position: 'absolute',
                            left: 0,
                            right: 0,
                            top: 0,
                            bottom: 0,
                            zIndex: 5,
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                            justifyContent: 'center',
                            textAlign: 'center',
                            padding: 'clamp(96px,15vh,150px) 24px 0',
                        }}
                    >
                        <h1
                            style={{
                                fontSize: 'clamp(40px,6.6vw,86px)',
                                lineHeight: 1.0,
                                fontWeight: 800,
                                letterSpacing: '-0.028em',
                                color: '#fff',
                                maxWidth: '15ch',
                                marginBottom: '24px',
                                textWrap: 'balance',
                                textShadow:
                                    '0 2px 30px rgba(4,8,10,0.55),0 1px 4px rgba(4,8,10,0.5)',
                                animation: 'riseIn 0.85s ease 0.12s both',
                            }}
                        >
                            Book the right space. Win the bid.
                        </h1>
                        <p
                            style={{
                                fontSize: 'clamp(16px,1.5vw,20px)',
                                lineHeight: 1.55,
                                fontWeight: 400,
                                color: 'rgba(255,255,255,0.92)',
                                maxWidth: '52ch',
                                marginBottom: '40px',
                                textWrap: 'pretty',
                                textShadow: '0 1px 18px rgba(4,8,10,0.6)',
                                animation: 'riseIn 0.85s ease 0.26s both',
                            }}
                        >
                            From finding the right floor to submitting a bid the
                            tenants will approve — we guide every step, so
                            securing your event date is finally simple.
                        </p>
                        <div
                            className="hero-cta-row"
                            style={{ animation: 'riseIn 0.85s ease 0.4s both' }}
                        >
                            <a href="#how" className="cta-btn">
                                Start planning
                                <span className="arrow">→</span>
                            </a>
                            <button type="button" className="cta-btn-secondary">
                                See Demo
                            </button>
                        </div>
                    </div>
                </section>

                {/* ===== HOW IT WORKS ===== */}
                <section
                    id="how"
                    style={{
                        position: 'relative',
                        padding: 'clamp(80px,12vh,140px) clamp(24px,6vw,80px)',
                        background:
                            'linear-gradient(180deg, #F4F3EE 0%, #EDEAE2 100%)',
                        overflow: 'hidden',
                    }}
                >
                    <div
                        aria-hidden
                        style={{
                            position: 'absolute',
                            top: '-120px',
                            right: '-80px',
                            width: '420px',
                            height: '420px',
                            borderRadius: '50%',
                            background:
                                'radial-gradient(circle, rgba(16,130,91,0.08) 0%, transparent 70%)',
                            pointerEvents: 'none',
                        }}
                    />
                    <div
                        aria-hidden
                        style={{
                            position: 'absolute',
                            bottom: '-140px',
                            left: '-100px',
                            width: '360px',
                            height: '360px',
                            borderRadius: '50%',
                            background:
                                'radial-gradient(circle, rgba(138,109,28,0.07) 0%, transparent 70%)',
                            pointerEvents: 'none',
                        }}
                    />

                    <div
                        style={{
                            position: 'relative',
                            maxWidth: '1140px',
                            margin: '0 auto',
                        }}
                    >
                        <div
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                alignItems: 'flex-end',
                                justifyContent: 'space-between',
                                gap: '24px',
                                marginBottom: 'clamp(48px,7vh,76px)',
                            }}
                        >
                            <div style={{ maxWidth: '620px' }}>
                                <span
                                    style={{
                                        display: 'inline-block',
                                        fontSize: '13px',
                                        fontWeight: 600,
                                        letterSpacing: '0.16em',
                                        textTransform: 'uppercase',
                                        color: '#10825B',
                                        marginBottom: '16px',
                                    }}
                                >
                                    How it works
                                </span>
                                <h2
                                    style={{
                                        fontSize: 'clamp(30px,3.8vw,48px)',
                                        lineHeight: 1.06,
                                        fontWeight: 800,
                                        letterSpacing: '-0.025em',
                                        color: '#1A1A1A',
                                        marginBottom: '18px',
                                        textWrap: 'balance',
                                    }}
                                >
                                    Three moves.
                                    <br />
                                    <span style={{ color: '#10825B' }}>
                                        One path to approval.
                                    </span>
                                </h2>
                            </div>
                            <p
                                style={{
                                    fontSize: '16px',
                                    lineHeight: 1.65,
                                    color: '#6E6E6E',
                                    textWrap: 'pretty',
                                    maxWidth: '34ch',
                                }}
                            >
                                Floor plans, tenant fit, bid quality — sorted
                                before you hit submit. No more long back-and-forth.
                            </p>
                        </div>

                        <div className="steps-journey">
                            {steps.map((step, index) => (
                                <article
                                    key={step.num}
                                    className="reveal step-panel"
                                    data-reveal={index}
                                    style={
                                        {
                                            '--step-accent': step.accent,
                                        } as CSSProperties
                                    }
                                >
                                    <div className="step-panel-accent" />
                                    <div className="step-panel-num">
                                        {step.num}
                                    </div>
                                    <span className="step-panel-tag">
                                        {step.tag}
                                    </span>
                                    <h3 className="step-panel-title">
                                        {step.title}
                                    </h3>
                                    <p className="step-panel-body">
                                        {step.body}
                                    </p>
                                    <div className="step-panel-foot">
                                        <span className="step-panel-index">
                                            Step {step.num}
                                        </span>
                                        <span
                                            className="step-panel-arrow"
                                            aria-hidden
                                        >
                                            →
                                        </span>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ===== TENANTS / SERVICES ===== */}
                <section
                    id="tenants"
                    style={{
                        padding: 'clamp(72px,10vh,120px) clamp(24px,6vw,80px)',
                        background: '#EAE7DC',
                        borderTop: '1px solid #E0DCD3',
                        borderBottom: '1px solid #E0DCD3',
                    }}
                >
                    <div style={{ maxWidth: '1140px', margin: '0 auto' }}>
                        <div
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                alignItems: 'flex-end',
                                justifyContent: 'space-between',
                                gap: '20px',
                                marginBottom: 'clamp(36px,5vh,52px)',
                            }}
                        >
                            <div style={{ maxWidth: '560px' }}>
                                <span
                                    style={{
                                        display: 'inline-block',
                                        fontSize: '13px',
                                        fontWeight: 600,
                                        letterSpacing: '0.16em',
                                        textTransform: 'uppercase',
                                        color: '#8A6D1C',
                                        marginBottom: '14px',
                                    }}
                                >
                                    The tenants
                                </span>
                                <h2
                                    style={{
                                        fontSize: 'clamp(26px,3.2vw,40px)',
                                        lineHeight: 1.1,
                                        fontWeight: 800,
                                        letterSpacing: '-0.02em',
                                        color: '#1A1A1A',
                                        textWrap: 'balance',
                                    }}
                                >
                                    Who you're planning your event with
                                </h2>
                            </div>
                            <p
                                style={{
                                    fontSize: '15.5px',
                                    lineHeight: 1.6,
                                    color: '#6E6E6E',
                                    maxWidth: '36ch',
                                }}
                            >
                                The three programs that share the Pyramid. We
                                tailor your bid to their aims.
                            </p>
                        </div>

                        <div
                            style={{
                                display: 'flex',
                                flexDirection: 'column',
                                gap: 'clamp(56px,9vh,112px)',
                                marginTop: 'clamp(44px,7vh,76px)',
                            }}
                        >
                            {/* Tenant 1: TUMO — text left, image right */}
                            <div
                                className="reveal"
                                style={{
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    alignItems: 'center',
                                    gap: 'clamp(32px,5vw,72px)',
                                    transition:
                                        'opacity .8s ease,transform .8s cubic-bezier(.2,.8,.2,1)',
                                }}
                            >
                                <div
                                    style={{
                                        flex: '1 1 330px',
                                        minWidth: '280px',
                                    }}
                                >
                                    <div
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: '13px',
                                            marginBottom: '20px',
                                        }}
                                    >
                                        <span
                                            style={{
                                                fontSize: '14px',
                                                fontWeight: 800,
                                                letterSpacing: '0.08em',
                                                color: '#10825B',
                                            }}
                                        >
                                            01
                                        </span>
                                        <span
                                            style={{
                                                height: '1px',
                                                width: '36px',
                                                background: '#C9C3B4',
                                            }}
                                        />
                                        <span
                                            style={{
                                                fontSize: '12px',
                                                fontWeight: 600,
                                                letterSpacing: '0.18em',
                                                textTransform: 'uppercase',
                                                color: '#6E6E6E',
                                            }}
                                        >
                                            Education · Learning
                                        </span>
                                    </div>
                                    <h3
                                        style={{
                                            fontSize: 'clamp(25px,3vw,34px)',
                                            fontWeight: 800,
                                            letterSpacing: '-0.015em',
                                            color: '#1A1A1A',
                                            marginBottom: '18px',
                                        }}
                                    >
                                        TUMO Tirana
                                    </h3>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            marginBottom: '14px',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        In Albania, this innovative center is
                                        an initiative of the Albanian-American
                                        Development Foundation (AADF) in
                                        partnership with the Municipality of
                                        Tirana. Students have access to a modern
                                        space with learning labs, auditoriums,
                                        music studios, and many open study
                                        environments.
                                    </p>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            marginBottom: '14px',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        TUMO is a non-profit organization
                                        founded by the Simonian Foundation for
                                        Education, first opened in Yerevan in
                                        2011. Its unique philosophy has brought
                                        it to Paris, Beirut, Berlin, Moscow,
                                        and now Tirana.
                                    </p>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        Students learn independently through the
                                        TUMO Path platform, building a personal
                                        portfolio in programming, animation, game
                                        development, graphic design, filmmaking,
                                        robotics, music, and 3D modeling.
                                    </p>
                                </div>
                                <div
                                    style={{
                                        flex: '1 1 330px',
                                        minWidth: '280px',
                                        borderRadius: '22px',
                                        overflow: 'hidden',
                                        boxShadow:
                                            '0 26px 56px -28px rgba(26,26,26,0.4)',
                                        aspectRatio: '4/3',
                                        position: 'relative',
                                    }}
                                >
                                    <img
                                        src={CARD_IMAGE}
                                        alt=""
                                        loading="lazy"
                                        decoding="async"
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            width: '100%',
                                            height: '100%',
                                            objectFit: 'cover',
                                        }}
                                    />
                                </div>
                            </div>

                            {/* Tenant 2: ICT — image left, text right */}
                            <div
                                className="reveal"
                                style={{
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    alignItems: 'center',
                                    gap: 'clamp(32px,5vw,72px)',
                                    flexDirection: 'row-reverse',
                                    transition:
                                        'opacity .8s ease,transform .8s cubic-bezier(.2,.8,.2,1)',
                                }}
                            >
                                <div
                                    style={{
                                        flex: '1 1 330px',
                                        minWidth: '280px',
                                    }}
                                >
                                    <div
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: '13px',
                                            marginBottom: '20px',
                                        }}
                                    >
                                        <span
                                            style={{
                                                fontSize: '14px',
                                                fontWeight: 800,
                                                letterSpacing: '0.08em',
                                                color: '#2A6F44',
                                            }}
                                        >
                                            02
                                        </span>
                                        <span
                                            style={{
                                                height: '1px',
                                                width: '36px',
                                                background: '#C9C3B4',
                                            }}
                                        />
                                        <span
                                            style={{
                                                fontSize: '12px',
                                                fontWeight: 600,
                                                letterSpacing: '0.18em',
                                                textTransform: 'uppercase',
                                                color: '#6E6E6E',
                                            }}
                                        >
                                            Technology · Innovation
                                        </span>
                                    </div>
                                    <h3
                                        style={{
                                            fontSize: 'clamp(25px,3vw,34px)',
                                            fontWeight: 800,
                                            letterSpacing: '-0.015em',
                                            color: '#1A1A1A',
                                            marginBottom: '18px',
                                        }}
                                    >
                                        ICT Ecosystem
                                    </h3>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            marginBottom: '14px',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        Albania's ICT space has seen significant
                                        growth in recent years, with educational
                                        programs, communities, and support
                                        networks. Entrepreneurs and startups are
                                        driving innovation to meet global and
                                        local needs.
                                    </p>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            marginBottom: '14px',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        The Pyramid of Tirana will play a central
                                        role in fostering this ecosystem — a hub
                                        for exchanging ideas, an inclusive public
                                        forum open 24/7 with a wide range of
                                        functions and events.
                                    </p>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        Its transformation into an open ICT
                                        ecosystem shows how a building can adapt
                                        to the needs of a new era while
                                        preserving its history.
                                    </p>
                                </div>
                                <div
                                    style={{
                                        flex: '1 1 330px',
                                        minWidth: '280px',
                                        borderRadius: '22px',
                                        overflow: 'hidden',
                                        boxShadow:
                                            '0 26px 56px -28px rgba(26,26,26,0.4)',
                                        aspectRatio: '4/3',
                                        position: 'relative',
                                    }}
                                >
                                    <img
                                        src={CARD_IMAGE}
                                        alt=""
                                        loading="lazy"
                                        decoding="async"
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            width: '100%',
                                            height: '100%',
                                            objectFit: 'cover',
                                        }}
                                    />
                                </div>
                            </div>

                            {/* Tenant 3: ARTS — text left, image right */}
                            <div
                                className="reveal"
                                style={{
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    alignItems: 'center',
                                    gap: 'clamp(32px,5vw,72px)',
                                    transition:
                                        'opacity .8s ease,transform .8s cubic-bezier(.2,.8,.2,1)',
                                }}
                            >
                                <div
                                    style={{
                                        flex: '1 1 330px',
                                        minWidth: '280px',
                                    }}
                                >
                                    <div
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: '13px',
                                            marginBottom: '20px',
                                        }}
                                    >
                                        <span
                                            style={{
                                                fontSize: '14px',
                                                fontWeight: 800,
                                                letterSpacing: '0.08em',
                                                color: '#8A6D1C',
                                            }}
                                        >
                                            03
                                        </span>
                                        <span
                                            style={{
                                                height: '1px',
                                                width: '36px',
                                                background: '#C9C3B4',
                                            }}
                                        />
                                        <span
                                            style={{
                                                fontSize: '12px',
                                                fontWeight: 600,
                                                letterSpacing: '0.18em',
                                                textTransform: 'uppercase',
                                                color: '#6E6E6E',
                                            }}
                                        >
                                            Culture · Art
                                        </span>
                                    </div>
                                    <h3
                                        style={{
                                            fontSize: 'clamp(25px,3vw,34px)',
                                            fontWeight: 800,
                                            letterSpacing: '-0.015em',
                                            color: '#1A1A1A',
                                            marginBottom: '18px',
                                        }}
                                    >
                                        Arts
                                    </h3>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            marginBottom: '14px',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        Art plays a key role in fostering
                                        creativity and inspiring new
                                        perspectives. The opportunity to share
                                        and exhibit work with the public is
                                        essential for artists to realize their
                                        vision.
                                    </p>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            marginBottom: '14px',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        At the Pyramid of Tirana, we are
                                        dedicated to cultivating the arts in
                                        Albania. With a wide range of
                                        exhibitions, events, and programs, it
                                        will serve as a meeting place for
                                        artists and the public.
                                    </p>
                                    <p
                                        style={{
                                            fontSize: '15.5px',
                                            lineHeight: 1.72,
                                            color: '#54514A',
                                            textWrap: 'pretty',
                                            maxWidth: '58ch',
                                        }}
                                    >
                                        Whether you are an artist or simply a
                                        lover of art, the Pyramid of Tirana is
                                        the perfect place to be inspired, talk,
                                        and create.
                                    </p>
                                </div>
                                <div
                                    style={{
                                        flex: '1 1 330px',
                                        minWidth: '280px',
                                        borderRadius: '22px',
                                        overflow: 'hidden',
                                        boxShadow:
                                            '0 26px 56px -28px rgba(26,26,26,0.4)',
                                        aspectRatio: '4/3',
                                        position: 'relative',
                                    }}
                                >
                                    <img
                                        src={CARD_IMAGE}
                                        alt=""
                                        loading="lazy"
                                        decoding="async"
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            width: '100%',
                                            height: '100%',
                                            objectFit: 'cover',
                                        }}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* ===== FOOTER ===== */}
                <footer
                    style={{
                        background:
                            'linear-gradient(180deg,#143729 0%,#0E251B 100%)',
                        color: '#fff',
                        padding:
                            'clamp(60px,9vh,92px) clamp(24px,6vw,80px) 40px',
                    }}
                >
                    <div style={{ maxWidth: '1140px', margin: '0 auto' }}>
                        <div
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                justifyContent: 'space-between',
                                gap: '48px',
                                paddingBottom: '52px',
                                borderBottom:
                                    '1px solid rgba(255,255,255,0.12)',
                            }}
                        >
                            <div style={{ maxWidth: '340px' }}>
                                <BrandLogo
                                    height={42}
                                    style={{ marginBottom: 18 }}
                                />
                                <p
                                    style={{
                                        fontSize: '15px',
                                        lineHeight: 1.65,
                                        color: 'rgba(255,255,255,0.6)',
                                        textWrap: 'pretty',
                                        marginBottom: '26px',
                                    }}
                                >
                                    A simpler way to book spaces, build winning
                                    bids and secure your event date at the
                                    Pyramid of Tirana.
                                </p>
                                <a
                                    href="#how"
                                    className="footer-cta"
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: '10px',
                                        padding: '13px 28px',
                                        background: '#F4F3EE',
                                        color: '#10231A',
                                        fontSize: '14px',
                                        fontWeight: 700,
                                        letterSpacing: '0.04em',
                                        borderRadius: '999px',
                                        textDecoration: 'none',
                                        transition:
                                            'transform .25s ease,box-shadow .25s ease,background .25s ease',
                                    }}
                                >
                                    Start planning{' '}
                                    <span
                                        style={{ transform: 'translateY(1px)' }}
                                    >
                                        →
                                    </span>
                                </a>
                            </div>
                            <div
                                style={{
                                    display: 'flex',
                                    gap: 'clamp(40px,8vw,96px)',
                                    flexWrap: 'wrap',
                                }}
                            >
                                <div
                                    style={{
                                        display: 'flex',
                                        flexDirection: 'column',
                                        gap: '14px',
                                    }}
                                >
                                    <span
                                        style={{
                                            fontSize: '12px',
                                            fontWeight: 600,
                                            letterSpacing: '0.14em',
                                            textTransform: 'uppercase',
                                            color: 'rgba(255,255,255,0.4)',
                                            marginBottom: '4px',
                                        }}
                                    >
                                        Platform
                                    </span>
                                    <a
                                        href="#how"
                                        className="footer-link"
                                        style={{
                                            fontSize: '15px',
                                            color: 'rgba(255,255,255,0.74)',
                                            textDecoration: 'none',
                                            transition: 'color .2s ease',
                                        }}
                                    >
                                        How it works
                                    </a>
                                    <a
                                        href="#tenants"
                                        className="footer-link"
                                        style={{
                                            fontSize: '15px',
                                            color: 'rgba(255,255,255,0.74)',
                                            textDecoration: 'none',
                                            transition: 'color .2s ease',
                                        }}
                                    >
                                        Tenants
                                    </a>
                                    <a
                                        href="#how"
                                        className="footer-link"
                                        style={{
                                            fontSize: '15px',
                                            color: 'rgba(255,255,255,0.74)',
                                            textDecoration: 'none',
                                            transition: 'color .2s ease',
                                        }}
                                    >
                                        Start planning
                                    </a>
                                </div>
                                <div
                                    style={{
                                        display: 'flex',
                                        flexDirection: 'column',
                                        gap: '14px',
                                    }}
                                >
                                    <span
                                        style={{
                                            fontSize: '12px',
                                            fontWeight: 600,
                                            letterSpacing: '0.14em',
                                            textTransform: 'uppercase',
                                            color: 'rgba(255,255,255,0.4)',
                                            marginBottom: '4px',
                                        }}
                                    >
                                        Pyramid
                                    </span>
                                    <a
                                        href="#tenants"
                                        className="footer-link"
                                        style={{
                                            fontSize: '15px',
                                            color: 'rgba(255,255,255,0.74)',
                                            textDecoration: 'none',
                                            transition: 'color .2s ease',
                                        }}
                                    >
                                        TUMO Tirana
                                    </a>
                                    <a
                                        href="#tenants"
                                        className="footer-link"
                                        style={{
                                            fontSize: '15px',
                                            color: 'rgba(255,255,255,0.74)',
                                            textDecoration: 'none',
                                            transition: 'color .2s ease',
                                        }}
                                    >
                                        ICT Ecosystem
                                    </a>
                                    <a
                                        href="#tenants"
                                        className="footer-link"
                                        style={{
                                            fontSize: '15px',
                                            color: 'rgba(255,255,255,0.74)',
                                            textDecoration: 'none',
                                            transition: 'color .2s ease',
                                        }}
                                    >
                                        ARTS
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                justifyContent: 'space-between',
                                gap: '12px',
                                paddingTop: '28px',
                            }}
                        >
                            <span
                                style={{
                                    fontSize: '13.5px',
                                    color: 'rgba(255,255,255,0.45)',
                                }}
                            >
                                © 2026 Piramida Spaces · Tirana, Albania
                            </span>
                            <span
                                style={{
                                    fontSize: '13.5px',
                                    color: 'rgba(255,255,255,0.45)',
                                }}
                            >
                                Built for the Pyramid of Tirana
                            </span>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
