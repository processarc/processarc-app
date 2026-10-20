<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OptimizationOpportunity;
use App\Models\Process;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(): View
    {
        return view('app.optimizations.index', [
            'opportunities' => OptimizationOpportunity::with('process')->latest()->paginate(12),
            'processes' => Process::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, Process $process): RedirectResponse
    {
        return $this->createOpportunity($request, $process);
    }

    public function storeFromWorkspace(Request $request): RedirectResponse
    {
        $validated = $request->validate(['process_id' => ['required', 'integer', 'exists:processes,id']]);

        return $this->createOpportunity($request, Process::findOrFail($validated['process_id']));
    }

    private function createOpportunity(Request $request, Process $process): RedirectResponse
    {
        $opportunity = $process->opportunities()->create($request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high'],
            'owner_name' => ['nullable', 'string', 'max:120'],
            'impact_hours' => ['required', 'numeric', 'min:0', 'max:999999'],
        ]));
        ActivityLog::create(['user_id' => $request->user()->id, 'event' => 'Opportunity identified', 'description' => "Identified {$opportunity->title} for {$process->name}."]);

        return back()->with('success', 'Optimization opportunity recorded.');
    }

    public function update(Request $request, OptimizationOpportunity $opportunity): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:identified,approved,in_progress,completed']]);
        $opportunity->update($data);
        ActivityLog::create(['user_id' => $request->user()->id, 'event' => 'Opportunity status updated', 'description' => "Moved {$opportunity->title} to ".str_replace('_', ' ', $opportunity->status).'.']);

        return back()->with('success', 'Opportunity status updated.');
    }
}
