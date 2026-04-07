<?php

namespace App\Services\PrintCheck;

use App\Models\OpenAIUsageLog;
use App\Models\PrintAnalysis;
use App\Models\PrintAnalysisResult;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

/**
 * Servicio principal que orquesta el análisis completo de un archivo PDF.
 * Coordina: extracción de metadatos → llamada a IA → persistencia de resultados.
 */
class PdfAnalysisService
{
    public function __construct(
        private readonly PdfMetadataService    $metadataService,
        private readonly OpenAIAnalysisService $openAIService,
    ) {}

    /**
     * Ejecuta el análisis completo de un PrintAnalysis registrado.
     * Actualiza el estado del registro y persiste resultados y uso de tokens.
     *
     * @throws \Throwable
     */
    public function analyze(PrintAnalysis $analysis): void
    {
        $storagePath = storage_path("app/private/{$analysis->storage_path}");

        if (!file_exists($storagePath)) {
            throw new \RuntimeException("Archivo no encontrado: {$storagePath}");
        }

        // 1. Extraer metadatos con el parser PHP (sin herramientas binarias)
        $metadata = $this->metadataService->extract($storagePath);

        // Actualizar page_count si lo obtenemos
        if (($metadata['page_count'] ?? 0) > 0) {
            $analysis->update(['page_count' => $metadata['page_count']]);
        }

        // 2. Enviar a OpenAI y obtener resultados
        $result = $this->openAIService->analyze(
            pdfPath:  $storagePath,
            filename: $analysis->original_filename,
            metadata: $metadata,
        );

        // 3. Persistir resultados
        foreach ($result['checks'] as $check) {
            PrintAnalysisResult::create([
                'print_analysis_id' => $analysis->id,
                'check_type'        => $check->checkType,
                'status'            => $check->status,
                'summary'           => $check->summary,
                'details'           => $check->details,
            ]);
        }

        // 4. Persistir consumo de tokens
        OpenAIUsageLog::create([
            'print_analysis_id' => $analysis->id,
            ...$result['usage'],
        ]);

        // 5. Marcar análisis como completado
        $analysis->update([
            'status'         => 'completed',
            'openai_file_id' => $result['openai_file_id'],
            'completed_at'   => now(),
        ]);

        Log::info('PrintAnalysis completado', [
            'analysis_id' => $analysis->id,
            'tokens'      => $result['usage']['total_tokens'],
            'cost_usd'    => $result['usage']['cost_usd'],
        ]);
    }
}
