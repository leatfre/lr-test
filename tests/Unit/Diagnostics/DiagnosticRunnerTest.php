<?php

namespace Tests\Unit\Diagnostics;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use App\Diagnostics\CheckStatus;
use App\Diagnostics\DiagnosticRunner;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DiagnosticRunnerTest extends TestCase
{
    public function test_it_collects_every_result_in_registration_order(): void
    {
        $report = (new DiagnosticRunner([
            $this->check(CheckResult::pass(CheckGroup::Runtime, 'First', 'a')),
            $this->check(CheckResult::warn(CheckGroup::Runtime, 'Second', 'b')),
        ]))->run();

        $this->assertSame(['First', 'Second'], array_map(
            fn (CheckResult $r) => $r->label,
            $report->all(),
        ));
    }

    public function test_it_groups_results_preserving_group_order(): void
    {
        $report = (new DiagnosticRunner([
            $this->check(CheckResult::pass(CheckGroup::Database, 'Connection', 'ok')),
            $this->check(CheckResult::pass(CheckGroup::Runtime, 'PHP', '8.5')),
            $this->check(CheckResult::pass(CheckGroup::Database, 'Write', 'ok')),
        ]))->run();

        $groups = $report->groups();

        $this->assertSame(['Database', 'Runtime'], array_map(
            fn (CheckGroup $g) => $g->value,
            array_column($groups, 'group'),
        ));
        $this->assertCount(2, $groups[0]['results']);
        $this->assertCount(1, $groups[1]['results']);
    }

    public function test_a_throwing_check_becomes_a_failed_result_instead_of_bubbling(): void
    {
        $runner = new DiagnosticRunner([
            new class implements Check
            {
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
                    throw new RuntimeException('connection refused');
                }
            },
        ]);

        $report = $runner->run();
        $result = $report->all()[0];

        $this->assertSame(CheckStatus::Fail, $result->status);
        $this->assertSame('Outbound HTTP', $result->label);
        $this->assertSame(CheckGroup::Network, $result->group);
        $this->assertStringContainsString('connection refused', (string) $result->detail);
    }

    public function test_counts_tally_every_status_including_children(): void
    {
        $report = (new DiagnosticRunner([
            $this->check(CheckResult::pass(CheckGroup::Runtime, 'A', 'x')->withChildren(
                CheckResult::fail(CheckGroup::Runtime, 'A.1', 'x'),
            )),
            $this->check(CheckResult::warn(CheckGroup::Runtime, 'B', 'x')),
            $this->check(CheckResult::skipped(CheckGroup::Runtime, 'C', 'x')),
        ]))->run();

        $this->assertSame(
            ['pass' => 1, 'warn' => 1, 'fail' => 1, 'skipped' => 1],
            $report->counts(),
        );
    }

    public function test_overall_status_is_the_worst_status_observed(): void
    {
        $this->assertSame(CheckStatus::Pass, (new DiagnosticRunner([
            $this->check(CheckResult::pass(CheckGroup::Runtime, 'A', 'x')),
            $this->check(CheckResult::skipped(CheckGroup::Runtime, 'B', 'x')),
        ]))->run()->overallStatus());

        $this->assertSame(CheckStatus::Warn, (new DiagnosticRunner([
            $this->check(CheckResult::pass(CheckGroup::Runtime, 'A', 'x')),
            $this->check(CheckResult::warn(CheckGroup::Runtime, 'B', 'x')),
        ]))->run()->overallStatus());

        $this->assertSame(CheckStatus::Fail, (new DiagnosticRunner([
            $this->check(CheckResult::warn(CheckGroup::Runtime, 'A', 'x')),
            $this->check(CheckResult::fail(CheckGroup::Runtime, 'B', 'x')),
        ]))->run()->overallStatus());
    }

    private function check(CheckResult $result): Check
    {
        return new class($result) implements Check
        {
            public function __construct(private CheckResult $result) {}

            public function group(): CheckGroup
            {
                return $this->result->group;
            }

            public function label(): string
            {
                return $this->result->label;
            }

            public function run(): CheckResult
            {
                return $this->result;
            }
        };
    }
}
