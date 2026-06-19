<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDF text extraction (Poppler)
    |--------------------------------------------------------------------------
    |
    | pdftotext extracts selectable text directly from PDFs — faster and more
    | accurate than OCR for digital documents. OCR remains the fallback.
    |
    */

    'pdftotext_binary' => env('PDFTOTEXT_BINARY', 'pdftotext'),

    'timeout' => (int) env('PDF_TEXT_TIMEOUT', 120),

];
