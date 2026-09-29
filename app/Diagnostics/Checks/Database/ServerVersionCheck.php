<?php

namespace App\Diagnostics\Checks\Database;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

final class ServerVersionCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Database;
    }

    public function label(): string
    {
        return 'Server version';
    }

    public function run(): CheckResult
    {
        try {
            $version = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);

            return CheckResult::pass($this->group(), $this->label(), (string) $version);
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'unknown', $e->getMessage());
        }
    }
}
