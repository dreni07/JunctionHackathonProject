import { Head, Link } from '@inertiajs/react';

type DocumentDetail = {
    id: number;
    title: string;
    original_filename: string;
    source_type: string;
    page_count: number;
    full_text: string;
    created_at: string;
};

export default function DocumentsShow({ document }: { document: DocumentDetail }) {
    return (
        <>
            <Head title={document.title} />
            <div className="mx-auto min-h-screen max-w-3xl space-y-6 p-6 text-[#1b1b18] dark:text-[#EDEDEC]">
                <Link href="/documents" className="text-sm text-neutral-500 hover:underline">
                    ← Back to library
                </Link>

                <header className="space-y-1">
                    <h1 className="text-2xl font-semibold">{document.title}</h1>
                    <p className="text-xs text-neutral-500 uppercase">
                        {document.source_type} · {document.page_count} page{document.page_count === 1 ? '' : 's'} ·{' '}
                        {document.original_filename}
                    </p>
                </header>

                <div className="space-y-2">
                    <h2 className="text-sm font-medium text-neutral-500">Extracted text</h2>
                    <pre className="overflow-auto rounded-md bg-neutral-100 p-4 text-sm whitespace-pre-wrap dark:bg-neutral-900">
                        {document.full_text || '(no text detected)'}
                    </pre>
                </div>
            </div>
        </>
    );
}
