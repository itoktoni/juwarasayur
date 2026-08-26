<?php

namespace App\Services\Commission;

readonly class FeeResult
{
    public function __construct(
        public float $percent,
        public float $amount,
        public ?string $source,
        public ?string $role,
        public float $hargaEfektif,
    ) {}
}
