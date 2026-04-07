<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenAIUsageLog extends Model
{
    use HasFactory;

    protected $table = 'openai_usage_logs';

    protected $fillable = [
        'print_analysis_id',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
    ];

    protected $casts = [
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost_usd' => 'decimal:6',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(PrintAnalysis::class, 'print_analysis_id');
    }
}
