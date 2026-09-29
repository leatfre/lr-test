<?php

namespace App\Diagnostics;

final class DiagnosticReport
{
    /**
     * @param  CheckResult[]  $results  Flat, top-level results in registration order.
     */
    public function __construct(private readonly array $results) {}

    /**
     * @return CheckResult[]
     */
    public function all(): array
    {
        return $this->results;
    }

    /**
     * @return array<int, array{group: CheckGroup, results: CheckResult[]}>
     */
    public function groups(): array
    {
        $groups = [];

        foreach ($this->results as $result) {
            $key = $result->group->value;

            if (! isset($groups[$key])) {
                $groups[$key] = ['group' => $result->group, 'results' => []];
            }

            $groups[$key]['results'][] = $result;
        }

        return array_values($groups);
    }

    /**
     * @return array{pass: int, warn: int, fail: int, skipped: int}
     */
    public function counts(): array
    {
        $counts = ['pass' => 0, 'warn' => 0, 'fail' => 0, 'skipped' => 0];

        foreach ($this->results as $result) {
            $this->tally($result, $counts);
        }

        return $counts;
    }

    /**
     * @param  array{pass: int, warn: int, fail: int, skipped: int}  $counts
     */
    private function tally(CheckResult $result, array &$counts): void
    {
        $counts[$result->status->value]++;

        foreach ($result->children as $child) {
            $this->tally($child, $counts);
        }
    }

    public function overallStatus(): CheckStatus
    {
        $worst = CheckStatus::Skipped;

        foreach ($this->results as $result) {
            $worst = $this->worstOf($worst, $result);
        }

        return $worst;
    }

    private function worstOf(CheckStatus $worst, CheckResult $result): CheckStatus
    {
        if ($result->status->severity() > $worst->severity()) {
            $worst = $result->status;
        }

        foreach ($result->children as $child) {
            $worst = $this->worstOf($worst, $child);
        }

        return $worst;
    }
}
