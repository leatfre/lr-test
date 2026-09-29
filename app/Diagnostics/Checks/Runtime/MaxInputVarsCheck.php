<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class MaxInputVarsCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Max input vars';
    }

    public function run(): CheckResult
    {
        $raw = ini_get('max_input_vars');
        $value = $raw === false ? 'n/a' : (string) $raw;

        if ($raw !== false && (int) $raw < 1000) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'Large forms may be silently truncated below 1000.');
        }

        return CheckResult::pass($this->group(), $this->label(), $value);
    }
}
