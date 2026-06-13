<?php

namespace App\Http\Controllers;

use App\Services\DocumentOcrService;
use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class OcrController extends Controller
{
    public function __construct(
        private readonly OcrService $ocr,
        private readonly DocumentOcrService $documentOcr,
    ) {}

    /**
     * Render the landing page with the OCR upload component.
     */
    public function index(): Response
    {
        return Inertia::render('welcome');
    }

    /**
     * Run OCR on the uploaded image and return the extracted text.
     */
    public function extract(Request $request): JsonResponse
    {
        $validator = Validator::make(
            ['image' => $request->file('image')],
            ['image' => ['required', 'image', 'max:10240']],
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('image'),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $text = $this->ocr->extractTextFromFile($request->file('image'));

        return response()->json([
            'response' => $text,
        ]);
    }

    /**
     * Run OCR on the uploaded PDF document and return the extracted text.
     */
    public function extractDocument(Request $request): JsonResponse
    {
        $validator = Validator::make(
            ['document' => $request->file('document')],
            ['document' => ['required', 'file', 'mimes:pdf', 'max:20480']],
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('document'),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $text = $this->documentOcr->extractTextFromFile($request->file('document'));

        return response()->json([
            'response' => $text,
        ]);
    }
}
