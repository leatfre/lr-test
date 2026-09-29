<?php

namespace Tests\Feature\Diagnostics;

use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckRegistry;
use App\Diagnostics\CheckStatus;
use App\Diagnostics\DiagnosticRunner;
use Tests\TestCase;

class RegisteredChecksTest extends TestCase
{
    public function test_the_registry_is_resolvable_and_not_empty(): void
    {
        $this->assertNotEmpty(app(CheckRegistry::class)->checks());
    }

    public function test_every_registered_check_covers_every_group(): void
    {
        $groups = array_map(
            fn ($check) => $check->group(),
            app(CheckRegistry::class)->checks(),
        );

        foreach (CheckGroup::cases() as $group) {
            $this->assertContains($group, $groups, "No check registered for group {$group->value}");
        }
    }

    public function test_the_full_suite_runs_without_throwing_and_reports_every_result(): void
    {
        $report = (new DiagnosticRunner(app(CheckRegistry::class)->checks()))->run();

        $this->assertNotEmpty($report->all());

        foreach ($report->all() as $result) {
            $this->assertInstanceOf(CheckStatus::class, $result->status);
            $this->assertNotSame('', $result->label);
            $this->assertNotSame('', $result->value);
        }
    }

    public function test_core_runtime_facts_are_reported_accurately(): void
    {
        $report = (new DiagnosticRunner(app(CheckRegistry::class)->checks()))->run();

        $byLabel = [];
        foreach ($report->all() as $result) {
            $byLabel[$result->label] = $result;
        }

        $this->assertArrayHasKey('PHP version', $byLabel);
        $this->assertSame(PHP_VERSION, $byLabel['PHP version']->value);

        $this->assertArrayHasKey('Server API', $byLabel);
        $this->assertSame(PHP_SAPI, $byLabel['Server API']->value);

        $this->assertArrayHasKey('Required extensions', $byLabel);
        $this->assertNotEmpty($byLabel['Required extensions']->children);
    }

    public function test_database_connection_check_passes_against_the_test_database(): void
    {
        $report = (new DiagnosticRunner(app(CheckRegistry::class)->checks()))->run();

        foreach ($report->all() as $result) {
            if ($result->label === 'Connection') {
                $this->assertSame(CheckStatus::Pass, $result->status, (string) $result->detail);

                return;
            }
        }

        $this->fail('No database connection check was registered.');
    }
}
