<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class LogsWritableCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'Log storage';
    }

    public function run(): CheckResult
    {
        $path = storage_path('logs');

        if (! is_dir($path) || ! is_writable($path)) {
            return CheckResult::fail($this->group(), $this->label(), 'not writable', $path);
        }

        return CheckResult::pass($this->group(), $this->label(), 'writable', $path);
    }
}
