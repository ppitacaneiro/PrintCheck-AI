<?php

namespace App\Services\PrintCheck;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

/**
 * Envía el PDF a la API de OpenAI y retorna los resultados del análisis de preimpresión.
 * Usa el endpoint Files para subir el PDF y después Chat Completions con gpt-4o.
 */
class OpenAIAnalysisService
{
    // Coste por 1 000 tokens (gpt-4o, precios 2025)
    private const COST_INPUT_PER_1K  = 0.0025;  // $0.0025 / 1K prompt tokens
    private const COST_OUTPUT_PER_1K = 0.010;   // $0.010  / 1K completion tokens

    private const SYSTEM_PROMPT = <<<'PROMPT'
Eres un experto en preimpresión y artes gráficas. Tu tarea es analizar archivos PDF destinados a impresión profesional
y verificar su calidad según los estándares de la industria.

Para cada comprobación devuelves ÚNICAMENTE un objeto JSON con exactamente esta estructura:
{
  "resolution":      { "status": "pass|warn|fail", "summary": "…", "details": {} },
  "color_profile":   { "status": "pass|warn|fail", "summary": "…", "details": {} },
  "embedded_fonts":  { "status": "pass|warn|fail", "summary": "…", "details": {} },
  "bleed_area":      { "status": "pass|warn|fail", "summary": "…", "details": {} },
  "safety_margins":  { "status": "pass|warn|fail", "summary": "…", "details": {} },
  "transparency":    { "status": "pass|warn|fail", "summary": "…", "details": {} }
}

Criterios:
- resolution:     pass ≥ 300 dpi, warn 200-299 dpi (imágenes pixeladas visibles en zoom), fail < 200 dpi
- color_profile:  pass = CMYK o ICC con perfil adecuado, warn = RGB pero convertible, fail = sin perfil definido
- embedded_fonts: pass = todas las fuentes embebidas, warn = alguna no embebida, fail = fuentes faltantes
- bleed_area:     pass ≥ 3 mm sangrado, warn 1-2 mm, fail = sin sangrado
- safety_margins: pass = contenido a > 5 mm del borde, warn 3-5 mm, fail < 3 mm
- transparency:   pass = sin transparencias o todas aplanadas, warn = transparencias sin aplanar no críticas, fail = transparencias sin aplanar en elementos clave

El campo "details" puede incluir claves específicas como páginas afectadas, valores exactos, fuentes problemáticas, etc.
No incluyas ningún texto fuera del JSON. Responde sólo con el JSON.
PROMPT;

    /**
     * @return array{checks: list<CheckResult>, usage: array{model: string, prompt_tokens: int, completion_tokens: int, total_tokens: int, cost_usd: float}}
     */
    public function analyze(string $pdfPath, string $filename, array $metadata): array
    {
        // 1. Subir el PDF a OpenAI Files API
        $fileId = $this->uploadFile($pdfPath, $filename);

        // 2. Construir el prompt con contexto extraído del PDF
        $userPrompt = $this->buildUserPrompt($filename, $metadata);

        // 3. Llamar a Chat Completions con el archivo adjunto
        $response = OpenAI::chat()->create([
            'model' => config('openai.model', 'gpt-4o'),
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => self::SYSTEM_PROMPT,
                ],
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $userPrompt,
                        ],
                        [
                            'type' => 'file',
                            'file' => ['file_id' => $fileId],
                        ],
                    ],
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.1,
            'max_tokens'      => 2048,
        ]);

        // 4. Limpiar el archivo de OpenAI (no factura almacenamiento tras 24h, pero buena práctica)
        $this->deleteFile($fileId);

        // 5. Parsear resultados
        $raw    = $response->choices[0]->message->content;
        $checks = $this->parseChecks($raw);
        $usage  = $this->calculateUsage($response->usage, config('openai.model', 'gpt-4o'));

        return [
            'openai_file_id' => $fileId,
            'checks'         => $checks,
            'usage'          => $usage,
        ];
    }

    /** Sube el PDF a la Files API de OpenAI y devuelve el file_id. */
    private function uploadFile(string $pdfPath, string $filename): string
    {
        $fileHandle = fopen($pdfPath, 'r');
        if ($fileHandle === false) {
            throw new \RuntimeException("No se puede abrir el archivo: {$pdfPath}");
        }

        try {
            $response = OpenAI::files()->upload([
                'purpose' => 'assistants',
                'file'    => $fileHandle,
            ]);
        } finally {
            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }
        }

        return $response->id;
    }

    /** Elimina un archivo temporal de OpenAI. */
    private function deleteFile(string $fileId): void
    {
        try {
            OpenAI::files()->delete($fileId);
        } catch (\Throwable $e) {
            // No crítico, continuar
            Log::warning('No se pudo eliminar el archivo de OpenAI', ['file_id' => $fileId, 'error' => $e->getMessage()]);
        }
    }

    private function buildUserPrompt(string $filename, array $metadata): string
    {
        $pages   = $metadata['page_count'] ?? '?';
        $widthMm = $metadata['width_mm']   ?? '?';
        $heightMm= $metadata['height_mm'] ?? '?';
        $fonts   = implode(', ', $metadata['fonts'] ?? []) ?: 'No detectadas';
        $spaces  = implode(', ', $metadata['color_spaces'] ?? []) ?: 'No detectados';
        $pdfVer  = $metadata['pdf_version'] ?? 'desconocida';

        return <<<TEXT
Analiza el siguiente archivo PDF para impresión profesional y devuelve el JSON de comprobaciones.

Datos extraídos del PDF:
- Nombre: {$filename}
- Versión PDF: {$pdfVer}
- Páginas: {$pages}
- Tamaño página 1: {$widthMm} mm × {$heightMm} mm
- Fuentes detectadas: {$fonts}
- Espacios de color detectados: {$spaces}

Examina el contenido visual y los metadatos del PDF adjunto para realizar un análisis completo.
TEXT;
    }

    /** Convierte el JSON de OpenAI en lista de CheckResult. */
    private function parseChecks(string $raw): array
    {
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new \RuntimeException("La respuesta de OpenAI no es JSON válido: {$raw}");
        }

        $checks = [];
        $validTypes = ['resolution', 'color_profile', 'embedded_fonts', 'bleed_area', 'safety_margins', 'transparency'];

        foreach ($validTypes as $type) {
            $item = $data[$type] ?? [];
            $checks[] = new CheckResult(
                checkType: $type,
                status:    in_array($item['status'] ?? '', ['pass', 'warn', 'fail'], true)
                               ? $item['status']
                               : 'warn',
                summary:   $item['summary'] ?? 'Sin información',
                details:   is_array($item['details'] ?? null) ? $item['details'] : [],
            );
        }

        return $checks;
    }

    private function calculateUsage(object $usage, string $model): array
    {
        $prompt     = $usage->promptTokens ?? 0;
        $completion = $usage->completionTokens ?? 0;
        $total      = $usage->totalTokens ?? ($prompt + $completion);

        $cost = ($prompt / 1000 * self::COST_INPUT_PER_1K)
              + ($completion / 1000 * self::COST_OUTPUT_PER_1K);

        return [
            'model'             => $model,
            'prompt_tokens'     => $prompt,
            'completion_tokens' => $completion,
            'total_tokens'      => $total,
            'cost_usd'          => round($cost, 6),
        ];
    }
}
