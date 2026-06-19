import { Head } from '@inertiajs/react';
import { useState } from 'react';

function getCookie(name: string): string {
    const match = document.cookie.match(
        new RegExp('(^|; )' + name + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[2]) : '';
}

type UploaderProps = {
    title: string;
    description: string;
    endpoint: string;
    fieldName: string;
    accept: string;
};

function Uploader({
    title,
    description,
    endpoint,
    fieldName,
    accept,
}: UploaderProps) {
    const [file, setFile] = useState<File | null>(null);
    const [fileName, setFileName] = useState('');
    const [text, setText] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [processing, setProcessing] = useState(false);

    function handleFile(event: React.ChangeEvent<HTMLInputElement>) {
        const selected = event.target.files?.[0] ?? null;
        setFile(selected);
        setFileName(selected?.name ?? '');
        setText(null);
        setError(null);
    }

    async function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!file) {
            setError('Please choose a file first.');

            return;
        }

        setProcessing(true);
        setText(null);
        setError(null);

        const body = new FormData();
        body.append(fieldName, file);

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
                },
                body,
            });

            const data = await res.json();

            if (!res.ok) {
                setError(
                    data?.errors?.[fieldName]?.[0] ??
                        data?.message ??
                        'Failed to extract text.',
                );

                return;
            }

            setText(data.response ?? '');
        } catch {
            setError('Network error — could not reach the server.');
        } finally {
            setProcessing(false);
        }
    }

    return (
        <div className="space-y-4">
            <div className="space-y-1">
                <h2 className="text-lg font-semibold">{title}</h2>
                <p className="text-sm text-neutral-500">{description}</p>
            </div>

            <form
                onSubmit={submit}
                className="space-y-4 rounded-xl border border-neutral-200 p-6 dark:border-neutral-800"
            >
                <label className="block">
                    <input
                        type="file"
                        accept={accept}
                        onChange={handleFile}
                        className="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-neutral-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-neutral-700 dark:file:bg-white dark:file:text-black"
                    />
                    {fileName && (
                        <span className="mt-1 block text-xs text-neutral-500">
                            {fileName}
                        </span>
                    )}
                </label>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50 dark:bg-white dark:text-black"
                >
                    {processing ? 'Extracting…' : 'Extract text'}
                </button>

                {error && <p className="text-sm text-red-600">{error}</p>}
            </form>

            {text !== null && (
                <div className="space-y-2">
                    <h3 className="text-sm font-medium">Extracted text</h3>
                    <pre className="max-h-80 overflow-auto rounded-md bg-neutral-100 p-4 text-sm whitespace-pre-wrap dark:bg-neutral-900">
                        {text || '(no text detected)'}
                    </pre>
                </div>
            )}
        </div>
    );
}

export default function Welcome() {
    return (
        <>
            <Head title="OCR" />
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
                <div className="w-full max-w-xl space-y-10 py-10">
                    <div className="space-y-1 text-center">
                        <h1 className="text-2xl font-semibold">
                            Tesseract OCR
                        </h1>
                        <p className="text-sm text-neutral-500">
                            Extract text from an image or a PDF document.
                        </p>
                    </div>

                    <Uploader
                        title="Image"
                        description="Upload an image (PNG, JPG, …) and extract its text."
                        endpoint="/ocr"
                        fieldName="image"
                        accept="image/*"
                    />

                    <Uploader
                        title="Document (PDF)"
                        description="Upload a PDF; every page is rasterized and OCR'd."
                        endpoint="/ocr/document"
                        fieldName="document"
                        accept="application/pdf"
                    />
                </div>
            </div>
        </>
    );
}
