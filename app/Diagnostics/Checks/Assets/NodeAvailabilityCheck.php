<?php

namespace App\Diagnostics\Checks\Assets;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Throwable;

final class NodeAvailabilityCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Assets;
    }

    public function label(): string
    {
        return 'Node / npm availability';
    }

    public function run(): CheckResult
    {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        if (! function_exists('exec') || in_array('exec', $disabled, true)) {
            return CheckResult::skipped(
                $this->group(),
                $this->label(),
                'unknown',
                'exec() is unavailable; the build must run on a machine that has Node/npm and its output uploaded.',
            );
        }

        try {
            $nodeOutput = [];
            $nodeExit = 1;
            @exec('node -v 2>&1', $nodeOutput, $nodeExit);

            $npmOutput = [];
            $npmExit = 1;
            @exec('npm -v 2>&1', $npmOutput, $npmExit);

            $node = $nodeExit === 0 ? trim(implode(' ', $nodeOutput)) : null;
            $npm = $npmExit === 0 ? trim(implode(' ', $npmOutput)) : null;

            if ($node === null && $npm === null) {
                return CheckResult::warn(
                    $this->group(),
                    $this->label(),
                    'not found',
                    'Node/npm are not on the server. Build assets locally with `npm run build` and upload public/build.',
                );
            }

            return CheckResult::pass($this->group(), $this->label(), 'node '.($node ?? 'n/a').', npm '.($npm ?? 'n/a'));
        } catch (Throwable $e) {
            return CheckResult::skipped($this->group(), $this->label(), 'unknown', $e->getMessage());
        }
    }
}
