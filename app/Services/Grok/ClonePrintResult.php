<?php

namespace App\Services\Grok;

class ClonePrintResult
{
    /**
     * @param  array<int, array<string, mixed>>  $layers
     * @param  array<int, array<string, mixed>>  $checklist
     */
    public function __construct(
        public string $png,
        public array $layers,
        public array $checklist,
        public string $model,
    ) {}
}
