<?php

namespace App\Services\Grok;

class CoordinatorResult
{
    public function __construct(
        public string $outcome,
        public string $message,
        public ?string $model = null,
        public ?ClonePrintResult $print = null,
    ) {}
}
