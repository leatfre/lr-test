<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class Integer64BitCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return '64-bit integers';
    }

    public function run(): CheckResult
    {
        $bits = PHP_INT_SIZE * 8;
        $value = "{$bits}-bit (PHP_INT_MAX=".PHP_INT_MAX.')';

        if ($bits < 64) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'Large integer IDs (e.g. Snowflake IDs) may overflow.');
        }

        return CheckResult::pass($this->group(), $this->label(), $value);
    }
}
