<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class AppEnvCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'APP_ENV';
    }

    public function run(): CheckResult
    {
        $env = (string) config('app.env');

        if ($env === 'local' || $env === 'testing') {
            return CheckResult::warn($this->group(), $this->label(), $env, 'Not a production environment.');
        }

        return CheckResult::pass($this->group(), $this->label(), $env);
    }
}
