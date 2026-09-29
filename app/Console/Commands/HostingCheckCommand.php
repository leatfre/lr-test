<?php

namespace App\Console\Commands;

use App\Diagnostics\CheckRegistry;
use App\Diagnostics\DiagnosticRunner;
use App\Http\Controllers\DashboardController;
use Illuminate\Console\Command;

class HostingCheckCommand extends Command
{
    protected $signature = 'hosting:check {--json : Output the full report as JSON instead of formatted text}';

    protected $description = 'Run the hosting compatibility diagnostics suite and print the results.';

    public function handle(CheckRegistry $registry): int
    {
        $report = (new DiagnosticRunner($registry->checks()))->run();

        if ($this->option('json')) {
            $this->line(json_encode(
                DashboardController::reportToArray($report),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            ));

            return self::SUCCESS;
        }

        foreach ($report->groups() as $group) {
            $this->line('');
            $this->line('== '.$group['group']->value.' ==');

            foreach ($group['results'] as $result) {
                $this->line(sprintf(' [%s] %s: %s', strtoupper($result->status->value), $result->label, $result->value));

                if ($result->detail) {
                    $this->line('     '.$result->detail);
                }

                foreach ($result->children as $child) {
                    $this->line(sprintf('   - [%s] %s: %s', strtoupper($child->status->value), $child->label, $child->value));
                }
            }
        }

        $counts = $report->counts();

        $this->line('');
        $this->line(sprintf(
            'Overall: %s (pass=%d warn=%d fail=%d skipped=%d)',
            strtoupper($report->overallStatus()->value),
            $counts['pass'],
            $counts['warn'],
            $counts['fail'],
            $counts['skipped'],
        ));

        return self::SUCCESS;
    }
}
