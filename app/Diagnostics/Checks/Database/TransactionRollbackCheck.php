<?php

namespace App\Diagnostics\Checks\Database;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

final class TransactionRollbackCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Database;
    }

    public function label(): string
    {
        return 'Transaction rollback';
    }

    public function run(): CheckResult
    {
        $table = 'hosting_check_txn_'.Str::random(8);

        try {
            Schema::create($table, function ($blueprint) {
                $blueprint->id();
                $blueprint->string('value');
            });

            DB::beginTransaction();
            DB::table($table)->insert(['value' => 'should-not-persist']);
            DB::rollBack();

            $count = DB::table($table)->count();

            if ($count !== 0) {
                return CheckResult::fail($this->group(), $this->label(), 'not isolated', 'Row survived a rollback; transactions are not effective on this connection.');
            }

            return CheckResult::pass($this->group(), $this->label(), 'rollback isolated');
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'failed', $e->getMessage());
        } finally {
            try {
                Schema::dropIfExists($table);
            } catch (Throwable) {
                // Cleanup best-effort.
            }
        }
    }
}
