<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Foundation\Application;

final class LaravelVersionCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'Laravel version';
    }

    public function run(): CheckResult
    {
        return CheckResult::pass($this->group(), $this->label(), Application::VERSION);
    }
}
