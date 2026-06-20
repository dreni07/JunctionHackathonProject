import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Check,
    ChevronDown,
    Loader2,
    Mail,
    Send,
    Sparkles,
    X,
} from 'lucide-react';
import { useMemo, useRef, useState } from 'react';

const C = {
    cream: '#F4F3EE',
    card: '#FFFFFF',
    border: '#E0DCD3',
    borderSoft: '#EAE7DC',
    green: '#10825B',
    greenDeep: '#0E6E4D',
    greenTint: '#D8E2DC',
    ink: '#1A1A1A',
    muted: '#6E6E6E',
    faint: '#9A958B',
    danger: '#B4453A',
};

const css = `
.mbt-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif;background:${C.cream};color:${C.ink};min-height:100vh}
.mbt-root *{box-sizing:border-box}
.mbt-card{transition:box-shadow .25s ease,transform .2s ease,border-color .2s ease}
.mbt-card:hover{box-shadow:0 22px 50px -34px rgba(26,26,26,.4);transform:translateY(-2px);border-color:${C.green}}
.mbt-btn{transition:background .18s ease,transform .15s ease,opacity .15s ease}
.mbt-btn:not(:disabled):hover{transform:translateY(-1px)}
.mbt-spin{animation:mbt-spin 1s linear infinite}
@keyframes mbt-spin{to{transform:rotate(360deg)}}
@keyframes mbt-fade{from{opacity:0}to{opacity:1}}
@keyframes mbt-in{from{opacity:0;transform:translateY(10px) scale(.98)}to{opacity:1;transform:none}}
.mbt-bg{animation:mbt-fade .18s ease}
.mbt-modal{animation:mbt-in .24s cubic-bezier(.2,.8,.2,1)}
.mbt-field{outline:none;font-family:inherit;width:100%;border:1px solid ${C.border};border-radius:10px;padding:10px 12px;font-size:14px;background:${C.card};color:${C.ink}}
.mbt-field:focus{border-color:${C.green};box-shadow:0 0 0 3px rgba(16,130,91,.12)}
.mbt-prev:hover{background:${C.cream}}
`;

type Template = { key: string; name: string; description: string };
type PrevPrompt = {
    id: number;
    prompt: string;
    template: string | null;
    created_at: string | null;
};

type PageProps = {
    templates: Template[];
    previousPrompts: PrevPrompt[];
};

function csrf(): string {
    const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return m ? decodeURIComponent(m[1]) : '';
}

async function postJson(url: string, body: unknown): Promise<Response> {
    return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        body: JSON.stringify(body),
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': csrf(),
        },
    });
}

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function ManageBoringThings(props: PageProps) {
    const [emailOpen, setEmailOpen] = useState(false);

    return (
        <div className="mbt-root">
            <Head title="Manage boring things">
                <link
                    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap"
                    rel="stylesheet"
                />
            </Head>
            <style dangerouslySetInnerHTML={{ __html: css }} />

            <div style={{ maxWidth: 880, margin: '0 auto', padding: '28px 24px' }}>
                <Link
                    href="/operations"
                    style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: 6,
                        fontSize: 13,
                        color: C.muted,
                        textDecoration: 'none',
                        marginBottom: 18,
                    }}
                >
                    <ArrowLeft size={15} /> Operations
                </Link>

                <h1 style={{ fontSize: 28, fontWeight: 800, margin: 0 }}>
                    Manage boring things
                </h1>
                <p style={{ fontSize: 15, color: C.muted, margin: '6px 0 26px' }}>
                    The little chores, handled fast — let the assistant do the
                    typing.
                </p>

                <div
                    style={{
                        display: 'grid',
                        gridTemplateColumns:
                            'repeat(auto-fill, minmax(260px, 1fr))',
                        gap: 16,
                    }}
                >
                    <button
                        type="button"
                        className="mbt-card"
                        onClick={() => setEmailOpen(true)}
                        style={{
                            textAlign: 'left',
                            background: C.card,
                            border: `1px solid ${C.border}`,
                            borderRadius: 16,
                            padding: 20,
                            cursor: 'pointer',
                            fontFamily: 'inherit',
                        }}
                    >
                        <span
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: 44,
                                height: 44,
                                borderRadius: 12,
                                background: C.greenTint,
                                color: C.green,
                                marginBottom: 14,
                            }}
                        >
                            <Mail size={22} />
                        </span>
                        <div style={{ fontSize: 16.5, fontWeight: 700 }}>
                            Send emails
                        </div>
                        <div
                            style={{
                                fontSize: 13.5,
                                color: C.muted,
                                marginTop: 4,
                                lineHeight: 1.5,
                            }}
                        >
                            Pick a template, add recipients, describe what to
                            say — the assistant writes it, you review and send.
                        </div>
                    </button>
                </div>
            </div>

            {emailOpen && (
                <SendEmailModal
                    templates={props.templates}
                    previousPrompts={props.previousPrompts}
                    onClose={() => setEmailOpen(false)}
                />
            )}
        </div>
    );
}

