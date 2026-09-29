<?php

namespace App\Diagnostics\Checks\Database;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

final class DdlDmlRoundTripCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Database;
    }

    public function label(): string
    {
        return 'DDL/DML round trip';
    }

    public function run(): CheckResult
    {
        $table = 'hosting_check_probe_'.Str::random(8);

        try {
            Schema::create($table, function ($blueprint) {
                $blueprint->id();
                $blueprint->string('value');
            });

            $expected = Str::random(12);
            DB::table($table)->insert(['value' => $expected]);
            $actual = DB::table($table)->value('value');

            if ($actual !== $expected) {
                return CheckResult::fail($this->group(), $this->label(), 'mismatch', "Wrote '{$expected}' but read '{$actual}'.");
            }

            return CheckResult::pass($this->group(), $this->label(), 'create/insert/select/drop ok');
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'failed', $e->getMessage());
        } finally {
            try {
                Schema::dropIfExists($table);
            } catch (Throwable) {
                // Cleanup best-effort; the failure is already reported above if relevant.
            }
        }
    }
}
