<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\Process\Process;

class OcrService
{
    public function __construct(
        private readonly string $binary = 'tesseract',
        private readonly string $defaultLanguage = 'eng',
        private readonly int $timeout = 60,
        private readonly bool $preprocess = true,
        private readonly string $imagemagickBinary = 'convert',
    ) {}

    /**
     * Extract text from an uploaded image file.
     */
    public function extractTextFromFile(UploadedFile $image, ?string $language = null): string
    {
        $path = $image->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read the uploaded image.');
        }

        return $this->extractText($path, $language);
    }

    /**
     * Run Tesseract against an image on disk and return the recognized text.
     */
    public function extractText(string $imagePath, ?string $language = null): string
    {
        $target = $this->preprocess ? $this->preprocessImage($imagePath) : $imagePath;

        try {
            return $this->runTesseract($target, $language);
        } finally {
            if ($target !== $imagePath && is_file($target)) {
                @unlink($target);
            }
        }
    }

    private function runTesseract(string $imagePath, ?string $language): string
    {
        // PSM 3 = automatic layout (best for paragraphs/documents).
        // PSM 6 = assume a single uniform block (best for short labels and
        // large headline text that defeats automatic layout analysis).
        // Return the first mode that yields any text.
        foreach (['3', '6'] as $pageSegmentationMode) {
            $text = $this->tesseract($imagePath, $language, $pageSegmentationMode);

            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function tesseract(string $imagePath, ?string $language, string $pageSegmentationMode): string
    {
        $process = new Process([
            $this->binary,
            $imagePath,
            'stdout',
            '-l', $language ?? $this->defaultLanguage,
            '--psm', $pageSegmentationMode,
        ]);

        $process->setTimeout($this->timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Tesseract OCR failed: '.trim($process->getErrorOutput()));
        }

        return trim($process->getOutput());
    }

    /**
     * Normalize the image for OCR: grayscale, stretch contrast, binarize with
     * Otsu, and ensure dark text sits on a light background (Tesseract's
     * preferred input). Falls back to the original image if ImageMagick fails.
     */
    private function preprocessImage(string $sourcePath): string
    {
        $output = tempnam(sys_get_temp_dir(), 'ocr_').'.png';

        $process = new Process([
            $this->imagemagickBinary,
            $sourcePath,
            '-colorspace', 'Gray',
            '-auto-level',
            '-auto-threshold', 'OTSU',
            $output,
        ]);
        $process->setTimeout($this->timeout);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($output) || filesize($output) === 0) {
            @unlink($output);

            return $sourcePath;
        }

        $this->ensureDarkTextOnLightBackground($output);

        return $output;
    }

    /**
     * If the binarized image is mostly dark (e.g. light text on a dark
     * background), invert it so text becomes dark on light. Always pads with a
     * white border, which improves Tesseract's edge detection.
     */
    private function ensureDarkTextOnLightBackground(string $path): void
    {
        $mean = new Process([$this->imagemagickBinary, $path, '-format', '%[fx:mean]', 'info:']);
        $mean->run();

        $brightness = (float) trim($mean->getOutput());

        $arguments = [$this->imagemagickBinary, $path];

        if ($brightness < 0.5) {
            $arguments[] = '-negate';
        }

        array_push($arguments, '-bordercolor', 'white', '-border', '20', $path);

        (new Process($arguments))->run();
    }
}
