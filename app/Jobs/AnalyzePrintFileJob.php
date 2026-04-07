<?php

namespace App\Jobs;

use App\Models\PrintAnalysis;
use App\Services\PrintCheck\PdfAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzePrintFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Número máximo de reintentos ante error */
    public int $tries = 3;

    /** Espera exponencial entre reintentos (segundos) */
    public int $backoff = 30;

    /** Tiempo máximo de ejecución del job (segundos): 5 minutos */
    public int $timeout = 300;

    public function __construct(public readonly PrintAnalysis $analysis) {}

    public function handle(PdfAnalysisService $service): void
    {
        Log::info('AnalyzePrintFileJob iniciado', ['analysis_id' => $this->analysis->id]);

        // Marcar como procesando
        $this->analysis->update(['status' => 'processing']);

        try {
            $service->analyze($this->analysis);
        } catch (\Throwable $e) {
            Log::error('AnalyzePrintFileJob falló', [
                'analysis_id' => $this->analysis->id,
                'error'       => $e->getMessage(),
                'attempt'     => $this->attempts(),
            ]);

            // Volver a pending para que el frontend no muestre "en proceso" durante el backoff
            $this->analysis->update(['status' => 'pending']);

            throw $e; // Re-lanzar para que Laravel gestione los reintentos
        }
    }

    /** Callback final cuando el job agota todos los reintentos */
    public function failed(\Throwable $e): void
    {
        $this->analysis->update([
            'status'        => 'failed',
            'error_message' => $e->getMessage(),
        ]);

        Log::error('AnalyzePrintFileJob agotó reintentos', [
            'analysis_id' => $this->analysis->id,
            'error'       => $e->getMessage(),
        ]);
    }
}
