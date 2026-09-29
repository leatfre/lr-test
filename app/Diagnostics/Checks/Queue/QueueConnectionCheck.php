<?php

namespace App\Diagnostics\Checks\Queue;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class QueueConnectionCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Queue;
    }

    public function label(): string
    {
        return 'Queue connection';
    }

    public function run(): CheckResult
    {
        $connection = (string) config('queue.default');

        return CheckResult::pass($this->group(), $this->label(), $connection);
    }
}
