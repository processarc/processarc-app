<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Process;
use App\Models\ProcessMetric;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProcessController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,at_risk,paused'],
            'category' => ['nullable', 'string', 'max:80'],
        ]);

        $processes = Process::with('owner')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")->orWhere('category', 'like', "%{$term}%");
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('app.processes.index', [
            'processes' => $processes,
            'categories' => Process::query()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function create(): View
    {
        return view('app.processes.form', [
            'process' => new Process(),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'availableProcesses' => Process::orderBy('name')->get(['id', 'name']),
            'currentExceptionCount' => 0,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $dependencyIds = $data['dependency_ids'] ?? [];
        $exceptionCount = $data['exception_count'];
        unset($data['dependency_ids']);
        unset($data['exception_count']);
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;
        $process = Process::create($data);
        if ($request->boolean('dependency_ids_submitted')) {
            $process->dependencies()->sync($dependencyIds);
        }
        $process->recordMetricSnapshot((float) $process->cycle_time_hours, $exceptionCount);
        ActivityLog::create(['user_id' => $request->user()->id, 'event' => 'Process created', 'description' => "Added {$process->name} to the process registry."]);

        return redirect()->route('app.processes.show', $process)->with('success', 'Process added to your registry.');
    }

    public function show(Process $process): View
    {
        $process->load(['owner', 'stages', 'opportunities', 'dependencies']);

        return view('app.processes.show', compact('process'));
    }

    public function edit(Process $process): View
    {
        $process->load('dependencies');

        return view('app.processes.form', [
            'process' => $process,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'availableProcesses' => Process::where('id', '!=', $process->id)->orderBy('name')->get(['id', 'name']),
            'currentExceptionCount' => $process->metrics()->latest('measured_at')->value('exception_count') ?? 0,
        ]);
    }

    public function update(Request $request, Process $process): RedirectResponse
    {
        $data = $this->validatedData($request, $process);
        $dependencyIds = $data['dependency_ids'] ?? [];
        $exceptionCount = (int) $data['exception_count'];
        unset($data['dependency_ids']);
        unset($data['exception_count']);
        $cycleTimeChanged = (float) $process->cycle_time_hours !== (float) $data['cycle_time_hours'];
        $latestMetric = $process->metrics()->latest('measured_at')->first();
        $exceptionCountChanged = (int) ($latestMetric?->exception_count ?? 0) !== $exceptionCount;
        $process->update($data);
        if ($request->boolean('dependency_ids_submitted')) {
            $process->dependencies()->sync($dependencyIds);
        }
        if ($cycleTimeChanged || $exceptionCountChanged) {
            $process->recordMetricSnapshot((float) $process->cycle_time_hours, $exceptionCount);
        }
        ActivityLog::create(['user_id' => $request->user()->id, 'event' => 'Process updated', 'description' => "Updated {$process->name}."]);

        return redirect()->route('app.processes.show', $process)->with('success', 'Process details updated.');
    }

    public function destroy(Request $request, Process $process): RedirectResponse
    {
        $name = $process->name;
        $process->delete();
        ActivityLog::create(['user_id' => $request->user()->id, 'event' => 'Process archived', 'description' => "Removed {$name} and its linked records from the registry."]);

        return redirect()->route('app.processes.index')->with('success', 'Process removed from the registry.');
    }

    private function validatedData(Request $request, ?Process $process = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,at_risk,paused'],
            'cycle_time_hours' => ['required', 'numeric', 'min:0', 'max:999999'],
            'target_cycle_hours' => ['required', 'numeric', 'min:0', 'max:999999'],
            'exception_count' => ['required', 'integer', 'min:0', 'max:1000000'],
            'owner_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'dependency_ids_submitted' => ['sometimes', 'accepted'],
            'dependency_ids' => ['sometimes', 'array'],
            'dependency_ids.*' => ['integer', 'exists:processes,id', Rule::notIn(array_filter([$process?->id]))],
        ]);
    }
}
