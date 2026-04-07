<?php

namespace Tests\Unit;

use App\Services\PrintCheck\CheckResult;
use App\Services\PrintCheck\OpenAIAnalysisService;
use Tests\TestCase;

class OpenAIAnalysisServiceTest extends TestCase
{
    /**
     * Verifica que CheckResult mantiene correctamente los valores de sus campos.
     */
    public function test_check_result_stores_values_correctly(): void
    {
        $check = new CheckResult(
            checkType: 'resolution',
            status: 'fail',
            summary: 'Resolución insuficiente en página 1',
            details: ['affected_pages' => [1], 'min_dpi' => 72],
        );

        $this->assertSame('resolution', $check->checkType);
        $this->assertSame('fail', $check->status);
        $this->assertSame('Resolución insuficiente en página 1', $check->summary);
        $this->assertSame([1], $check->details['affected_pages']);
    }

    /**
     * El servicio debe rechazar una respuesta JSON malformada.
     */
    public function test_parse_throws_on_invalid_json(): void
    {
        // Usamos reflexión para acceder al método privado parseChecks
        $service = new OpenAIAnalysisService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseChecks');
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/no es JSON válido/');

        $method->invoke($service, 'esto no es json');
    }

    /**
     * Cuando el JSON tiene un status inválido, se usa 'warn' como fallback.
     */
    public function test_parse_uses_warn_as_fallback_for_invalid_status(): void
    {
        $service = new OpenAIAnalysisService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseChecks');
        $method->setAccessible(true);

        $json = json_encode([
            'resolution'     => ['status' => 'INVALID', 'summary' => 'test', 'details' => []],
            'color_profile'  => ['status' => 'pass',    'summary' => 'ok',   'details' => []],
            'embedded_fonts' => ['status' => 'pass',    'summary' => 'ok',   'details' => []],
            'bleed_area'     => ['status' => 'pass',    'summary' => 'ok',   'details' => []],
            'safety_margins' => ['status' => 'pass',    'summary' => 'ok',   'details' => []],
            'transparency'   => ['status' => 'pass',    'summary' => 'ok',   'details' => []],
        ]);

        $results = $method->invoke($service, $json);

        $this->assertCount(6, $results);
        $resolutionResult = array_filter($results, fn ($r) => $r->checkType === 'resolution');
        $this->assertSame('warn', array_values($resolutionResult)[0]->status);
    }

    /**
     * El cálculo de coste sigue la fórmula correcta para gpt-4o.
     */
    public function test_calculate_usage_computes_cost_correctly(): void
    {
        $service = new OpenAIAnalysisService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('calculateUsage');
        $method->setAccessible(true);

        // Simular objeto usage de OpenAI
        $usageStub = new class {
            public int $promptTokens     = 2000;
            public int $completionTokens = 500;
            public int $totalTokens      = 2500;
        };

        $result = $method->invoke($service, $usageStub, 'gpt-4o');

        // 2000/1000 * 0.0025 + 500/1000 * 0.010 = 0.005 + 0.005 = 0.01
        $this->assertEqualsWithDelta(0.01, $result['cost_usd'], 0.000001);
        $this->assertSame(2000, $result['prompt_tokens']);
        $this->assertSame(500,  $result['completion_tokens']);
        $this->assertSame(2500, $result['total_tokens']);
        $this->assertSame('gpt-4o', $result['model']);
    }
}
