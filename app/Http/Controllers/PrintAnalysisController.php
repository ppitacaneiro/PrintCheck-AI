<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzePrintFileJob;
use App\Models\PrintAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class PrintAnalysisController extends Controller
{
    private const MAX_FILE_MB = 50;

    /**
     * Subir un PDF e iniciar el análisis en cola.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                File::types(['pdf'])
                    ->max(self::MAX_FILE_MB * 1024),
            ],
        ]);

        $uploadedFile = $request->file('file');

        // Almacenar en storage/app/private/print_analyses/{user_id}/
        $relativePath = $uploadedFile->store(
            "print_analyses/" . Auth::id(),
            'private'
        );

        $analysis = PrintAnalysis::create([
            'user_id'           => Auth::id(),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'storage_path'      => $relativePath,
            'file_size_bytes'   => $uploadedFile->getSize(),
            'status'            => 'pending',
        ]);

        // Despachar en la cola de análisis
        AnalyzePrintFileJob::dispatch($analysis);

        return response()->json([
            'id'       => $analysis->id,
            'status'   => $analysis->status,
            'filename' => $analysis->original_filename,
        ], 201);
    }

    /**
     * Listar análisis del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $analyses = PrintAnalysis::query()
            ->where('user_id', Auth::id())
            ->with(['results', 'usageLog'])
            ->latest()
            ->paginate(10);

        return response()->json($analyses);
    }

    /**
     * Estado de un análisis concreto (para polling).
     */
    public function show(PrintAnalysis $printAnalysis): JsonResponse
    {
        $this->authorize('view', $printAnalysis);

        $printAnalysis->load(['results', 'usageLog']);

        return response()->json($this->formatAnalysis($printAnalysis));
    }

    /**
     * Estadísticas del dashboard del usuario.
     */
    public function stats(): JsonResponse
    {
        $userId = Auth::id();

        $total     = PrintAnalysis::where('user_id', $userId)->where('status', 'completed')->count();
        $withFails = PrintAnalysis::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereHas('results', fn ($q) => $q->where('status', 'fail'))
            ->count();
        $okRate    = $total > 0 ? round((($total - $withFails) / $total) * 100) : 0;

        $totalErrors = \App\Models\PrintAnalysisResult::whereHas(
            'analysis',
            fn ($q) => $q->where('user_id', $userId)->where('status', 'completed')
        )->where('status', 'fail')->count();

        return response()->json([
            'total_analyses' => $total,
            'total_errors'   => $totalErrors,
            'ok_rate'        => $okRate,
        ]);
    }

    private function formatAnalysis(PrintAnalysis $analysis): array
    {
        return [
            'id'                => $analysis->id,
            'filename'          => $analysis->original_filename,
            'file_size_bytes'   => $analysis->file_size_bytes,
            'page_count'        => $analysis->page_count,
            'status'            => $analysis->status,
            'error_message'     => $analysis->error_message,
            'completed_at'      => $analysis->completed_at?->toIso8601String(),
            'created_at'        => $analysis->created_at->toIso8601String(),
            'results'           => $analysis->results->map(fn ($r) => [
                'check_type' => $r->check_type,
                'status'     => $r->status,
                'summary'    => $r->summary,
                'details'    => $r->details,
            ])->values(),
            'usage'             => $analysis->usageLog ? [
                'model'             => $analysis->usageLog->model,
                'prompt_tokens'     => $analysis->usageLog->prompt_tokens,
                'completion_tokens' => $analysis->usageLog->completion_tokens,
                'total_tokens'      => $analysis->usageLog->total_tokens,
                'cost_usd'          => $analysis->usageLog->cost_usd,
            ] : null,
        ];
    }
}
