<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintAnalysisResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'print_analysis_id',
        'check_type',
        'status',
        'summary',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(PrintAnalysis::class, 'print_analysis_id');
    }
}
