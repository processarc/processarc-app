<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Process;
use App\Models\WorkflowStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    public function index(): View
    {
        return view('app.workflows.index', [
            'processes' => Process::with('stages')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Process $process): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'owner_name' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['position'] = (int) $process->stages()->max('position') + 1;
        $stage = $process->stages()->create($data);
        ActivityLog::create(['user_id' => $request->user()->id, 'event' => 'Workflow stage added', 'description' => "Added {$stage->name} to {$process->name}."]);

        return back()->with('success', 'Workflow stage added.');
    }

    public function update(Request $request, WorkflowStage $stage): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:not_started,in_progress,completed,blocked']]);
        $stage->update($data);
        ActivityLog::create(['user_id' => $request->user()->id, 'event' => 'Workflow stage updated', 'description' => "Marked {$stage->name} as ".str_replace('_', ' ', $stage->status).'.']);

        return back()->with('success', 'Workflow stage updated.');
    }
}
