<?php

namespace App\Diagnostics\Checks\Queue;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use App\Jobs\ProbeJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

final class SyncDispatchCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Queue;
    }

    public function label(): string
    {
        return 'Dispatch round trip';
    }

    public function run(): CheckResult
    {
        $marker = Str::random(12);
        $job = new ProbeJob($marker);

        try {
            ProbeJob::dispatchSync($marker);

            $found = Cache::get($job->markerKey()) !== null;
            Cache::forget($job->markerKey());

            if (! $found) {
                return CheckResult::fail($this->group(), $this->label(), 'no marker', 'Job dispatched synchronously but its side effect was not observed.');
            }

            return CheckResult::pass($this->group(), $this->label(), 'ok', 'ProbeJob ran synchronously and wrote its marker.');
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'failed', $e->getMessage());
        }
    }
}
