<?php

namespace App\Services\Grok;

class PrintSpec
{
    public function __construct(
        public string $type,
        public ?int $width,
        public ?int $height,
        public ?int $dpi,
        public string $sourceBytes,
        public string $instruction = '',
        public string $redoNote = '',
    ) {}

    public function isComplete(): bool
    {
        return $this->width !== null && $this->width > 0
            && $this->height !== null && $this->height > 0
            && $this->dpi !== null && $this->dpi > 0;
    }
}
