<?php

namespace App\Diagnostics\Checks\Queue;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class JobsTableCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Queue;
    }

    public function label(): string
    {
        return 'Jobs table';
    }

    public function run(): CheckResult
    {
        $connection = (string) config('queue.default');

        if ($connection !== 'database') {
            return CheckResult::skipped($this->group(), $this->label(), 'n/a', "Queue driver is '{$connection}', not 'database'.");
        }

        $table = (string) config('queue.connections.database.table', 'jobs');

        try {
            if (! Schema::hasTable($table)) {
                return CheckResult::fail($this->group(), $this->label(), 'missing', "Table '{$table}' not found; run `php artisan queue:table` and migrate.");
            }

            return CheckResult::pass($this->group(), $this->label(), 'present');
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'unknown', $e->getMessage());
        }
    }
}
