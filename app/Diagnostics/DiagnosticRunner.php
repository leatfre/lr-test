<?php

namespace App\Diagnostics;

use Throwable;

final class DiagnosticRunner
{
    /**
     * @param  iterable<Check>  $checks
     */
    public function __construct(private readonly iterable $checks) {}

    public function run(): DiagnosticReport
    {
        $results = [];

        foreach ($this->checks as $check) {
            try {
                $results[] = $check->run();
            } catch (Throwable $throwable) {
                $results[] = CheckResult::fail(
                    $check->group(),
                    $check->label(),
                    'error',
                    $throwable->getMessage(),
                );
            }
        }

        return new DiagnosticReport($results);
    }
}
