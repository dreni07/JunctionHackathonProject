import { Head, Link } from '@inertiajs/react';
import { useRef, useState } from 'react';

function getCookie(name: string): string {
    const match = document.cookie.match(
        new RegExp('(^|; )' + name + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[2]) : '';
}

type Message = {
    role: 'user' | 'assistant';
    content: string;
    tools?: string[];
};

export default function Chat() {
    const [messages, setMessages] = useState<Message[]>([]);
    const [input, setInput] = useState('');
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const endRef = useRef<HTMLDivElement>(null);

    function scrollToBottom() {
        requestAnimationFrame(() =>
            endRef.current?.scrollIntoView({ behavior: 'smooth' }),
        );
    }

    async function send(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const trimmed = input.trim();

        if (!trimmed || processing) {
            return;
        }

        const next: Message[] = [
            ...messages,
            { role: 'user', content: trimmed },
        ];
        setMessages(next);
        setInput('');
        setError(null);
        setProcessing(true);
        scrollToBottom();

        try {
            const res = await fetch('/chat', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
                },
                body: JSON.stringify({
                    messages: next.map((m) => ({
                        role: m.role,
                        content: m.content,
                    })),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                setError(data?.message ?? 'The assistant failed to respond.');

                return;
            }

            setMessages([
                ...next,
                {
                    role: 'assistant',
                    content: data.reply ?? '',
                    tools: data.tools_used ?? [],
                },
            ]);
            scrollToBottom();
        } catch {
            setError('Network error — could not reach the server.');
        } finally {
            setProcessing(false);
        }
    }

    return (
        <>
            <Head title="Chat" />
            <div className="mx-auto flex min-h-screen max-w-2xl flex-col p-6 text-[#1b1b18] dark:text-[#EDEDEC]">
                <header className="mb-4 flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Study Assistant</h1>
                    <Link
                        href="/documents"
                        className="text-sm text-neutral-500 hover:underline"
                    >
                        Library →
                    </Link>
                </header>

                <div className="flex-1 space-y-4 overflow-y-auto rounded-xl border border-neutral-200 p-4 dark:border-neutral-800">
                    {messages.length === 0 && (
                        <p className="text-sm text-neutral-500">
                            Ask me anything to get started.
                        </p>
                    )}

                    {messages.map((message, index) => (
                        <div
                            key={index}
                            className={
                                message.role === 'user'
                                    ? 'text-right'
                                    : 'text-left'
                            }
                        >
                            <span
                                className={
                                    'inline-block max-w-[85%] rounded-2xl px-4 py-2 text-sm whitespace-pre-wrap ' +
                                    (message.role === 'user'
                                        ? 'bg-neutral-900 text-white dark:bg-white dark:text-black'
                                        : 'bg-neutral-100 dark:bg-neutral-800')
                                }
                            >
                                {message.content}
                            </span>
                            {message.role === 'assistant' &&
                                message.tools &&
                                message.tools.length > 0 && (
                                    <div className="mt-1 flex flex-wrap gap-1">
                                        {message.tools.map((tool, i) => (
                                            <span
                                                key={i}
                                                className="rounded-full bg-neutral-200 px-2 py-0.5 text-[10px] font-medium text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300"
                                            >
                                                🔧 {tool}
                                            </span>
                                        ))}
                                    </div>
                                )}
                        </div>
                    ))}

                    {processing && (
                        <p className="text-left text-sm text-neutral-400">
                            Thinking…
                        </p>
                    )}
                    <div ref={endRef} />
                </div>

                {error && <p className="mt-2 text-sm text-red-600">{error}</p>}

                <form onSubmit={send} className="mt-4 flex gap-2">
                    <input
                        type="text"
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        placeholder="Type a message…"
                        className="flex-1 rounded-md border border-neutral-300 px-4 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                    />
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50 dark:bg-white dark:text-black"
                    >
                        Send
                    </button>
                </form>
            </div>
        </>
    );
}
