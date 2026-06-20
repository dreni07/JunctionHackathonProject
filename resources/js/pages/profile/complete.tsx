import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Camera, Loader2, Triangle } from 'lucide-react';
import { useRef, useState, type CSSProperties, type ReactNode } from 'react';

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
};

const css = `
.pf-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:${C.cream};color:${C.ink}}
.pf-root *{box-sizing:border-box}
.pf-field:focus-within{border-color:${C.green};box-shadow:0 0 0 4px rgba(16,130,91,0.1)}
.pf-input::placeholder{color:${C.faint}}
.pf-btn{transition:background .2s ease,transform .2s ease,opacity .2s ease}
.pf-btn:not(:disabled):hover{background:${C.greenDeep};transform:translateY(-1px)}
.pf-btn:disabled{opacity:.55;cursor:not-allowed}
.pf-back{transition:background .18s ease,color .18s ease}
.pf-back:hover{background:${C.cream};color:${C.ink}}
.pf-avatar-btn{transition:background .18s ease,border-color .18s ease}
.pf-avatar-btn:hover{background:${C.cream};border-color:${C.green}}
@keyframes pf-spin{to{transform:rotate(360deg)}}
.pf-spin{animation:pf-spin 1s linear infinite}
`;

type ProfileData = {
    name: string;
    email: string;
    phone: string | null;
    avatar_url: string | null;
    job_title: string | null;
    company: string | null;
    location: string | null;
    website: string | null;
    bio: string | null;
};

