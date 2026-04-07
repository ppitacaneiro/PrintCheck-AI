<?php

namespace Tests\Feature;

use App\Jobs\AnalyzePrintFileJob;
use App\Models\OpenAIUsageLog;
use App\Models\PrintAnalysis;
use App\Models\PrintAnalysisResult;
use App\Models\User;
use App\Services\PrintCheck\PdfAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class AnalyzePrintFileJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    /** El job llama al servicio y actualiza el estado a 'completed'. */
    public function test_job_completes_successfully(): void
    {
        $user     = User::factory()->create();
        $analysis = PrintAnalysis::factory()->create([
            'user_id'      => $user->id,
            'status'       => 'pending',
            'storage_path' => 'print_analyses/1/test.pdf',
        ]);

        // Crear un PDF falso en el storage
        Storage::disk('private')->put('print_analyses/1/test.pdf', '%PDF-1.4 fake content');

        // Mock del servicio de análisis
        $this->mock(PdfAnalysisService::class, function (MockInterface $mock) use ($analysis) {
            $mock->shouldReceive('analyze')
                ->once()
                ->with(\Mockery::on(fn ($a) => $a->id === $analysis->id))
                ->andReturnUsing(function ($a) {
                    // Simular lo que haría el servicio real
                    PrintAnalysisResult::create([
                        'print_analysis_id' => $a->id,
                        'check_type'        => 'resolution',
                        'status'            => 'pass',
                        'summary'           => 'Resolución adecuada',
                    ]);
                    OpenAIUsageLog::create([
                        'print_analysis_id' => $a->id,
                        'model'             => 'gpt-4o',
                        'prompt_tokens'     => 100,
                        'completion_tokens' => 200,
                        'total_tokens'      => 300,
                        'cost_usd'          => 0.000025,
                    ]);
                    $a->update(['status' => 'completed', 'completed_at' => now()]);
                });
        });

        $job = new AnalyzePrintFileJob($analysis);
        app()->call([$job, 'handle'], ['service' => app(PdfAnalysisService::class)]);

        $this->assertDatabaseHas('print_analyses', [
            'id'     => $analysis->id,
            'status' => 'completed',
        ]);
    }

    /** Cuando el servicio lanza una excepción, el estado pasa a 'failed' al agotar reintentos. */
    public function test_job_marks_analysis_as_failed_when_service_throws(): void
    {
        $user     = User::factory()->create();
        $analysis = PrintAnalysis::factory()->create([
            'user_id' => $user->id,
            'status'  => 'pending',
        ]);

        $this->mock(PdfAnalysisService::class, function (MockInterface $mock) {
            $mock->shouldReceive('analyze')->andThrow(new \RuntimeException('OpenAI error'));
        });

        $job = new AnalyzePrintFileJob($analysis);

        // Simular que agotó todos los intentos llamando a failed()
        $job->failed(new \RuntimeException('OpenAI error'));

        $this->assertDatabaseHas('print_analyses', [
            'id'     => $analysis->id,
            'status' => 'failed',
        ]);
        $this->assertNotNull(
            PrintAnalysis::find($analysis->id)->error_message
        );
    }
}
