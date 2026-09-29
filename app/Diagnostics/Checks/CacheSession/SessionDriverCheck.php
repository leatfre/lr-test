<?php

namespace App\Diagnostics\Checks\CacheSession;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\Schema;

final class SessionDriverCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::CacheSession;
    }

    public function label(): string
    {
        return 'Session driver';
    }

    public function run(): CheckResult
    {
        $driver = (string) config('session.driver');

        $needsDatabase = in_array($driver, ['database'], true);

        if ($needsDatabase && ! Schema::hasTable((string) config('session.table', 'sessions'))) {
            return CheckResult::warn($this->group(), $this->label(), $driver, 'Sessions table is missing; run `php artisan migrate`.');
        }

        return CheckResult::pass($this->group(), $this->label(), $driver);
    }
}
