<?php

namespace App\Services\Grok;

class GrokReply
{
    public function __construct(
        public string $content,
        public string $model,
    ) {}
}
