<?php

namespace App\Diagnostics\Checks\Database;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class MigrationTableCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Database;
    }

    public function label(): string
    {
        return 'Migration table';
    }

    public function run(): CheckResult
    {
        $table = (string) config('database.migrations.table', 'migrations');

        try {
            if (! Schema::hasTable($table)) {
                return CheckResult::warn($this->group(), $this->label(), 'missing', "Table '{$table}' not found; run `php artisan migrate`.");
            }

            $count = DB::table($table)->count();

            return CheckResult::pass($this->group(), $this->label(), "{$count} migrations run");
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'unknown', $e->getMessage());
        }
    }
}
