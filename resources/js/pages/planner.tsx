import { Head, router } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowUp,
    FileText,
    Loader2,
    MessageSquare,
    Mic,
    Sparkles,
    Square,
    Triangle,
    Upload,
    UploadCloud,
    Volume2,
    X,
    type LucideIcon,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

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
};

const css = `
.pl-root{font-family:'Hanken Grotesk',-apple-system,BlinkMacSystemFont,sans-serif}
.pl-root *{box-sizing:border-box}
.pl-card{transition:border-color .2s ease,box-shadow .25s ease,transform .25s ease}
.pl-card:hover{border-color:${C.green};box-shadow:0 20px 40px -24px rgba(16,130,91,0.45);transform:translateY(-3px)}
.pl-card:hover .pl-card-arrow{transform:translateX(3px);color:${C.green}}
.pl-icon-btn{transition:background .18s ease,border-color .18s ease,color .18s ease}
.pl-icon-btn:hover{background:${C.cream}}
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
@keyframes pl-pulse{0%{transform:scale(1);opacity:.55}70%{transform:scale(2.1);opacity:0}100%{opacity:0}}
@keyframes pl-spin{to{transform:rotate(360deg)}}
@keyframes pl-blink{0%,80%,100%{opacity:.2}40%{opacity:1}}
.pl-spin{animation:pl-spin 1s linear infinite}
`;

type Mode = 'home' | 'voice' | 'chat' | 'upload';

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

export default function Planner() {
    const [mode, setMode] = useState<Mode>('home');
    const [seedChat, setSeedChat] = useState('');

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

            {/* ===== TOP BAR ===== */}
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
                <div
                    style={{ display: 'flex', alignItems: 'center', gap: 11 }}
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
                    <span style={{ fontWeight: 800, letterSpacing: '0.04em' }}>
                        PIRAMIDA
                    </span>
                    <span style={{ color: C.faint, fontSize: 14 }}>
                        · AI Planner
                    </span>
                </div>
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
            </header>

            {/* ===== BODY ===== */}
            <div
                style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    minHeight: 0,
                }}
            >
                {mode !== 'home' && (
                    <div style={{ padding: '14px 26px 0' }}>
                        <button
                            type="button"
                            className="pl-ghost"
                            onClick={() => setMode('home')}
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: 6,
                                padding: '7px 12px',
                                borderRadius: 9,
                                border: 'none',
                                background: 'transparent',
                                color: C.muted,
                                fontSize: 14,
                                fontWeight: 500,
                                cursor: 'pointer',
                            }}
                        >
                            <ArrowLeft size={16} />
                            Back
                        </button>
                    </div>
                )}

                {mode === 'home' && <Home onSelect={setMode} />}
                {mode === 'voice' && (
                    <VoiceMode
                        onUseTranscript={(text) => {
                            setSeedChat(text);
                            setMode('chat');
                        }}
                    />
                )}
                {mode === 'chat' && <ChatMode initialInput={seedChat} />}
                {mode === 'upload' && <UploadMode />}
            </div>
        </div>
    );
}

/* ============================ HOME ============================ */

const actions: {
    mode: Mode;
    icon: LucideIcon;
    title: string;
    subtitle: string;
}[] = [
    {
        mode: 'voice',
        icon: Mic,
        title: 'Talk to Planner',
        subtitle: 'Describe your event out loud',
    },
    {
        mode: 'chat',
        icon: MessageSquare,
        title: 'Chat with Planner',
        subtitle: 'Type and refine in a chat',
    },
    {
        mode: 'upload',
        icon: Upload,
        title: 'Upload a brief',
        subtitle: 'Add a PDF or image',
    },
];

function Home({ onSelect }: { onSelect: (mode: Mode) => void }) {
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
            <Orb />
            <h1
                style={{
                    fontSize: 32,
                    fontWeight: 700,
                    letterSpacing: '-0.02em',
                    textAlign: 'center',
                    margin: '26px 0 10px',
                }}
            >
                What event do you want to organize?
            </h1>
            <p
                style={{
                    fontSize: 15.5,
                    color: C.muted,
                    textAlign: 'center',
                    maxWidth: 480,
                    marginBottom: 34,
                }}
            >
                Pick how you'd like to start — talk it through, chat it out, or
                upload an existing brief.
            </p>

            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns:
                        'repeat(auto-fit, minmax(210px, 1fr))',
                    gap: 16,
                    width: '100%',
                    maxWidth: 720,
                }}
            >
                {actions.map((action) => (
                    <button
                        key={action.mode}
                        type="button"
                        className="pl-card"
                        onClick={() => onSelect(action.mode)}
                        style={{
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'flex-start',
                            gap: 14,
                            padding: '22px 20px',
                            borderRadius: 16,
                            border: `1px solid ${C.border}`,
                            background: C.card,
                            cursor: 'pointer',
                            textAlign: 'left',
                            boxShadow: '0 14px 34px -26px rgba(26,26,26,0.3)',
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
                            }}
                        >
                            <action.icon size={21} />
                        </span>
                        <span>
                            <span
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 6,
                                    fontSize: 16.5,
                                    fontWeight: 600,
                                }}
                            >
                                {action.title}
                                <ArrowUp
                                    className="pl-card-arrow"
                                    size={15}
                                    color={C.faint}
                                    style={{
                                        transform: 'rotate(45deg)',
                                        transition: 'transform .2s ease',
                                    }}
                                />
                            </span>
                            <span
                                style={{
                                    display: 'block',
                                    marginTop: 3,
                                    fontSize: 13.5,
                                    color: C.muted,
                                }}
                            >
                                {action.subtitle}
                            </span>
                        </span>
                    </button>
                ))}
            </div>
        </div>
    );
}

