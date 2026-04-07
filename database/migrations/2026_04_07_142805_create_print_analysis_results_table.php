<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_analysis_id')->constrained()->cascadeOnDelete();
            $table->enum('check_type', [
                'resolution',
                'color_profile',
                'embedded_fonts',
                'bleed_area',
                'safety_margins',
                'transparency'
            ]);
            $table->enum('status', ['pass', 'warn', 'fail'])->default('warn');
            $table->text('summary')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_analysis_results');
    }
};
