<?php

namespace App\Diagnostics\Checks\Network;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Throwable;

final class MailTransportCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Network;
    }

    public function label(): string
    {
        return 'Mail transport';
    }

    public function run(): CheckResult
    {
        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return CheckResult::skipped($this->group(), $this->label(), $mailer, 'No network reachability check needed for this transport.');
        }

        if ($mailer !== 'smtp') {
            return CheckResult::pass($this->group(), $this->label(), $mailer, 'API-based transport; reachability depends on outbound HTTPS.');
        }

        $host = (string) config('mail.mailers.smtp.host');
        $port = (int) config('mail.mailers.smtp.port', 587);

        if ($host === '') {
            return CheckResult::fail($this->group(), $this->label(), 'smtp', 'No SMTP host configured.');
        }

        try {
            $errno = 0;
            $errstr = '';
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);

            if ($connection === false) {
                return CheckResult::fail($this->group(), $this->label(), "{$host}:{$port}", $errstr ?: 'Connection failed.');
            }

            fclose($connection);

            return CheckResult::pass($this->group(), $this->label(), "{$host}:{$port}");
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), "{$host}:{$port}", $e->getMessage());
        }
    }
}
