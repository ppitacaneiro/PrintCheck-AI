<?php

namespace App\Services\PrintCheck;

/**
 * Resultado individual de una comprobación de preimpresión.
 */
final class CheckResult
{
    public function __construct(
        public readonly string $checkType,
        public readonly string $status,   // pass | warn | fail
        public readonly string $summary,
        public readonly array $details = [],
    ) {}
}
