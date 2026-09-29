<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class AppDebugCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'APP_DEBUG';
    }

    public function run(): CheckResult
    {
        $debug = (bool) config('app.debug');

        if ($debug) {
            return CheckResult::warn($this->group(), $this->label(), 'true', 'Debug mode leaks stack traces and configuration; disable it in production.');
        }

        return CheckResult::pass($this->group(), $this->label(), 'false');
    }
}
