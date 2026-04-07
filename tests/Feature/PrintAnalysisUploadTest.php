<?php

namespace Tests\Feature;

use App\Jobs\AnalyzePrintFileJob;
use App\Models\PrintAnalysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrintAnalysisUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Queue::fake();
        $this->user = User::factory()->create();
    }

    /** Un usuario autenticado puede subir un PDF válido. */
    public function test_authenticated_user_can_upload_pdf(): void
    {
        $file = UploadedFile::fake()->create('documento.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-analyses', ['file' => $file]);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'status', 'filename']);

        $this->assertDatabaseHas('print_analyses', [
            'user_id'           => $this->user->id,
            'original_filename' => 'documento.pdf',
            'status'            => 'pending',
        ]);
    }

    /** Al subir un PDF se despacha el job de análisis en la cola. */
    public function test_upload_dispatches_analysis_job(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $this->actingAs($this->user)
            ->postJson('/api/print-analyses', ['file' => $file]);

        Queue::assertPushed(AnalyzePrintFileJob::class, function ($job) {
            return $job->analysis->user_id === $this->user->id;
        });
    }

    /** Se rechaza un archivo que no es PDF. */
    public function test_upload_rejects_non_pdf_files(): void
    {
        $file = UploadedFile::fake()->create('imagen.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-analyses', ['file' => $file]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);

        Queue::assertNothingPushed();
    }

    /** Se rechaza un PDF que supera 50 MB. */
    public function test_upload_rejects_pdf_exceeding_50mb(): void
    {
        // 51 MB (en KB para UploadedFile::fake)
        $file = UploadedFile::fake()->create('grande.pdf', 51 * 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-analyses', ['file' => $file]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);

        Queue::assertNothingPushed();
    }

    /** Un usuario no autenticado no puede subir archivos. */
    public function test_guest_cannot_upload(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $this->postJson('/api/print-analyses', ['file' => $file])
            ->assertUnauthorized();
    }

    /** El endpoint /stats devuelve la estructura correcta. */
    public function test_stats_endpoint_returns_expected_structure(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/print-analyses/stats');

        $response->assertOk()
            ->assertJsonStructure(['total_analyses', 'total_errors', 'ok_rate']);
    }

    /** Un usuario sólo ve sus propios análisis. */
    public function test_user_only_sees_own_analyses(): void
    {
        $otherUser = User::factory()->create();

        PrintAnalysis::factory()->create(['user_id' => $this->user->id]);
        PrintAnalysis::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/print-analyses');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /** Un usuario no puede ver el análisis de otro usuario. */
    public function test_user_cannot_view_other_users_analysis(): void
    {
        $otherUser = User::factory()->create();
        $analysis  = PrintAnalysis::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->getJson("/api/print-analyses/{$analysis->id}")
            ->assertForbidden();
    }

    /** El análisis propio devuelve la estructura correcta. */
    public function test_show_own_analysis_returns_correct_structure(): void
    {
        $analysis = PrintAnalysis::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/print-analyses/{$analysis->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'id', 'filename', 'status', 'created_at', 'results', 'usage',
            ]);
    }
}
