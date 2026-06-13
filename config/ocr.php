<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tesseract OCR
    |--------------------------------------------------------------------------
    |
    | Configuration for the Tesseract binary the OcrService shells out to.
    | The binary is provided by tesseract-ocr (see Dockerfile / nixpacks.toml).
    |
    */

    'binary' => env('TESSERACT_BINARY', 'tesseract'),

    'language' => env('TESSERACT_LANGUAGE', 'eng'),

    'timeout' => (int) env('TESSERACT_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Image pre-processing
    |--------------------------------------------------------------------------
    |
    | Before running OCR the image is normalized with ImageMagick (grayscale,
    | contrast stretch, Otsu binarization, polarity fix). This dramatically
    | improves recognition of light-on-dark or colored-background text.
    |
    */

    'preprocess' => (bool) env('OCR_PREPROCESS', true),

    'imagemagick_binary' => env('IMAGEMAGICK_BINARY', 'convert'),

    /*
    |--------------------------------------------------------------------------
    | Document (PDF) OCR
    |--------------------------------------------------------------------------
    |
    | PDFs are rasterized to images with poppler-utils (pdftoppm) before each
    | page is run through Tesseract. Higher DPI gives better accuracy at the
    | cost of speed.
    |
    */

    'pdftoppm_binary' => env('PDFTOPPM_BINARY', 'pdftoppm'),

    'document_dpi' => (int) env('OCR_DOCUMENT_DPI', 300),

    'document_timeout' => (int) env('OCR_DOCUMENT_TIMEOUT', 120),

];
