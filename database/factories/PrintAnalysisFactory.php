<?php

namespace Database\Factories;

use App\Models\PrintAnalysis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrintAnalysis>
 */
class PrintAnalysisFactory extends Factory
{
    protected $model = PrintAnalysis::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'original_filename' => $this->faker->word() . '.pdf',
            'storage_path'      => 'print_analyses/1/' . $this->faker->uuid() . '.pdf',
            'file_size_bytes'   => $this->faker->numberBetween(50_000, 10_000_000),
            'page_count'        => null,
            'status'            => 'pending',
            'error_message'     => null,
            'openai_file_id'    => null,
            'completed_at'      => null,
        ];
    }

    /** Estado: análisis en proceso. */
    public function processing(): static
    {
        return $this->state(['status' => 'processing']);
    }

    /** Estado: análisis completado exitosamente. */
    public function completed(): static
    {
        return $this->state([
            'status'         => 'completed',
            'page_count'     => $this->faker->numberBetween(1, 3),
            'openai_file_id' => 'file-' . $this->faker->uuid(),
            'completed_at'   => now(),
        ]);
    }

    /** Estado: análisis fallido. */
    public function failed(): static
    {
        return $this->state([
            'status'        => 'failed',
            'error_message' => 'Error simulado en test.',
        ]);
    }
}
