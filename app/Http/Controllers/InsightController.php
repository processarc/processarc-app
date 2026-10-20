<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InsightController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate(['process_id' => ['required', 'integer', 'exists:processes,id']]);
        $process = Process::with(['stages', 'opportunities'])->findOrFail($data['process_id']);
        $model = config('services.ollama.model');

        if (! $model) {
            return back()->with('error', 'Local AI is not configured yet. Set OLLAMA_MODEL in your environment to enable Qwen insights.');
        }

        $prompt = $this->promptFor($process);

        try {
            $response = Http::timeout(45)->post(rtrim(config('services.ollama.url'), '/').'/api/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
            ]);
        } catch (ConnectionException) {
            return back()->with('error', 'Could not reach your local Ollama service. Start Ollama and try again.');
        }

        $insight = $response->json('response');
        if (! $response->successful() || ! is_string($insight) || trim($insight) === '') {
            return back()->with('error', 'The local model did not return an insight. Check the Ollama model and server logs.');
        }

        return back()->with('insight', [
            'process' => $process->name,
            'text' => trim($insight),
            'model' => $model,
        ]);
    }

    private function promptFor(Process $process): string
    {
        $stages = $process->stages->map(fn ($stage) => "{$stage->name}: {$stage->status}")->implode('; ');
        $opportunities = $process->opportunities->map(fn ($item) => "{$item->title} ({$item->priority}, {$item->status})")->implode('; ');

        return "You are an operations analyst. Use only this process record and return three concise, practical improvement recommendations. Do not invent facts. Process: {$process->name}. Category: {$process->category}. Description: {$process->description}. Current cycle time: {$process->cycle_time_hours} hours; target: {$process->target_cycle_hours} hours. Workflow stages: {$stages}. Existing opportunities: {$opportunities}.";
    }
}
