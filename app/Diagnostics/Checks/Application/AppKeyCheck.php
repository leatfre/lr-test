<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class AppKeyCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'APP_KEY';
    }

    public function run(): CheckResult
    {
        $key = (string) config('app.key');

        if ($key === '') {
            return CheckResult::fail($this->group(), $this->label(), 'not set', 'Run `php artisan key:generate`. Sessions and encrypted cookies will fail without it.');
        }

        return CheckResult::pass($this->group(), $this->label(), 'set');
    }
}
