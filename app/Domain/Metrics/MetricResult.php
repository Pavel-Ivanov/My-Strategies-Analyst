<?php

namespace App\Domain\Metrics;

class MetricResult
{
    public function __construct(
        public readonly string $key,
        public readonly ?float $value,
        public readonly ?string $unit = null,
        public readonly ?string $displayName = null,
        public readonly array $meta = [],
    ) {}
}