function Orb({ size = 92 }: { size?: number }) {
    return (
        <div style={{ position: 'relative', width: size, height: size }}>
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
                    width: size,
                    height: size,
                    borderRadius: '50%',
                    background:
                        'radial-gradient(circle at 35% 28%, #6FC0A2 0%, #10825B 52%, #0C5A41 100%)',
                    boxShadow:
                        'inset 0 -9px 20px rgba(0,0,0,0.28), inset 0 7px 14px rgba(255,255,255,0.5)',
                }}
            />
        </div>
    );
}

/* ============================ VOICE ============================ */

type VoiceStatus = 'recording' | 'transcribing' | 'done' | 'error';

function VoiceMode({
    onUseTranscript,
}: {
    onUseTranscript: (text: string) => void;
}) {
    const [status, setStatus] = useState<VoiceStatus>('recording');
    const [seconds, setSeconds] = useState(0);
    const [transcript, setTranscript] = useState('');
    const [error, setError] = useState('');

    const recorderRef = useRef<MediaRecorder | null>(null);
    const chunksRef = useRef<Blob[]>([]);
    const streamRef = useRef<MediaStream | null>(null);
    const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const stopStream = () => {
        streamRef.current?.getTracks().forEach((track) => track.stop());
        streamRef.current = null;
        if (timerRef.current) {
            clearInterval(timerRef.current);
            timerRef.current = null;
        }
    };

    const start = async () => {
        setError('');
        setTranscript('');
        setSeconds(0);
        chunksRef.current = [];

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: true,
            });
            streamRef.current = stream;

            const recorder = new MediaRecorder(stream);
            recorderRef.current = recorder;

            recorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    chunksRef.current.push(event.data);
                }
            };
            recorder.onstop = () => {
                stopStream();
                const blob = new Blob(chunksRef.current, {
                    type: recorder.mimeType || 'audio/webm',
                });
                void transcribe(blob);
            };

            recorder.start();
            setStatus('recording');
            timerRef.current = setInterval(
                () => setSeconds((s) => s + 1),
                1000,
            );
        } catch {
            setError(
                'Microphone access was blocked. Allow mic permission and try again.',
            );
            setStatus('error');
        }
    };

    const stop = () => {
        if (recorderRef.current && recorderRef.current.state !== 'inactive') {
            setStatus('transcribing');
            recorderRef.current.stop();
        }
    };

    const transcribe = async (blob: Blob) => {
        try {
            const form = new FormData();
            const extension = blob.type.includes('mp4') ? 'mp4' : 'webm';
            form.append('audio', blob, `recording.${extension}`);

            const response = await postForm('/speech/transcribe', form);
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                setError(data.message || 'Could not transcribe the recording.');
                setStatus('error');

                return;
            }

            setTranscript(String(data.text || '').trim());
            setStatus('done');
        } catch {
            setError('Network error while transcribing.');
            setStatus('error');
        }
    };

    useEffect(() => {
        void start();

        return () => {
            if (
                recorderRef.current &&
                recorderRef.current.state !== 'inactive'
            ) {
                recorderRef.current.stop();
            }
            stopStream();
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
        <div
            style={{
                flex: 1,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '24px',
                gap: 22,
            }}
        >
            {/* Mic / status visual */}
            <div
                style={{
                    position: 'relative',
                    width: 116,
                    height: 116,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                }}
            >
                {status === 'recording' &&
                    [0, 0.6, 1.2].map((delay) => (
                        <span
                            key={delay}
                            style={{
                                position: 'absolute',
                                inset: 18,
                                borderRadius: '50%',
                                background: 'rgba(16,130,91,0.35)',
                                animation: `pl-pulse 2.2s ease-out ${delay}s infinite`,
                            }}
                        />
                    ))}
                <span
                    style={{
                        position: 'relative',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        width: 80,
                        height: 80,
                        borderRadius: '50%',
                        background:
                            status === 'error'
                                ? '#F1E2E0'
                                : `linear-gradient(135deg, ${C.green}, ${C.greenDark})`,
                        color: status === 'error' ? C.danger : '#fff',
                    }}
                >
                    {status === 'transcribing' ? (
                        <Loader2 className="pl-spin" size={30} />
                    ) : status === 'error' ? (
                        <X size={30} />
                    ) : (
                        <Mic size={30} />
                    )}
                </span>
            </div>

            {status === 'recording' && (
                <>
                    <div style={{ textAlign: 'center' }}>
                        <p style={{ fontSize: 18, fontWeight: 600 }}>
                            Listening…
                        </p>
                        <p style={{ color: C.muted, marginTop: 4 }}>
                            {formatTime(seconds)} · speak your event idea
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={stop}
                        style={primaryButton()}
                    >
                        <Square size={16} fill="#fff" />
                        Stop & transcribe
                    </button>
                </>
            )}

            {status === 'transcribing' && (
                <p style={{ fontSize: 17, fontWeight: 600 }}>Transcribing…</p>
            )}

            {status === 'error' && (
                <>
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
                        onClick={() => void start()}
                        style={primaryButton()}
                    >
                        <Mic size={16} />
                        Try again
                    </button>
                </>
            )}

            {status === 'done' && (
                <div style={{ width: '100%', maxWidth: 560 }}>
                    <p
                        style={{
                            fontSize: 13,
                            fontWeight: 600,
                            textTransform: 'uppercase',
                            letterSpacing: '0.05em',
                            color: C.faint,
                            marginBottom: 8,
                        }}
                    >
                        Transcript
                    </p>
                    <div
                        style={{
                            padding: '16px 18px',
                            borderRadius: 14,
                            border: `1px solid ${C.border}`,
                            background: C.card,
                            fontSize: 15.5,
                            lineHeight: 1.6,
                            minHeight: 64,
                            color: transcript ? C.ink : C.faint,
                        }}
                    >
                        {transcript || 'Nothing was picked up. Try again.'}
                    </div>
                    <div
                        style={{
                            display: 'flex',
                            gap: 10,
                            marginTop: 16,
                            justifyContent: 'center',
                        }}
                    >
                        <button
                            type="button"
                            onClick={() => void start()}
                            style={secondaryButton()}
                        >
                            <Mic size={16} />
                            Record again
                        </button>
                        <button
                            type="button"
                            disabled={!transcript}
                            onClick={() => onUseTranscript(transcript)}
                            style={primaryButton(!transcript)}
                        >
                            Continue in chat
                            <ArrowUp
                                size={16}
                                style={{ transform: 'rotate(45deg)' }}
                            />
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}

/* ============================ CHAT ============================ */

type ChatMessage = {
    role: 'user' | 'assistant';
    content: string;
    tools?: string[];
};

function ChatMode({ initialInput = '' }: { initialInput?: string }) {
    const [messages, setMessages] = useState<ChatMessage[]>([]);
    const [input, setInput] = useState(initialInput);
    const [sending, setSending] = useState(false);
    const [speakingIndex, setSpeakingIndex] = useState<number | null>(null);
    const scrollRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        scrollRef.current?.scrollTo({
            top: scrollRef.current.scrollHeight,
            behavior: 'smooth',
        });
    }, [messages, sending]);

    const send = async () => {
        const text = input.trim();
        if (text === '' || sending) {
            return;
        }

        const next: ChatMessage[] = [
            ...messages,
            { role: 'user', content: text },
        ];
        setMessages(next);
        setInput('');
        setSending(true);

        try {
            const response = await postJson('/chat', {
                messages: next.map((m) => ({
                    role: m.role,
                    content: m.content,
                })),
            });
            const data = await response.json().catch(() => ({}));

            setMessages((current) => [
                ...current,
                {
                    role: 'assistant',
                    content: response.ok
                        ? String(data.reply || '')
                        : data.message || 'Something went wrong.',
                    tools: Array.isArray(data.tools_used)
                        ? data.tools_used
                        : undefined,
                },
            ]);
        } catch {
            setMessages((current) => [
                ...current,
                {
                    role: 'assistant',
                    content: 'Network error. Please try again.',
                },
            ]);
        } finally {
            setSending(false);
        }
    };

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
            </div>
        </div>
    );
}

/* ============================ UPLOAD ============================ */

function UploadMode() {
    const [file, setFile] = useState<File | null>(null);
    const [over, setOver] = useState(false);
    const [uploading, setUploading] = useState(false);
    const inputRef = useRef<HTMLInputElement | null>(null);

    const upload = () => {
        if (!file || uploading) {
            return;
        }

        setUploading(true);
        router.post(
            '/documents',
            { file },
            {
                forceFormData: true,
                onFinish: () => setUploading(false),
            },
        );
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
                    onClick={upload}
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
                    {uploading ? 'Uploading…' : 'Upload & read'}
                </button>
            </div>
        </div>
    );
}

/* ============================ shared button styles ============================ */

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

function secondaryButton(): React.CSSProperties {
    return {
        display: 'inline-flex',
        alignItems: 'center',
        gap: 8,
        padding: '12px 20px',
        borderRadius: 11,
        border: `1px solid ${C.border}`,
        background: C.card,
        color: C.ink,
        fontSize: 15,
        fontWeight: 600,
        cursor: 'pointer',
    };
}

function formatTime(totalSeconds: number): string {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
}
