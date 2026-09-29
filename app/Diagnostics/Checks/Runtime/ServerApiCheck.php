<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class ServerApiCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Server API';
    }

    public function run(): CheckResult
    {
        return CheckResult::pass($this->group(), $this->label(), PHP_SAPI);
    }
}
