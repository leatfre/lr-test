<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProbeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $marker) {}

    /**
     * The cache key this job writes to once it executes, proving it actually ran.
     */
    public function markerKey(): string
    {
        return 'hosting-check:probe-job:'.$this->marker;
    }

    public function handle(): void
    {
        Cache::put($this->markerKey(), now()->toIso8601String(), 60);
    }
}
