<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class MaxExecutionTimeCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Max execution time';
    }

    public function run(): CheckResult
    {
        $raw = (string) ini_get('max_execution_time');
        $seconds = (int) $raw;

        if ($seconds === 0) {
            return CheckResult::pass($this->group(), $this->label(), 'unlimited');
        }

        if ($seconds < 30) {
            return CheckResult::warn($this->group(), $this->label(), $seconds.'s', 'Short limits can abort artisan commands or heavy requests.');
        }

        return CheckResult::pass($this->group(), $this->label(), $seconds.'s');
    }
}
