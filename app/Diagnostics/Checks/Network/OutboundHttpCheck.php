<?php

namespace App\Diagnostics\Checks\Network;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\Http;
use Throwable;

final class OutboundHttpCheck implements Check
{
    private const URL = 'https://www.google.com/generate_204';

    public function group(): CheckGroup
    {
        return CheckGroup::Network;
    }

    public function label(): string
    {
        return 'Outbound HTTP';
    }

    public function run(): CheckResult
    {
        try {
            $response = Http::timeout(5)->connectTimeout(3)->get(self::URL);

            if ($response->successful() || $response->status() === 204) {
                return CheckResult::pass($this->group(), $this->label(), 'HTTP '.$response->status());
            }

            return CheckResult::warn($this->group(), $this->label(), 'HTTP '.$response->status(), 'Outbound request completed but returned an unexpected status.');
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'unreachable', $e->getMessage());
        }
    }
}
