<?php

namespace App\Diagnostics\Checks\Network;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Throwable;

final class DnsResolutionCheck implements Check
{
    private const HOSTNAME = 'www.google.com';

    public function group(): CheckGroup
    {
        return CheckGroup::Network;
    }

    public function label(): string
    {
        return 'DNS resolution';
    }

    public function run(): CheckResult
    {
        try {
            if (function_exists('checkdnsrr') && @checkdnsrr(self::HOSTNAME, 'A')) {
                return CheckResult::pass($this->group(), $this->label(), 'resolved', self::HOSTNAME);
            }

            $ip = @gethostbyname(self::HOSTNAME);

            if ($ip !== self::HOSTNAME && filter_var($ip, FILTER_VALIDATE_IP)) {
                return CheckResult::pass($this->group(), $this->label(), 'resolved', "{$ip}");
            }

            return CheckResult::fail($this->group(), $this->label(), 'unresolved', 'Could not resolve '.self::HOSTNAME.'; outbound DNS may be blocked.');
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'unresolved', $e->getMessage());
        }
    }
}
