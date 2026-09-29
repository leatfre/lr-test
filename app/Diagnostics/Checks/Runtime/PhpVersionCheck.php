<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class PhpVersionCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'PHP version';
    }

    public function run(): CheckResult
    {
        $value = PHP_VERSION;

        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            return CheckResult::fail($this->group(), $this->label(), $value, 'Laravel 13 requires PHP 8.2 or higher.');
        }

        if (version_compare(PHP_VERSION, '8.3.0', '<')) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'PHP 8.3+ is recommended for current Laravel releases.');
        }

        return CheckResult::pass($this->group(), $this->label(), $value);
    }
}
