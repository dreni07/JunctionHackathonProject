<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Extract text from PDFs using Poppler's pdftotext, with OCR fallback.
 */
class PdfTextExtractor
{
    public function __construct(
        private readonly DocumentOcrService $documentOcr,
        private readonly string $pdftotextBinary = 'pdftotext',
        private readonly int $timeout = 120,
    ) {}

    public function extractFromFile(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read the uploaded PDF.');
        }

        return $this->extract($path);
    }

    public function extract(string $pdfPath): string
    {
        $text = $this->extractWithPdftotext($pdfPath);

        if ($this->isUsableText($text)) {
            return $text;
        }

        Log::info('PDF text extraction fell back to OCR.', ['path' => basename($pdfPath)]);

        return $this->documentOcr->extractText($pdfPath);
    }

    private function extractWithPdftotext(string $pdfPath): string
    {
        $outputPath = tempnam(sys_get_temp_dir(), 'pdftext_');

        if ($outputPath === false) {
            throw new RuntimeException('Unable to create a temporary file for PDF extraction.');
        }

        $textPath = $outputPath.'.txt';
        @rename($outputPath, $textPath);

        try {
            $process = new Process([
                $this->pdftotextBinary,
                '-layout',
                '-enc', 'UTF-8',
                $pdfPath,
                $textPath,
            ]);

            $process->setTimeout($this->timeout);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('pdftotext failed: '.trim($process->getErrorOutput()));
            }

            $contents = @file_get_contents($textPath);

            return trim(is_string($contents) ? $contents : '');
        } finally {
            if (is_file($textPath)) {
                @unlink($textPath);
            }
        }
    }

    private function isUsableText(string $text): bool
    {
        return strlen(preg_replace('/\s+/u', '', $text) ?? '') >= 40;
    }
}
