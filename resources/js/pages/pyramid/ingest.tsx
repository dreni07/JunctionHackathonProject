import { Head, Link } from '@inertiajs/react';
import { FileUp, Loader2, Sparkles, Table2 } from 'lucide-react';
import { useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type ToolActivity = {
    name: string;
    input: Record<string, unknown>;
    result: string;
};

type IngestResult = {
    success: true;
    summary: string;
    extract_preview: string;
    character_count: number;
    tool_activity: ToolActivity[];
};

function getCookie(name: string): string {
    const match = document.cookie.match(
        new RegExp('(^|; )' + name + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[2]) : '';
}

const toolLabels: Record<string, string> = {
    list_matching_pyramid_tables: 'Matched tables',
    create_pyramid_table: 'Created table',
    insert_pyramid_rows: 'Inserted rows',
};

export default function PyramidIngest() {
    const inputRef = useRef<HTMLInputElement>(null);
    const [file, setFile] = useState<File | null>(null);
    const [dragActive, setDragActive] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [result, setResult] = useState<IngestResult | null>(null);

    function pickFile(selected: File | null) {
        setFile(selected);
        setError(null);
        setResult(null);
    }

    async function submit(event: React.FormEvent) {
        event.preventDefault();

        if (!file || processing) {
            return;
        }

        setProcessing(true);
        setError(null);
        setResult(null);

        const body = new FormData();
        body.append('file', file);

        try {
            const response = await fetch('/pyramid/ingest', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
                },
                body,
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                setError(data.message ?? 'Ingestion failed. Please try again.');

                return;
            }

            setResult(data as IngestResult);
        } catch {
            setError('Network error while ingesting the document.');
        } finally {
            setProcessing(false);
        }
    }

    return (
        <>
            <Head title="Pyramid knowledge ingest" />

            <div className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="space-y-2">
                    <div className="flex items-center gap-2">
                        <Sparkles className="size-5 text-primary" />
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Pyramid knowledge ingest
                        </h1>
                    </div>
                    <p className="max-w-2xl text-sm text-muted-foreground">
                        Upload an official Pyramid PDF. We extract the text with
                        Poppler, then an AI agent maps facts into database
                        tables using list, create, and insert tools — ready for
                        RAG and operations.
                    </p>
                    <Button
                        variant="outline"
                        size="sm"
                        asChild
                        className="mt-2"
                    >
                        <Link href="/pyramid/knowledge">
                            <Table2 />
                            Browse ingested data
                        </Link>
                    </Button>
                </div>

                <Card className="overflow-hidden border-sidebar-border/70 shadow-none">
                    <CardHeader className="border-b border-sidebar-border/50 bg-muted/20">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <FileUp className="size-4" />
                            Upload PDF
                        </CardTitle>
                        <CardDescription>
                            Floor plans, capacity sheets, equipment lists,
                            policies — PDF only, up to 20&nbsp;MB.
                        </CardDescription>
                    </CardHeader>

                    <CardContent className="pt-6">
                        <form onSubmit={submit} className="space-y-5">
                            <div
                                role="button"
                                tabIndex={0}
                                onKeyDown={(event) => {
                                    if (
                                        event.key === 'Enter' ||
                                        event.key === ' '
                                    ) {
                                        inputRef.current?.click();
                                    }
                                }}
                                onClick={() => inputRef.current?.click()}
                                onDragOver={(event) => {
                                    event.preventDefault();
                                    setDragActive(true);
                                }}
                                onDragLeave={() => setDragActive(false)}
                                onDrop={(event) => {
                                    event.preventDefault();
                                    setDragActive(false);
                                    const dropped =
                                        event.dataTransfer.files?.[0] ?? null;

                                    if (dropped?.type === 'application/pdf') {
                                        pickFile(dropped);
                                    }
                                }}
                                className={[
                                    'flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-12 text-center transition-colors',
                                    dragActive
                                        ? 'border-primary bg-primary/5'
                                        : 'border-sidebar-border/70 hover:border-primary/40 hover:bg-muted/30',
                                ].join(' ')}
                            >
                                <FileUp className="mb-3 size-10 text-muted-foreground" />
                                <p className="text-sm font-medium">
                                    {file
                                        ? file.name
                                        : 'Drop a Pyramid PDF here'}
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    or click to browse
                                </p>
                                <input
                                    ref={inputRef}
                                    type="file"
                                    accept="application/pdf"
                                    className="hidden"
                                    onChange={(event) =>
                                        pickFile(
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                />
                            </div>

                            {error && (
                                <p className="rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">
                                    {error}
                                </p>
                            )}

                            <Button
                                type="submit"
                                disabled={!file || processing}
                                className="w-full sm:w-auto"
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="animate-spin" />
                                        Extracting &amp; structuring…
                                    </>
                                ) : (
                                    <>
                                        <Sparkles />
                                        Ingest document
                                    </>
                                )}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {result && (
                    <div className="grid gap-4 md:grid-cols-2">
                        <Card className="border-sidebar-border/70 shadow-none md:col-span-2">
                            <CardHeader>
                                <CardTitle className="text-lg">
                                    Agent summary
                                </CardTitle>
                                <CardDescription>
                                    {result.character_count.toLocaleString()}{' '}
                                    characters extracted from the PDF
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm leading-relaxed whitespace-pre-wrap">
                                    {result.summary}
                                </p>
                            </CardContent>
                        </Card>

                        <Card className="border-sidebar-border/70 shadow-none">
                            <CardHeader>
                                <CardTitle className="text-lg">
                                    Extract preview
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <pre className="max-h-64 overflow-auto rounded-lg bg-muted/40 p-4 text-xs leading-relaxed whitespace-pre-wrap">
                                    {result.extract_preview}
                                </pre>
                            </CardContent>
                        </Card>

                        <Card className="border-sidebar-border/70 shadow-none">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Table2 className="size-4" />
                                    Tool activity
                                </CardTitle>
                                <CardDescription>
                                    {result.tool_activity.length} tool call
                                    {result.tool_activity.length === 1
                                        ? ''
                                        : 's'}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {result.tool_activity.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        No tools were invoked.
                                    </p>
                                ) : (
                                    result.tool_activity.map(
                                        (activity, index) => (
                                            <div
                                                key={`${activity.name}-${index}`}
                                                className="rounded-lg border border-sidebar-border/60 p-3"
                                            >
                                                <Badge
                                                    variant="secondary"
                                                    className="mb-2"
                                                >
                                                    {toolLabels[
                                                        activity.name
                                                    ] ?? activity.name}
                                                </Badge>
                                                <pre className="max-h-32 overflow-auto text-xs whitespace-pre-wrap text-muted-foreground">
                                                    {activity.result}
                                                </pre>
                                            </div>
                                        ),
                                    )
                                )}
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>
        </>
    );
}