function SendEmailModal({
    templates,
    previousPrompts,
    onClose,
}: {
    templates: Template[];
    previousPrompts: PrevPrompt[];
    onClose: () => void;
}) {
    const [template, setTemplate] = useState(templates[0]?.key ?? '');
    const [recipients, setRecipients] = useState<string[]>([]);
    const [emailInput, setEmailInput] = useState('');
    const [prompt, setPrompt] = useState('');
    const [prompts, setPrompts] = useState<PrevPrompt[]>(previousPrompts);
    const [showPrev, setShowPrev] = useState(false);

    const [step, setStep] = useState<
        'compose' | 'review' | 'sending' | 'sent'
    >('compose');
    const [busy, setBusy] = useState(false);
    const [subject, setSubject] = useState('');
    const [body, setBody] = useState('');
    const [result, setResult] = useState<{ sent: string[]; failed: string[] } | null>(null);
    const [error, setError] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    const addEmails = (raw: string) => {
        const parts = raw
            .split(/[,;\s]+/)
            .map((p) => p.trim())
            .filter(Boolean);
        const valid = parts.filter((p) => EMAIL_RE.test(p));
        if (valid.length) {
            setRecipients((prev) =>
                Array.from(new Set([...prev, ...valid])),
            );
        }
        setEmailInput('');
    };

    const onEmailKey = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter' || e.key === ',' || e.key === ' ') {
            e.preventDefault();
            addEmails(emailInput);
        } else if (e.key === 'Backspace' && emailInput === '') {
            setRecipients((prev) => prev.slice(0, -1));
        }
    };

    const canGenerate = prompt.trim().length > 3 && !busy;
    const canSend = recipients.length > 0 && subject.trim() && body.trim();

    const generate = async () => {
        setBusy(true);
        setError('');
        try {
            const res = await postJson('/operations/emails/generate', {
                template,
                recipients,
                prompt,
            });
            if (!res.ok) {
                throw new Error('generate failed');
            }
            const json = await res.json();
            setSubject(json.data.subject ?? '');
            setBody(json.data.body ?? '');
            if (json.data.previousPrompts) {
                setPrompts(json.data.previousPrompts);
            }
            setStep('review');
        } catch {
            setError('The assistant could not draft this email. Try again.');
        } finally {
            setBusy(false);
        }
    };

    const send = async () => {
        setStep('sending');
        setError('');
        try {
            const res = await postJson('/operations/emails/send', {
                template,
                recipients,
                subject,
                body,
            });
            if (!res.ok) {
                throw new Error('send failed');
            }
            const json = await res.json();
            setResult(json.data);
            setStep('sent');
        } catch {
            setError('Sending failed. Please try again.');
            setStep('review');
        }
    };

    const templateName = useMemo(
        () => templates.find((t) => t.key === template)?.name ?? '',
        [templates, template],
    );

    return (
        <div
            className="mbt-bg"
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
                className="mbt-modal"
                onClick={(e) => e.stopPropagation()}
                style={{
                    width: '100%',
                    maxWidth: 560,
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
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 11,
                        marginBottom: 20,
                    }}
                >
                    <span
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: 38,
                            height: 38,
                            borderRadius: 11,
                            background: C.greenTint,
                            color: C.green,
                        }}
                    >
                        <Mail size={19} />
                    </span>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 17, fontWeight: 800 }}>
                            {step === 'sent' ? 'Emails sent' : 'Send emails'}
                        </div>
                        <div style={{ fontSize: 12.5, color: C.muted }}>
                            {step === 'review'
                                ? 'Review the draft, then send.'
                                : step === 'sent'
                                  ? 'All done.'
                                  : 'The assistant will write it for you.'}
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Close"
                        style={{
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

                {(step === 'compose' || step === 'review') && (
                    <>
                        {/* template */}
                        <label style={labelStyle}>Template</label>
                        <div style={{ position: 'relative', marginBottom: 16 }}>
                            <select
                                className="mbt-field"
                                value={template}
                                onChange={(e) => setTemplate(e.target.value)}
                                style={{ appearance: 'none', cursor: 'pointer' }}
                            >
                                {templates.map((t) => (
                                    <option key={t.key} value={t.key}>
                                        {t.name} — {t.description}
                                    </option>
                                ))}
                            </select>
                            <ChevronDown
                                size={16}
                                color={C.faint}
                                style={{
                                    position: 'absolute',
                                    right: 12,
                                    top: 13,
                                    pointerEvents: 'none',
                                }}
                            />
                        </div>

                        {/* recipients */}
                        <label style={labelStyle}>Recipients</label>
                        <div
                            onClick={() => inputRef.current?.focus()}
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                gap: 6,
                                padding: 8,
                                border: `1px solid ${C.border}`,
                                borderRadius: 10,
                                marginBottom: 16,
                                cursor: 'text',
                                minHeight: 44,
                            }}
                        >
                            {recipients.map((r) => (
                                <span
                                    key={r}
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: 5,
                                        background: C.greenTint,
                                        color: C.greenDeep,
                                        borderRadius: 999,
                                        padding: '3px 6px 3px 10px',
                                        fontSize: 12.5,
                                        fontWeight: 600,
                                    }}
                                >
                                    {r}
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setRecipients((prev) =>
                                                prev.filter((x) => x !== r),
                                            )
                                        }
                                        style={{
                                            border: 'none',
                                            background: 'none',
                                            cursor: 'pointer',
                                            color: C.greenDeep,
                                            display: 'flex',
                                            padding: 0,
                                        }}
                                    >
                                        <X size={13} />
                                    </button>
                                </span>
                            ))}
                            <input
                                ref={inputRef}
                                value={emailInput}
                                onChange={(e) => setEmailInput(e.target.value)}
                                onKeyDown={onEmailKey}
                                onBlur={() => emailInput && addEmails(emailInput)}
                                placeholder={
                                    recipients.length
                                        ? 'Add another…'
                                        : 'name@email.com, then Enter'
                                }
                                style={{
                                    flex: 1,
                                    minWidth: 160,
                                    border: 'none',
                                    outline: 'none',
                                    fontSize: 13.5,
                                    fontFamily: 'inherit',
                                    background: 'transparent',
                                }}
                            />
                        </div>

                        {/* prompt */}
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'space-between',
                                marginBottom: 6,
                            }}
                        >
                            <label style={{ ...labelStyle, marginBottom: 0 }}>
                                Describe to the assistant what to include
                            </label>
                            {prompts.length > 0 && (
                                <div style={{ position: 'relative' }}>
                                    <button
                                        type="button"
                                        onClick={() => setShowPrev((v) => !v)}
                                        style={{
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            gap: 5,
                                            fontSize: 12,
                                            fontWeight: 600,
                                            color: C.green,
                                            background: 'none',
                                            border: 'none',
                                            cursor: 'pointer',
                                            fontFamily: 'inherit',
                                        }}
                                    >
                                        Use previous prompts
                                        <ChevronDown size={13} />
                                    </button>
                                    {showPrev && (
                                        <div
                                            style={{
                                                position: 'absolute',
                                                right: 0,
                                                top: 24,
                                                width: 320,
                                                maxHeight: 240,
                                                overflowY: 'auto',
                                                background: C.card,
                                                border: `1px solid ${C.border}`,
                                                borderRadius: 12,
                                                boxShadow:
                                                    '0 20px 50px -24px rgba(26,26,26,0.4)',
                                                zIndex: 5,
                                                padding: 6,
                                            }}
                                        >
                                            {prompts.map((p) => (
                                                <button
                                                    key={p.id}
                                                    type="button"
                                                    className="mbt-prev"
                                                    onClick={() => {
                                                        setPrompt(p.prompt);
                                                        setShowPrev(false);
                                                    }}
                                                    style={{
                                                        display: 'block',
                                                        width: '100%',
                                                        textAlign: 'left',
                                                        padding: '8px 10px',
                                                        borderRadius: 8,
                                                        border: 'none',
                                                        background: 'none',
                                                        cursor: 'pointer',
                                                        fontSize: 12.5,
                                                        color: C.ink,
                                                        fontFamily: 'inherit',
                                                        lineHeight: 1.4,
                                                    }}
                                                >
                                                    {p.prompt.length > 90
                                                        ? p.prompt.slice(0, 90) +
                                                          '…'
                                                        : p.prompt}
                                                </button>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                        <textarea
                            className="mbt-field"
                            value={prompt}
                            onChange={(e) => setPrompt(e.target.value)}
                            rows={3}
                            placeholder="e.g. Tell guests the AI Builders Conference moved to Hall B08, doors at 9am, coffee provided."
                            style={{ resize: 'vertical', marginBottom: 16 }}
                        />

                        {step === 'review' && (
                            <div
                                style={{
                                    borderTop: `1px dashed ${C.border}`,
                                    paddingTop: 16,
                                    marginBottom: 4,
                                }}
                            >
                                <div
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 7,
                                        fontSize: 12,
                                        fontWeight: 700,
                                        letterSpacing: '0.05em',
                                        textTransform: 'uppercase',
                                        color: C.green,
                                        marginBottom: 12,
                                    }}
                                >
                                    <Sparkles size={14} /> Draft ({templateName})
                                    — edit anything
                                </div>
                                <label style={labelStyle}>Subject</label>
                                <input
                                    className="mbt-field"
                                    value={subject}
                                    onChange={(e) => setSubject(e.target.value)}
                                    style={{ marginBottom: 14 }}
                                />
                                <label style={labelStyle}>Body</label>
                                <textarea
                                    className="mbt-field"
                                    value={body}
                                    onChange={(e) => setBody(e.target.value)}
                                    rows={10}
                                    style={{ resize: 'vertical' }}
                                />
                            </div>
                        )}

                        {error && (
                            <div
                                style={{
                                    color: C.danger,
                                    fontSize: 13,
                                    marginTop: 10,
                                }}
                            >
                                {error}
                            </div>
                        )}

                        {/* actions */}
                        <div
                            style={{
                                display: 'flex',
                                gap: 10,
                                marginTop: 18,
                            }}
                        >
                            {step === 'compose' ? (
                                <button
                                    type="button"
                                    className="mbt-btn"
                                    disabled={!canGenerate}
                                    onClick={generate}
                                    style={primaryBtn(!canGenerate)}
                                >
                                    {busy ? (
                                        <Loader2
                                            size={16}
                                            className="mbt-spin"
                                        />
                                    ) : (
                                        <Sparkles size={16} />
                                    )}
                                    {busy ? 'Writing…' : 'Generate email'}
                                </button>
                            ) : (
                                <>
                                    <button
                                        type="button"
                                        className="mbt-btn"
                                        onClick={generate}
                                        disabled={busy}
                                        style={ghostBtn}
                                    >
                                        {busy ? (
                                            <Loader2
                                                size={15}
                                                className="mbt-spin"
                                            />
                                        ) : (
                                            <Sparkles size={15} />
                                        )}
                                        Regenerate
                                    </button>
                                    <button
                                        type="button"
                                        className="mbt-btn"
                                        disabled={!canSend}
                                        onClick={send}
                                        style={primaryBtn(!canSend)}
                                    >
                                        <Send size={16} />
                                        Agree &amp; send
                                    </button>
                                </>
                            )}
                        </div>
                    </>
                )}

                {step === 'sending' && (
                    <div
                        style={{
                            padding: '40px 0',
                            textAlign: 'center',
                            color: C.muted,
                        }}
                    >
                        <Loader2
                            size={30}
                            color={C.green}
                            className="mbt-spin"
                            style={{ marginBottom: 14 }}
                        />
                        <div style={{ fontSize: 14 }}>
                            Sending to {recipients.length} recipient
                            {recipients.length === 1 ? '' : 's'}…
                        </div>
                    </div>
                )}

                {step === 'sent' && result && (
                    <div style={{ padding: '8px 0' }}>
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 10,
                                padding: '14px 16px',
                                borderRadius: 12,
                                background: 'rgba(16,130,91,0.08)',
                                color: C.greenDeep,
                                marginBottom: 14,
                            }}
                        >
                            <Check size={20} />
                            <span style={{ fontSize: 14.5, fontWeight: 600 }}>
                                Sent to {result.sent.length} recipient
                                {result.sent.length === 1 ? '' : 's'}
                                {result.failed.length
                                    ? ` · ${result.failed.length} failed`
                                    : ''}
                                .
                            </span>
                        </div>
                        {result.failed.length > 0 && (
                            <div
                                style={{
                                    fontSize: 12.5,
                                    color: C.danger,
                                    marginBottom: 14,
                                }}
                            >
                                Failed: {result.failed.join(', ')}
                            </div>
                        )}
                        <button
                            type="button"
                            className="mbt-btn"
                            onClick={onClose}
                            style={primaryBtn(false)}
                        >
                            Done
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

const labelStyle: React.CSSProperties = {
    display: 'block',
    fontSize: 12,
    fontWeight: 700,
    letterSpacing: '0.03em',
    textTransform: 'uppercase',
    color: C.faint,
    marginBottom: 6,
};

function primaryBtn(disabled: boolean): React.CSSProperties {
    return {
        flex: 1,
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 8,
        padding: '12px',
        borderRadius: 11,
        border: 'none',
        background: C.green,
        color: '#fff',
        fontSize: 14.5,
        fontWeight: 700,
        cursor: disabled ? 'not-allowed' : 'pointer',
        opacity: disabled ? 0.45 : 1,
        fontFamily: 'inherit',
    };
}

const ghostBtn: React.CSSProperties = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 7,
    padding: '12px 16px',
    borderRadius: 11,
    border: `1px solid ${C.border}`,
    background: C.card,
    color: C.ink,
    fontSize: 14,
    fontWeight: 600,
    cursor: 'pointer',
    fontFamily: 'inherit',
};
