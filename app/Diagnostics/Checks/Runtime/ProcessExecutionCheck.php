<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class ProcessExecutionCheck implements Check
{
    private const FUNCTIONS = ['exec', 'shell_exec', 'proc_open', 'passthru'];

    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Process execution';
    }

    public function run(): CheckResult
    {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        $available = [];
        $unavailable = [];

        foreach (self::FUNCTIONS as $function) {
            if (function_exists($function) && ! in_array($function, $disabled, true)) {
                $available[] = $function;
            } else {
                $unavailable[] = $function;
            }
        }

        $value = count($available).'/'.count(self::FUNCTIONS).' available';

        if (empty($available)) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'Queue workers and storage:link fallbacks may be unavailable. Disabled: '.implode(', ', $unavailable));
        }

        if (! empty($unavailable)) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'Unavailable: '.implode(', ', $unavailable));
        }

        return CheckResult::pass($this->group(), $this->label(), $value, implode(', ', $available));
    }
}
