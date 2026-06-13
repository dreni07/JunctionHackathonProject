<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\Process\Process;

class DocumentOcrService
{
    public function __construct(
        private readonly OcrService $ocr,
        private readonly string $pdftoppmBinary = 'pdftoppm',
        private readonly int $dpi = 300,
        private readonly int $timeout = 120,
    ) {}

    /**
     * Extract text from an uploaded PDF document.
     */
    public function extractTextFromFile(UploadedFile $document, ?string $language = null): string
    {
        $path = $document->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read the uploaded document.');
        }

        return $this->extractText($path, $language);
    }

    /**
     * Rasterize each page of the PDF and OCR it, joining the pages together.
     */
    public function extractText(string $documentPath, ?string $language = null): string
    {
        $pages = $this->rasterizePdf($documentPath);

        try {
            $pageTexts = array_map(
                fn (string $pagePath): string => $this->ocr->extractText($pagePath, $language),
                $pages,
            );

            return trim(implode("\n\n", $pageTexts));
        } finally {
            foreach ($pages as $pagePath) {
                if (is_file($pagePath)) {
                    @unlink($pagePath);
                }
            }
        }
    }

    /**
     * Render every PDF page to a PNG and return the page image paths in order.
     *
     * @return list<string>
     */
    private function rasterizePdf(string $pdfPath): array
    {
        $prefix = tempnam(sys_get_temp_dir(), 'ocrdoc_');

        // pdftoppm writes "<prefix>-<page>.png"; remove the placeholder file.
        @unlink($prefix);

        $process = new Process([
            $this->pdftoppmBinary,
            '-png',
            '-r', (string) $this->dpi,
            $pdfPath,
            $prefix,
        ]);

        $process->setTimeout($this->timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Failed to rasterize PDF: '.trim($process->getErrorOutput()));
        }

        $pages = glob($prefix.'-*.png') ?: [];
        sort($pages, SORT_NATURAL);

        if ($pages === []) {
            throw new RuntimeException('No pages could be extracted from the PDF.');
        }

        return $pages;
    }
}
