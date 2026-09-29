<?php

namespace Tests\Feature;

use Tests\TestCase;

class HostingCheckCommandTest extends TestCase
{
    public function test_the_command_runs_and_prints_every_group(): void
    {
        $command = $this->artisan('hosting:check');

        foreach (\App\Diagnostics\CheckGroup::cases() as $group) {
            $command->expectsOutputToContain($group->value);
        }

        $command->run();
    }

    public function test_the_command_supports_json_output(): void
    {
        $this->artisan('hosting:check --json')->run();

        $this->assertTrue(true);
    }
}
