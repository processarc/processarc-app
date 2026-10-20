<?php

namespace App\Http\Controllers;

use App\Services\ProcessInsightAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProcessInsightController extends Controller
{
    public function __invoke(Request $request, ProcessInsightAnalyzer $analyzer): JsonResponse
    {
        $validated = $request->validate([
            'sample' => ['sometimes', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:csv,txt', 'max:200'],
        ]);

        $contents = null;
        if (! ($validated['sample'] ?? false)) {
            if (! $request->hasFile('file')) {
                throw ValidationException::withMessages(['file' => 'Choose a CSV file or select the sample data.']);
            }

            $contents = file_get_contents($request->file('file')->getRealPath());
            if ($contents === false) {
                throw ValidationException::withMessages(['file' => 'The uploaded CSV could not be read.']);
            }
        }

        return response()->json($analyzer->analyze($contents));
    }
}
