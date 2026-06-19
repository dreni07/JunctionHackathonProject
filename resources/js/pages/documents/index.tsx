import { Head, Link, useForm } from '@inertiajs/react';

type DocumentListItem = {
    id: number;
    title: string;
    source_type: string;
    page_count: number;
    created_at: string;
};

export default function DocumentsIndex({
    documents,
}: {
    documents: DocumentListItem[];
}) {
    const { setData, post, processing, errors } = useForm<{
        file: File | null;
    }>({ file: null });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/documents', { forceFormData: true });
    }

    return (
        <>
            <Head title="Library" />
            <div className="mx-auto min-h-screen max-w-3xl space-y-8 p-6 text-[#1b1b18] dark:text-[#EDEDEC]">
                <header className="space-y-1">
                    <h1 className="text-2xl font-semibold">Document Library</h1>
                    <p className="text-sm text-neutral-500">
                        Upload a study note, paper, or article (image or PDF).
                        We extract the text and save it.
                    </p>
                </header>

                <form
                    onSubmit={submit}
                    className="space-y-4 rounded-xl border border-neutral-200 p-6 dark:border-neutral-800"
                >
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        onChange={(e) =>
                            setData('file', e.target.files?.[0] ?? null)
                        }
                        className="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-neutral-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-neutral-700 dark:file:bg-white dark:file:text-black"
                    />
                    {errors.file && (
                        <p className="text-sm text-red-600">{errors.file}</p>
                    )}

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50 dark:bg-white dark:text-black"
                    >
                        {processing ? 'Uploading…' : 'Upload & extract'}
                    </button>
                </form>

                <section className="space-y-3">
                    <h2 className="text-sm font-medium text-neutral-500">
                        {documents.length} document
                        {documents.length === 1 ? '' : 's'}
                    </h2>

                    {documents.length === 0 ? (
                        <p className="text-sm text-neutral-500">
                            Nothing uploaded yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-neutral-200 rounded-xl border border-neutral-200 dark:divide-neutral-800 dark:border-neutral-800">
                            {documents.map((doc) => (
                                <li key={doc.id}>
                                    <Link
                                        href={`/documents/${doc.id}`}
                                        className="flex items-center justify-between gap-4 p-4 hover:bg-neutral-50 dark:hover:bg-neutral-900"
                                    >
                                        <span className="truncate font-medium">
                                            {doc.title}
                                        </span>
                                        <span className="shrink-0 text-xs text-neutral-500 uppercase">
                                            {doc.source_type} · {doc.page_count}
                                            p
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}
