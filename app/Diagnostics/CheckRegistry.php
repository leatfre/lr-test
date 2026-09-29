<?php

namespace App\Diagnostics;

interface CheckRegistry
{
    /**
     * @return Check[]
     */
    public function checks(): array;
}
