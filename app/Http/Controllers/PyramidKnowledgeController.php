<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PyramidKnowledgeIngestRequest;
use App\Services\PyramidKnowledgeIngestionService;
use App\Services\PyramidTableRegistry;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PyramidKnowledgeController extends Controller
{
    public function __construct(
        private readonly PyramidKnowledgeIngestionService $ingestion,
        private readonly PyramidTableRegistry $tables,
    ) {}

    public function index(): Response
    {
        return Inertia::render('pyramid/ingest');
    }

    public function explore(): Response
    {
        $tables = $this->tables->exportKnowledgeSnapshot();

        return Inertia::render('pyramid/explore', [
            'tables' => $tables,
            'totalTables' => count($tables),
            'totalRows' => collect($tables)->sum('row_count'),
        ]);
    }

    public function store(PyramidKnowledgeIngestRequest $request): JsonResponse
    {
        try {
            $result = $this->ingestion->ingest($request->file('file'));

            return response()->json([
                'success' => true,
                ...$result,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
