<?php

namespace App\Diagnostics\Checks\Database;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ConnectionCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Database;
    }

    public function label(): string
    {
        return 'Connection';
    }

    public function run(): CheckResult
    {
        $driver = (string) config('database.default');

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $elapsedMs = round((microtime(true) - $start) * 1000, 2);

            return CheckResult::pass($this->group(), $this->label(), $driver, "Connected in {$elapsedMs}ms");
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), $driver, $e->getMessage());
        }
    }
}
