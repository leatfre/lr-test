<?php

namespace Tests\Unit\Diagnostics;

use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use App\Diagnostics\CheckStatus;
use PHPUnit\Framework\TestCase;

class CheckResultTest extends TestCase
{
    public function test_named_constructors_set_the_matching_status(): void
    {
        $this->assertSame(CheckStatus::Pass, CheckResult::pass(CheckGroup::Runtime, 'PHP', '8.5.10')->status);
        $this->assertSame(CheckStatus::Warn, CheckResult::warn(CheckGroup::Runtime, 'PHP', '8.1.0')->status);
        $this->assertSame(CheckStatus::Fail, CheckResult::fail(CheckGroup::Runtime, 'PHP', '7.4.0')->status);
        $this->assertSame(CheckStatus::Skipped, CheckResult::skipped(CheckGroup::Runtime, 'PHP', 'n/a')->status);
    }

    public function test_it_exposes_label_value_detail_and_group(): void
    {
        $result = CheckResult::pass(CheckGroup::Database, 'Connection', 'sqlite', 'Connected in 3ms');

        $this->assertSame(CheckGroup::Database, $result->group);
        $this->assertSame('Connection', $result->label);
        $this->assertSame('sqlite', $result->value);
        $this->assertSame('Connected in 3ms', $result->detail);
        $this->assertSame([], $result->children);
    }

    public function test_children_can_be_attached_without_mutating_the_original(): void
    {
        $parent = CheckResult::pass(CheckGroup::Extensions, 'Required', '2/2');
        $withChildren = $parent->withChildren(
            CheckResult::pass(CheckGroup::Extensions, 'curl', 'loaded'),
            CheckResult::pass(CheckGroup::Extensions, 'mbstring', 'loaded'),
        );

        $this->assertCount(0, $parent->children);
        $this->assertCount(2, $withChildren->children);
        $this->assertSame('curl', $withChildren->children[0]->label);
    }

    public function test_it_serialises_to_an_array_for_json_responses(): void
    {
        $result = CheckResult::warn(CheckGroup::Queue, 'Worker', 'not running', 'No worker detected')
            ->withChildren(CheckResult::pass(CheckGroup::Queue, 'Driver', 'database'));

        $this->assertSame([
            'group' => 'Queue',
            'label' => 'Worker',
            'status' => 'warn',
            'value' => 'not running',
            'detail' => 'No worker detected',
            'children' => [[
                'group' => 'Queue',
                'label' => 'Driver',
                'status' => 'pass',
                'value' => 'database',
                'detail' => null,
                'children' => [],
            ]],
        ], $result->toArray());
    }
}
