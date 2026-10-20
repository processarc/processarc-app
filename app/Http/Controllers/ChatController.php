<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:1000']]);
        $message = trim($data['message']);

        if (! $this->isProcessArcQuestion($message)) {
            return response()->json(['reply' => 'I can help with ProcessArc, the demo workspace, process improvement, AI governance, or connecting you with the team. What would you like to know?']);
        }

        $model = config('services.ollama.model');
        if (! $model) {
            return response()->json(['reply' => $this->fallbackReply($message), 'engine' => 'ProcessArc assistant']);
        }

        try {
            $response = Http::timeout(12)->post(rtrim(config('services.ollama.url'), '/').'/api/generate', [
                'model' => $model,
                'prompt' => "You are ProcessArc Assistant on processarc.net. Answer only questions about ProcessArc, this product's business-process capabilities, its demo, AI governance, and contacting ProcessArc. Be concise, factual, and never claim an NVIDIA partnership, endorsement, certification, or integration. AI suggestions need human review. If you do not know a company-specific answer, direct the user to hello@processarc.net. Do not request sensitive process or personal data.\n\nVisitor question: {$message}",
                'stream' => false,
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('ProcessArc website assistant could not reach local Ollama.', ['message' => $exception->getMessage()]);

            return response()->json(['reply' => $this->fallbackReply($message), 'engine' => 'ProcessArc assistant']);
        }

        $answer = $response->json('response');
        if (! $response->successful() || ! is_string($answer) || trim($answer) === '') {
            Log::warning('ProcessArc website assistant received an invalid Ollama response.', ['status' => $response->status()]);

            return response()->json(['reply' => $this->fallbackReply($message), 'engine' => 'ProcessArc assistant']);
        }

        return response()->json(['reply' => trim($answer), 'engine' => 'Local AI']);
    }

    private function isProcessArcQuestion(string $message): bool
    {
        return preg_match('/processarc|process|workflow|bottleneck|cycle time|improv|optim|demo|pricing|plan|ai|model|ollama|qwen|nvidia|data|security|governance|human|contact|sales|book|call/i', $message) === 1;
    }

    private function fallbackReply(string $message): string
    {
        if (preg_match('/demo|try|workspace/i', $message)) {
            return 'The demo uses fictional process records to show the registry, workflows, analysis, and improvement workspace. Visit the demo sign-in to explore it. Please do not enter confidential business data.';
        }

        if (preg_match('/price|plan|cost/i', $message)) {
            return 'ProcessArc pricing has not been published. Contact hello@processarc.net to discuss an assessment or enterprise requirements.';
        }

        if (preg_match('/ai|model|ollama|qwen|nvidia|governance/i', $message)) {
            return 'ProcessArc can use a configured local Qwen model through Ollama for optional suggestions. NVIDIA technologies shown on the showcase are proposed options only. AI suggestions are reviewed by a person and never change a process automatically.';
        }

        if (preg_match('/contact|sales|book|call|human/i', $message)) {
            return 'You can reach the ProcessArc team at hello@processarc.net or call Sri Lanka +94 77 000 1202 / United States +1 213-555-0102.';
        }

        return 'ProcessArc provides a structured workspace for process records, workflow stages, cycle-time analysis, and owned improvement opportunities. Ask me about the demo, AI governance, or contacting the team.';
    }
}
