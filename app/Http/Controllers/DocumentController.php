<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentOcrService;
use App\Services\OcrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(
        private readonly OcrService $ocr,
        private readonly DocumentOcrService $documentOcr,
    ) {}

    /**
     * List the uploaded documents (the library).
     */
    public function index(): Response
    {
        $documents = Document::query()
            ->latest()
            ->get(['id', 'title', 'source_type', 'page_count', 'created_at']);

        return Inertia::render('documents/index', [
            'documents' => $documents,
        ]);
    }

    /**
     * Accept an image or PDF, OCR it, and store it in the library.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,webp,gif,bmp,tiff', 'max:20480'],
        ]);

        $file = $request->file('file');
        $isPdf = strtolower((string) $file->getClientOriginalExtension()) === 'pdf'
            || $file->getMimeType() === 'application/pdf';

        $fullText = $isPdf
            ? $this->documentOcr->extractTextFromFile($file)
            : $this->ocr->extractTextFromFile($file);

        $document = Document::create([
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $file->getClientOriginalName(),
            'source_type' => $isPdf ? 'pdf' : 'image',
            'page_count' => $isPdf ? max(1, substr_count($fullText, "\n\n") + 1) : 1,
            'full_text' => $fullText,
        ]);

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Document uploaded and text extracted.');
    }

    /**
     * Show a single document and its extracted text.
     */
    public function show(Document $document): Response
    {
        return Inertia::render('documents/show', [
            'document' => $document->only([
                'id', 'title', 'original_filename', 'source_type', 'page_count', 'full_text', 'created_at',
            ]),
        ]);
    }
}
