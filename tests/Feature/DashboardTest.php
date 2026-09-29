<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_the_dashboard_renders_successfully(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Hosting Compatibility', false);
    }

    public function test_the_dashboard_lists_every_check_group(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (\App\Diagnostics\CheckGroup::cases() as $group) {
            $response->assertSee($group->value, false);
        }
    }

    public function test_the_dashboard_survives_a_check_that_throws(): void
    {
        $this->app->bind(\App\Diagnostics\CheckRegistry::class, fn () => new class implements \App\Diagnostics\CheckRegistry
        {
            public function checks(): array
            {
                return [new class implements \App\Diagnostics\Check
                {
                    public function group(): \App\Diagnostics\CheckGroup
                    {
                        return \App\Diagnostics\CheckGroup::Runtime;
                    }

                    public function label(): string
                    {
                        return 'Exploding check';
                    }

                    public function run(): \App\Diagnostics\CheckResult
                    {
                        throw new \RuntimeException('boom');
                    }
                }];
            }
        });

        $this->get('/')->assertOk()->assertSee('Exploding check', false);
    }

    public function test_the_report_is_available_as_json(): void
    {
        $this->getJson('/report.json')
            ->assertOk()
            ->assertJsonStructure([
                'overall',
                'counts' => ['pass', 'warn', 'fail', 'skipped'],
                'groups' => [['group', 'results' => [['label', 'status', 'value', 'detail', 'children']]]],
            ]);
    }

    public function test_phpinfo_is_exposed_on_its_own_route(): void
    {
        $this->get('/phpinfo')->assertOk()->assertSee('phpinfo', false);
    }
}