function initials(name: string): string {
    return name
        .split(' ')
        .map((word) => word[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function FieldError({ message }: { message?: string }) {
    if (!message) {
        return null;
    }

    return (
        <p style={{ marginTop: 6, fontSize: 12.5, color: C.danger, fontWeight: 500 }}>
            {message}
        </p>
    );
}

function Field({
    id,
    label,
    error,
    children,
}: {
    id: string;
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
            <label
                htmlFor={id}
                style={{
                    fontSize: 12.5,
                    fontWeight: 700,
                    letterSpacing: '0.04em',
                    textTransform: 'uppercase',
                    color: C.muted,
                    marginBottom: 8,
                }}
            >
                {label}
            </label>
            <div
                className="pf-field"
                style={{
                    borderRadius: 12,
                    border: `1px solid ${C.border}`,
                    background: C.card,
                    transition: 'border-color .2s ease, box-shadow .2s ease',
                }}
            >
                {children}
            </div>
            <FieldError message={error} />
        </div>
    );
}

const inputStyle: CSSProperties = {
    width: '100%',
    border: 'none',
    outline: 'none',
    background: 'transparent',
    padding: '12px 14px',
    fontSize: 15,
    fontFamily: 'inherit',
    color: C.ink,
};

export default function ProfileComplete({
    profile,
    completion,
}: {
    profile: ProfileData;
    completion: number;
}) {
    const fileInput = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(profile.avatar_url);

    const { data, setData, post, processing, errors } = useForm<{
        name: string;
        phone: string;
        job_title: string;
        company: string;
        location: string;
        website: string;
        bio: string;
        avatar: File | null;
    }>({
        name: profile.name ?? '',
        phone: profile.phone ?? '',
        job_title: profile.job_title ?? '',
        company: profile.company ?? '',
        location: profile.location ?? '',
        website: profile.website ?? '',
        bio: profile.bio ?? '',
        avatar: null,
    });

    const pickFile = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] ?? null;
        setData('avatar', file);
        setPreview(file ? URL.createObjectURL(file) : profile.avatar_url);
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post('/profile/complete', { forceFormData: true, preserveScroll: true });
    };

    return (
        <div
            className="pf-root"
            style={{
                display: 'flex',
                flexDirection: 'column',
                minHeight: '100vh',
                width: '100%',
            }}
        >
            <Head title="Complete your profile">
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

            <header
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '14px clamp(18px,3vw,28px)',
                    borderBottom: `1px solid ${C.borderSoft}`,
                    background: 'rgba(255,255,255,0.88)',
                    backdropFilter: 'blur(12px)',
                    position: 'sticky',
                    top: 0,
                    zIndex: 10,
                }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                    <Link
                        href="/"
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 11,
                            textDecoration: 'none',
                            color: C.ink,
                        }}
                    >
                        <span
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: 34,
                                height: 34,
                                borderRadius: 10,
                                background: `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                            }}
                        >
                            <Triangle size={15} fill="#fff" color="#fff" />
                        </span>
                        <span
                            style={{
                                fontWeight: 800,
                                letterSpacing: '0.05em',
                                fontSize: 14,
                            }}
                        >
                            PIRAMIDA
                        </span>
                    </Link>
                    <span
                        style={{
                            width: 1,
                            height: 22,
                            background: C.border,
                        }}
                    />
                    <span
                        style={{
                            fontSize: 13,
                            fontWeight: 600,
                            color: C.muted,
                        }}
                    >
                        Your profile
                    </span>
                </div>

                <Link
                    href="/planner"
                    className="pf-back"
                    style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 7,
                        padding: '8px 14px',
                        borderRadius: 999,
                        border: `1px solid ${C.border}`,
                        background: C.card,
                        color: C.muted,
                        fontSize: 13,
                        fontWeight: 600,
                        textDecoration: 'none',
                    }}
                >
                    <ArrowLeft size={15} />
                    Back to planner
                </Link>
            </header>

            <main
                style={{
                    flex: 1,
                    overflowY: 'auto',
                    padding: 'clamp(28px,5vw,56px) clamp(18px,3vw,28px)',
                }}
            >
                <div style={{ maxWidth: 680, margin: '0 auto' }}>
                    <div style={{ marginBottom: 28 }}>
                        <span
                            style={{
                                display: 'inline-block',
                                fontSize: 12,
                                fontWeight: 700,
                                letterSpacing: '0.14em',
                                textTransform: 'uppercase',
                                color: C.green,
                                marginBottom: 12,
                            }}
                        >
                            Organization profile
                        </span>
                        <h1
                            style={{
                                fontSize: 'clamp(28px,4vw,38px)',
                                fontWeight: 800,
                                letterSpacing: '-0.025em',
                                lineHeight: 1.08,
                                marginBottom: 10,
                            }}
                        >
                            Complete your profile
                        </h1>
                        <p
                            style={{
                                fontSize: 16,
                                lineHeight: 1.6,
                                color: C.muted,
                                maxWidth: '48ch',
                            }}
                        >
                            Add a photo and a few details so the Pyramid team
                            knows who they are working with.
                        </p>
                    </div>

                    <div
                        style={{
                            marginBottom: 28,
                            padding: '18px 20px',
                            borderRadius: 16,
                            border: `1px solid ${C.borderSoft}`,
                            background: C.card,
                            boxShadow: '0 12px 36px -28px rgba(26,26,26,0.18)',
                        }}
                    >
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'space-between',
                                marginBottom: 10,
                                fontSize: 13.5,
                            }}
                        >
                            <span style={{ fontWeight: 600, color: C.muted }}>
                                Profile completion
                            </span>
                            <span style={{ fontWeight: 800, color: C.green }}>
                                {completion}%
                            </span>
                        </div>
                        <div
                            style={{
                                height: 8,
                                borderRadius: 999,
                                background: C.cream,
                                overflow: 'hidden',
                            }}
                        >
                            <div
                                style={{
                                    height: '100%',
                                    width: `${completion}%`,
                                    borderRadius: 999,
                                    background: `linear-gradient(90deg, ${C.green}, ${C.greenDark})`,
                                    transition: 'width .45s ease',
                                }}
                            />
                        </div>
                    </div>

                    <form
                        onSubmit={submit}
                        style={{
                            display: 'flex',
                            flexDirection: 'column',
                            gap: 22,
                            padding: 'clamp(22px,3vw,32px)',
                            borderRadius: 22,
                            border: `1px solid ${C.borderSoft}`,
                            background: C.card,
                            boxShadow: '0 16px 48px -32px rgba(26,26,26,0.22)',
                        }}
                    >
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 18,
                                flexWrap: 'wrap',
                            }}
                        >
                            <div style={{ position: 'relative' }}>
                                <div
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        width: 88,
                                        height: 88,
                                        borderRadius: '50%',
                                        overflow: 'hidden',
                                        border: `2px solid ${C.borderSoft}`,
                                        background: C.cream,
                                        fontSize: 26,
                                        fontWeight: 700,
                                        color: C.green,
                                    }}
                                >
                                    {preview ? (
                                        <img
                                            src={preview}
                                            alt="Avatar preview"
                                            style={{
                                                width: '100%',
                                                height: '100%',
                                                objectFit: 'cover',
                                            }}
                                        />
                                    ) : (
                                        initials(data.name || profile.name || '?')
                                    )}
                                </div>
                                <button
                                    type="button"
                                    className="pf-avatar-btn"
                                    onClick={() => fileInput.current?.click()}
                                    aria-label="Upload photo"
                                    style={{
                                        position: 'absolute',
                                        right: -2,
                                        bottom: -2,
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        width: 32,
                                        height: 32,
                                        borderRadius: '50%',
                                        border: `1px solid ${C.border}`,
                                        background: C.card,
                                        color: C.green,
                                        cursor: 'pointer',
                                    }}
                                >
                                    <Camera size={15} />
                                </button>
                                <input
                                    ref={fileInput}
                                    type="file"
                                    accept="image/*"
                                    style={{ display: 'none' }}
                                    onChange={pickFile}
                                />
                            </div>
                            <div>
                                <div
                                    style={{
                                        fontSize: 15,
                                        fontWeight: 700,
                                        marginBottom: 4,
                                    }}
                                >
                                    Profile photo
                                </div>
                                <div style={{ fontSize: 13, color: C.muted }}>
                                    JPG, PNG or GIF — up to 4 MB.
                                </div>
                                <FieldError message={errors.avatar} />
                            </div>
                        </div>

                        <Field id="name" label="Full name" error={errors.name}>
                            <input
                                id="name"
                                className="pf-input"
                                style={inputStyle}
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                                placeholder="Full name"
                            />
                        </Field>

                        <div
                            style={{
                                display: 'grid',
                                gridTemplateColumns:
                                    'repeat(auto-fit, minmax(240px, 1fr))',
                                gap: 18,
                            }}
                        >
                            <Field
                                id="job_title"
                                label="Job title"
                                error={errors.job_title}
                            >
                                <input
                                    id="job_title"
                                    className="pf-input"
                                    style={inputStyle}
                                    value={data.job_title}
                                    onChange={(e) =>
                                        setData('job_title', e.target.value)
                                    }
                                    placeholder="e.g. Events Lead"
                                />
                            </Field>
                            <Field
                                id="company"
                                label="Company / organization"
                                error={errors.company}
                            >
                                <input
                                    id="company"
                                    className="pf-input"
                                    style={inputStyle}
                                    value={data.company}
                                    onChange={(e) =>
                                        setData('company', e.target.value)
                                    }
                                    placeholder="e.g. TUMO Tirana"
                                />
                            </Field>
                        </div>

                        <div
                            style={{
                                display: 'grid',
                                gridTemplateColumns:
                                    'repeat(auto-fit, minmax(240px, 1fr))',
                                gap: 18,
                            }}
                        >
                            <Field id="phone" label="Phone" error={errors.phone}>
                                <input
                                    id="phone"
                                    className="pf-input"
                                    style={inputStyle}
                                    value={data.phone}
                                    onChange={(e) =>
                                        setData('phone', e.target.value)
                                    }
                                    placeholder="+355 …"
                                />
                            </Field>
                            <Field
                                id="location"
                                label="Location"
                                error={errors.location}
                            >
                                <input
                                    id="location"
                                    className="pf-input"
                                    style={inputStyle}
                                    value={data.location}
                                    onChange={(e) =>
                                        setData('location', e.target.value)
                                    }
                                    placeholder="e.g. Tirana, Albania"
                                />
                            </Field>
                        </div>

                        <Field id="website" label="Website" error={errors.website}>
                            <input
                                id="website"
                                className="pf-input"
                                style={inputStyle}
                                value={data.website}
                                onChange={(e) =>
                                    setData('website', e.target.value)
                                }
                                placeholder="https://…"
                            />
                        </Field>

                        <Field id="bio" label="About you" error={errors.bio}>
                            <textarea
                                id="bio"
                                className="pf-input"
                                style={{
                                    ...inputStyle,
                                    resize: 'vertical',
                                    minHeight: 120,
                                    paddingTop: 12,
                                    paddingBottom: 12,
                                }}
                                value={data.bio}
                                onChange={(e) => setData('bio', e.target.value)}
                                rows={4}
                                maxLength={1000}
                                placeholder="A short introduction…"
                            />
                        </Field>

                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 12,
                                paddingTop: 8,
                            }}
                        >
                            <button
                                type="submit"
                                disabled={processing}
                                className="pf-btn"
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: 10,
                                    padding: '13px 28px',
                                    borderRadius: 999,
                                    border: 'none',
                                    background: C.green,
                                    color: '#fff',
                                    fontSize: 14,
                                    fontWeight: 700,
                                    letterSpacing: '0.04em',
                                    cursor: processing ? 'not-allowed' : 'pointer',
                                }}
                            >
                                {processing && (
                                    <Loader2 size={16} className="pf-spin" />
                                )}
                                Save profile
                            </button>
                            <span style={{ fontSize: 13, color: C.faint }}>
                                {profile.email}
                            </span>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    );
}
