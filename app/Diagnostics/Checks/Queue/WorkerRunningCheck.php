<?php

namespace App\Diagnostics\Checks\Queue;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Throwable;

final class WorkerRunningCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Queue;
    }

    public function label(): string
    {
        return 'Queue worker';
    }

    public function run(): CheckResult
    {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        if (! function_exists('exec') || in_array('exec', $disabled, true)) {
            return CheckResult::skipped($this->group(), $this->label(), 'unknown', 'exec() is unavailable; cannot inspect running processes.');
        }

        if (stripos(PHP_OS, 'WIN') === 0) {
            return CheckResult::skipped($this->group(), $this->label(), 'unknown', 'Process inspection for queue:work is not implemented for Windows hosts.');
        }

        try {
            $output = [];
            @exec('ps aux 2>/dev/null', $output);

            $running = false;
            foreach ($output as $line) {
                if (str_contains($line, 'queue:work') || str_contains($line, 'queue:listen')) {
                    $running = true;
                    break;
                }
            }

            return $running
                ? CheckResult::pass($this->group(), $this->label(), 'running')
                : CheckResult::warn($this->group(), $this->label(), 'not detected', 'No queue:work/queue:listen process found. Queued jobs will not run unless a worker or cron processes them.');
        } catch (Throwable $e) {
            return CheckResult::skipped($this->group(), $this->label(), 'unknown', $e->getMessage());
        }
    }
}
