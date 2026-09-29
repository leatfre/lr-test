<?php

namespace App\Http\Controllers;

use App\Diagnostics\CheckRegistry;
use App\Diagnostics\DiagnosticReport;
use App\Diagnostics\DiagnosticRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(CheckRegistry $registry): View
    {
        return view('dashboard', [
            'report' => $this->run($registry),
        ]);
    }

    public function report(CheckRegistry $registry): JsonResponse
    {
        return response()->json($this->reportToArray($this->run($registry)));
    }

    private function run(CheckRegistry $registry): DiagnosticReport
    {
        return (new DiagnosticRunner($registry->checks()))->run();
    }

    /**
     * @return array{overall: string, counts: array, groups: array}
     */
    public static function reportToArray(DiagnosticReport $report): array
    {
        return [
            'overall' => $report->overallStatus()->value,
            'counts' => $report->counts(),
            'groups' => array_map(
                fn (array $group) => [
                    'group' => $group['group']->value,
                    'results' => array_map(
                        fn ($result) => $result->toArray(),
                        $group['results'],
                    ),
                ],
                $report->groups(),
            ),
        ];
    }
}
