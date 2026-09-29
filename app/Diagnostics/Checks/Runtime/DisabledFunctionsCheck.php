<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class DisabledFunctionsCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Disabled functions';
    }

    public function run(): CheckResult
    {
        $raw = trim((string) ini_get('disable_functions'));

        if ($raw === '') {
            return CheckResult::pass($this->group(), $this->label(), 'none');
        }

        $functions = array_values(array_filter(array_map('trim', explode(',', $raw))));

        $risky = array_values(array_intersect($functions, ['exec', 'shell_exec', 'proc_open', 'passthru', 'popen', 'symlink']));

        $value = count($functions).' disabled';

        if (! empty($risky)) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'Disabled: '.implode(', ', $risky));
        }

        return CheckResult::pass($this->group(), $this->label(), $value, implode(', ', $functions));
    }
}
