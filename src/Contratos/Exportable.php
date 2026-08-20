<?php

declare(strict_types=1);

namespace App\Contratos;

interface Exportable
{
    /** @return array<string, mixed> */
    public function aArray(): array;
}
