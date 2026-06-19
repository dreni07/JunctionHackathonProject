import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

type Props = {
    heroOverlay?: 'soft' | 'medium' | 'strong';
    stepStyle?: 'cards' | 'minimal';
};

const steps = [
    {
        num: '1',
        title: 'List your organization',
        body: 'Create a profile for your company or organization in minutes â your team, your mission, the kind of events you run.',
    },
    {
        num: '2',
        title: 'Get bid recommendations',
        body: 'Our agents suggest strong bids drawn from applications that have actually been approved before â no more guesswork.',
    },
    {
        num: '3',
        title: 'Submit with a rating',
        body: 'Before you apply, agents score how likely your bid is to pass with the tenants, so you only send applications worth sending.',
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

/* Hover behaviours ported from style-hover */
.nav-link:hover{color:#fff}
.step-card:hover{transform:translateY(-6px);box-shadow:0 18px 44px -20px rgba(26,26,26,0.22);border-color:#D8E2DC}
.step-row:hover{padding-left:14px}
.footer-cta:hover{transform:translateY(-2px);box-shadow:0 14px 30px -12px rgba(0,0,0,0.55);background:#fff}
.footer-link:hover{color:#fff}

/* Scroll reveal (progressive enhancement: hidden only once JS confirms it runs) */
html.js-reveal .reveal{opacity:0;transform:translateY(32px)}
html.js-reveal .reveal.is-visible{opacity:1;transform:none}
@media (prefers-reduced-motion:reduce){html.js-reveal .reveal{opacity:1;transform:none}}
`;

export default function Landing({
    heroOverlay = 'medium',
    stepStyle = 'cards',
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
                            backgroundImage: "url('/assets/pyramid.png')",
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
                            style={{
                                display: 'flex',
                                alignItems: 'baseline',
                                gap: '10px',
                                textDecoration: 'none',
                            }}
                        >
                            <span
                                style={{
                                    fontSize: '21px',
                                    fontWeight: 800,
                                    letterSpacing: '0.16em',
                                    color: '#fff',
                                }}
                            >
                                PIRAMIDA
                            </span>
                            <span
                                style={{
                                    fontSize: '12px',
                                    fontWeight: 500,
                                    letterSpacing: '0.18em',
                                    color: 'rgba(255,255,255,0.62)',
                                    textTransform: 'uppercase',
                                }}
                            >
                                Spaces
                            </span>
                        </a>
                        <div
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
                            tenants will approve â we guide every step, so
                            securing your event date is finally simple.
                        </p>
                        <a
                            href="#how"
                            className="cta-btn"
                            style={{ animation: 'riseIn 0.85s ease 0.4s both' }}
                        >
                            Start planning
                            <span className="arrow">â</span>
                        </a>
                    </div>
                </section>

                {/* ===== HOW IT WORKS ===== */}
                <section
                    id="how"
                    style={{
                        padding: 'clamp(80px,12vh,140px) clamp(24px,6vw,80px)',
                        background: '#F4F3EE',
                    }}
                >
                    <div style={{ maxWidth: '1140px', margin: '0 auto' }}>
                        <div
                            style={{
                                textAlign: 'center',
                                maxWidth: '640px',
                                margin: '0 auto clamp(48px,7vh,76px)',
                            }}
                        >
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
                                    fontSize: 'clamp(28px,3.6vw,46px)',
                                    lineHeight: 1.08,
                                    fontWeight: 800,
                                    letterSpacing: '-0.02em',
                                    color: '#1A1A1A',
                                    marginBottom: '16px',
                                    textWrap: 'balance',
                                }}
                            >
                                Three steps, instead of a long, uncertain
                                process
                            </h2>
                            <p
                                style={{
                                    fontSize: '17px',
                                    lineHeight: 1.6,
                                    color: '#6E6E6E',
                                    textWrap: 'pretty',
                                }}
                            >
                                No more guessing at floor plans, square meters
                                and what the tenants want. We turn the
                                application into something you can actually win.
                            </p>
                        </div>

                        {stepStyle === 'cards' && (
                            <div
                                style={{
                                    display: 'grid',
                                    gridTemplateColumns:
                                        'repeat(auto-fit,minmax(280px,1fr))',
                                    gap: '24px',
                                }}
                            >
                                {steps.map((step, index) => (
                                    <div
                                        key={step.num}
                                        className="reveal step-card"
                                        data-reveal={index}
                                        style={{
                                            position: 'relative',
                                            background: '#fff',
                                            border: '1px solid #EAE7DC',
                                            borderRadius: '18px',
                                            padding: '34px 30px 36px',
                                            transition:
                                                'transform .5s cubic-bezier(.2,.8,.2,1),box-shadow .35s ease,border-color .35s ease,opacity .6s ease',
                                        }}
                                    >
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                width: '46px',
                                                height: '46px',
                                                borderRadius: '12px',
                                                background: '#D8E2DC',
                                                color: '#10825B',
                                                fontSize: '18px',
                                                fontWeight: 800,
                                                marginBottom: '22px',
                                            }}
                                        >
                                            {step.num}
                                        </div>
                                        <h3
                                            style={{
                                                fontSize: '21px',
                                                fontWeight: 700,
                                                letterSpacing: '-0.01em',
                                                color: '#1A1A1A',
                                                marginBottom: '11px',
                                            }}
                                        >
                                            {step.title}
                                        </h3>
                                        <p
                                            style={{
                                                fontSize: '15.5px',
                                                lineHeight: 1.62,
                                                color: '#6E6E6E',
                                                textWrap: 'pretty',
                                            }}
                                        >
                                            {step.body}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        )}
                        {stepStyle === 'minimal' && (
                            <div
                                style={{
                                    display: 'flex',
                                    flexDirection: 'column',
                                    maxWidth: '760px',
                                    margin: '0 auto',
                                }}
                            >
                                {steps.map((step, index) => (
                                    <div
                                        key={step.num}
                                        className="reveal step-row"
                                        data-reveal={index}
                                        style={{
                                            display: 'flex',
                                            gap: '26px',
                                            alignItems: 'flex-start',
                                            padding: '30px 4px',
                                            borderTop: '1px solid #E0DCD3',
                                            transition:
                                                'padding-left .3s cubic-bezier(.2,.8,.2,1),opacity .7s ease,transform .75s cubic-bezier(.2,.8,.2,1)',
                                        }}
                                    >
                                        <span
                                            style={{
                                                flex: 'none',
                                                fontSize:
                                                    'clamp(34px,4vw,52px)',
                                                lineHeight: 1,
                                                fontWeight: 800,
                                                letterSpacing: '-0.02em',
                                                color: '#D8E2DC',
                                            }}
                                        >
                                            {step.num}
                                        </span>
                                        <div style={{ paddingTop: '4px' }}>
                                            <h3
                                                style={{
                                                    fontSize: '21px',
                                                    fontWeight: 700,
                                                    letterSpacing: '-0.01em',
                                                    color: '#1A1A1A',
                                                    marginBottom: '9px',
                                                }}
                                            >
                                                {step.title}
                                            </h3>
                                            <p
                                                style={{
                                                    fontSize: '16px',
                                                    lineHeight: 1.62,
                                                    color: '#6E6E6E',
                                                    textWrap: 'pretty',
                                                }}
                                            >
                                                {step.body}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
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
                            {/* Tenant 1: TUMO â text left, image right */}
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
                                            Arsim Â· Edukim
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
                                        NÃ« ShqipÃ«ri, kjo qendÃ«r inovatore
                                        vjen si njÃ« nismÃ« e Fondacionit
                                        Shqiptaro-Amerikan pÃ«r Zhvillim (AADF)
                                        nÃ« bashkÃ«punim me BashkinÃ« TiranÃ«.
                                        NxÃ«nÃ«sit kanÃ« nÃ« dispozicion njÃ«
                                        hapÃ«sirÃ« moderne me laboratorÃ«
                                        mÃ«simorÃ«, auditore, studio muzikore
                                        dhe shumÃ« ambiente tÃ« hapura studimi.
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
                                        TUMO Ã«shtÃ« njÃ« organizatÃ«
                                        jofitimprurÃ«se e themeluar nga
                                        Fondacioni Simonian pÃ«r Arsimin, e
                                        hapur pÃ«r herÃ« tÃ« parÃ« nÃ« Jerevan
                                        mÃ« 2011. Filozofia e saj unike e ka
                                        sjellÃ« nÃ« Paris, Bejrut, Berlin,
                                        MoskÃ« e tani nÃ« TiranÃ«.
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
                                        StudentÃ«t mÃ«sojnÃ« nÃ« mÃ«nyrÃ« tÃ«
                                        pavarur pÃ«rmes platformÃ«s TUMO Path,
                                        duke ndÃ«rtuar njÃ« portofol personal
                                        nÃ« programim, animacion, zhvillim
                                        lojrash, dizajn grafik, filmografi,
                                        robotikÃ«, muzikÃ« dhe modelim 3D.
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
                                        src="/assets/pyramid.png"
                                        alt=""
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

                            {/* Tenant 2: ICT â image left, text right */}
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
                                            Teknologji Â· Inovacion
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
                                        HapÃ«sira TIK
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
                                        HapÃ«sira TIK nÃ« ShqipÃ«ri ka parÃ«
                                        rritje tÃ« konsiderueshme vitet e
                                        fundit, me programe arsimore, komunitete
                                        dhe rrjete mbÃ«shtetÃ«se.
                                        SipÃ«rmarrÃ«sit dhe startup-et po nxisin
                                        inovacionin pÃ«r tÃ« pÃ«rmbushur nevojat
                                        globale dhe lokale.
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
                                        Piramida e TiranÃ«s do tÃ« luajÃ« njÃ«
                                        rol qendror nÃ« nxitjen e kÃ«tij
                                        ekosistemi â njÃ« qendÃ«r pÃ«r
                                        shkÃ«mbimin e ideve, njÃ« forum publik
                                        gjithÃ«pÃ«rfshirÃ«s, i hapur 24/7 me
                                        njÃ« larmi funksionesh dhe eventesh.
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
                                        Transformimi i saj nÃ« njÃ« ekosistem
                                        tÃ« hapur TIK tregon se si njÃ«
                                        ndÃ«rtesÃ« mund tÃ« pÃ«rshtatet pÃ«r
                                        nevojat e njÃ« epoke tÃ« re, duke
                                        ruajtur historinÃ« e saj.
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
                                        src="/assets/pyramid.png"
                                        alt=""
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

                            {/* Tenant 3: ARTS â text left, image right */}
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
                                            KulturÃ« Â· Art
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
                                        Artet
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
                                        Arti luan njÃ« rol kyÃ§ nÃ« nxitjen e
                                        krijimtarisÃ« dhe frymÃ«zimin e
                                        perspektivave tÃ« reja. MundÃ«sia pÃ«r
                                        tÃ« ndarÃ« dhe ekspozuar punÃ«n me
                                        publikun Ã«shtÃ« thelbÃ«sore qÃ«
                                        artistÃ«t tÃ« realizojnÃ« vizionin e
                                        tyre.
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
                                        NÃ« PiramidÃ«n e TiranÃ«s, ne jemi tÃ«
                                        pÃ«rkushtuar pÃ«r tÃ« kultivuar artet
                                        nÃ« ShqipÃ«ri. Me njÃ« gamÃ« tÃ«
                                        larmishme ekspozitash, eventesh dhe
                                        programesh, ajo do tÃ« shÃ«rbejÃ« si
                                        vend takimi pÃ«r artistÃ«t me publikun.
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
                                        NÃ«se jeni artist apo thjesht i
                                        dashuruar pas artit, Piramida e TiranÃ«s
                                        Ã«shtÃ« vendi i pÃ«rsosur pÃ«r t'u
                                        frymÃ«zuar, biseduar dhe pÃ«r tÃ«
                                        krijuar.
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
                                        src="/assets/pyramid.png"
                                        alt=""
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
                                <div
                                    style={{
                                        display: 'flex',
                                        alignItems: 'baseline',
                                        gap: '10px',
                                        marginBottom: '18px',
                                    }}
                                >
                                    <span
                                        style={{
                                            fontSize: '21px',
                                            fontWeight: 800,
                                            letterSpacing: '0.16em',
                                            color: '#fff',
                                        }}
                                    >
                                        PIRAMIDA
                                    </span>
                                    <span
                                        style={{
                                            fontSize: '12px',
                                            fontWeight: 500,
                                            letterSpacing: '0.18em',
                                            color: 'rgba(255,255,255,0.5)',
                                            textTransform: 'uppercase',
                                        }}
                                    >
                                        Spaces
                                    </span>
                                </div>
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
                                        â
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
                                Â© 2026 Piramida Spaces Â· Tirana, Albania
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
