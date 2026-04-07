<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PrintAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_filename',
        'storage_path',
        'file_size_bytes',
        'page_count',
        'status',
        'error_message',
        'openai_file_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'file_size_bytes' => 'integer',
        'page_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(PrintAnalysisResult::class);
    }

    public function usageLog(): HasOne
    {
        return $this->hasOne(OpenAIUsageLog::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
