<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OptimizationOpportunity;
use App\Models\Process;
use App\Models\ProcessMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $processes = Process::with('owner')->latest()->take(5)->get();
        $opportunities = OptimizationOpportunity::with('process')->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $activeCount = Process::where('status', 'active')->count();
        $atRiskCount = Process::where('status', 'at_risk')->count();
        $openOpportunities = OptimizationOpportunity::whereNotIn('status', ['completed'])->count();
        $averageCycle = (float) Process::where('status', 'active')->avg('cycle_time_hours');
        $averageTarget = (float) Process::where('status', 'active')->avg('target_cycle_hours');
        $metricHistory = ProcessMetric::query()
            ->select('measured_at', DB::raw('avg(cycle_time_hours) as average_cycle'), DB::raw('sum(exception_count) as exception_count'))
            ->where('measured_at', '>=', now()->subWeeks(8)->toDateString())
            ->groupBy('measured_at')
            ->orderBy('measured_at')
            ->get();

        return view('app.dashboard', [
            'processes' => $processes,
            'opportunities' => $opportunities,
            'activeCount' => $activeCount,
            'atRiskCount' => $atRiskCount,
            'openOpportunities' => $openOpportunities,
            'averageCycle' => $averageCycle,
            'averageTarget' => $averageTarget,
            'metricHistory' => $metricHistory,
            'cycleGap' => $averageTarget > 0 ? round((($averageCycle - $averageTarget) / $averageTarget) * 100) : 0,
            'activities' => ActivityLog::with('user')->latest()->take(5)->get(),
        ]);
    }

    public function analysis(): View
    {
        $categoryMetrics = Process::query()
            ->select('category', DB::raw('count(*) as process_count'), DB::raw('avg(cycle_time_hours) as average_cycle'))
            ->groupBy('category')
            ->orderByDesc('process_count')
            ->get();

        return view('app.analysis', [
            'categoryMetrics' => $categoryMetrics,
            'metricHistory' => ProcessMetric::query()
                ->select('measured_at', DB::raw('avg(cycle_time_hours) as average_cycle'), DB::raw('sum(exception_count) as exception_count'))
                ->where('measured_at', '>=', now()->subWeeks(8)->toDateString())
                ->groupBy('measured_at')
                ->orderBy('measured_at')
                ->get(),
            'totalCycleHours' => (float) Process::sum('cycle_time_hours'),
            'completedOpportunities' => OptimizationOpportunity::where('status', 'completed')->count(),
            'totalOpportunities' => OptimizationOpportunity::count(),
            'averageTarget' => (float) Process::where('status', 'active')->avg('target_cycle_hours'),
        ]);
    }
}
