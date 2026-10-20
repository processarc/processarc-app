<?php

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProcessInsightAnalyzer
{
    private const SAMPLE_CSV = <<<'CSV'
process,stage,start,end,owner
Invoice to payment,Intake,2026-09-01 08:00,2026-09-01 10:00,Finance
Invoice to payment,Review,2026-09-01 10:00,2026-09-02 00:00,Reviewer A
Invoice to payment,Approval,2026-09-02 00:00,2026-09-02 02:00,Manager
Invoice to payment,Fulfilment,2026-09-02 02:00,2026-09-02 05:00,Finance
Invoice to payment,Intake,2026-09-02 08:00,2026-09-02 10:30,Finance
Invoice to payment,Review,2026-09-02 10:30,2026-09-03 01:00,Reviewer B
Invoice to payment,Approval,2026-09-03 01:00,2026-09-03 02:30,Manager
Invoice to payment,Fulfilment,2026-09-03 02:30,2026-09-03 05:00,Finance
Invoice to payment,Intake,2026-09-03 08:00,2026-09-03 10:15,Finance
Invoice to payment,Review,2026-09-03 10:15,2026-09-04 00:45,Reviewer A
Invoice to payment,Approval,2026-09-04 00:45,2026-09-04 03:15,Manager
Invoice to payment,Fulfilment,2026-09-04 03:15,2026-09-04 05:45,Finance
CSV;

    public function analyze(?string $contents): array
    {
        $rows = $this->parseRows($contents ?? self::SAMPLE_CSV);

        if (count($rows) > 500) {
            throw ValidationException::withMessages(['file' => 'The analyzer accepts up to 500 event rows at a time.']);
        }

        $groups = [];

        foreach ($rows as $index => $row) {
            $stage = trim((string) ($row['stage'] ?? ''));
            $start = trim((string) ($row['start'] ?? ''));
            $end = trim((string) ($row['end'] ?? ''));

            if ($stage === '' || mb_strlen($stage) > 80 || $start === '' || $end === '') {
                throw ValidationException::withMessages(['file' => 'Each CSV row needs a stage, start and end value.']);
            }

            try {
                $startDate = new DateTimeImmutable($start);
                $endDate = new DateTimeImmutable($end);
            } catch (\Exception) {
                throw ValidationException::withMessages(['file' => 'Row '.($index + 2).' has an invalid start or end date.']);
            }

            $hours = ($endDate->getTimestamp() - $startDate->getTimestamp()) / 3600;
            if ($hours <= 0 || $hours > 8760) {
                throw ValidationException::withMessages(['file' => 'Row '.($index + 2).' must have a positive duration of one year or less.']);
            }

            $key = mb_strtolower($stage);
            $groups[$key] ??= ['stage' => $stage, 'total_hours' => 0.0, 'events' => 0];
            $groups[$key]['total_hours'] += $hours;
            $groups[$key]['events']++;
        }

        if ($groups === []) {
            throw ValidationException::withMessages(['file' => 'The CSV must contain at least one valid process event.']);
        }

        $stages = array_values(array_map(static fn (array $group): array => [
            'stage' => $group['stage'],
            'average_hours' => round($group['total_hours'] / $group['events'], 1),
            'event_count' => $group['events'],
        ], $groups));
        usort($stages, static fn (array $left, array $right): int => $right['average_hours'] <=> $left['average_hours']);

        $bottleneck = $stages[0];
        $suggestion = $this->suggestion($stages);

        return [
            'stages' => $stages,
            'bottleneck' => $bottleneck['stage'],
            'insight' => $suggestion['text'],
            'engine' => $suggestion['engine'],
            'nvidia_integrated' => $suggestion['engine'] === 'NVIDIA NIM',
            'event_count' => count($rows),
        ];
    }

    private function parseRows(string $contents): array
    {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw ValidationException::withMessages(['file' => 'The uploaded CSV could not be read.']);
        }

        fwrite($stream, $contents);
        rewind($stream);
        $headers = fgetcsv($stream);

        if (! is_array($headers)) {
            fclose($stream);

            throw ValidationException::withMessages(['file' => 'The CSV file is empty.']);
        }

        $headers = array_map(static fn ($header): string => mb_strtolower(trim((string) $header)), $headers);
        if (isset($headers[0])) {
            $headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
        }

        if (count(array_unique($headers)) !== count($headers)
            || ! in_array('stage', $headers, true)
            || ! in_array('start', $headers, true)
            || ! in_array('end', $headers, true)) {
            fclose($stream);

            throw ValidationException::withMessages(['file' => 'CSV headers must be unique and include stage, start and end.']);
        }

        $rows = [];
        while (($values = fgetcsv($stream)) !== false) {
            if ($values === [null] || $values === []) {
                continue;
            }

            $rows[] = array_combine($headers, array_slice(array_pad($values, count($headers), ''), 0, count($headers)));

            if (count($rows) > 500) {
                fclose($stream);

                throw ValidationException::withMessages(['file' => 'The analyzer accepts up to 500 event rows at a time.']);
            }
        }

        fclose($stream);

        return $rows;
    }

    private function suggestion(array $stages): array
    {
        $bottleneck = $stages[0];
        $statistics = collect($stages)->map(fn (array $stage): string => $stage['stage'].' averages '.$stage['average_hours'].'h across '.$stage['event_count'].' events.')->implode(' ');
        $prompt = "Analyze only these computed stage averages. Give one concise, practical investigation suggestion for the slowest stage. Do not claim causation or invent facts. Stage data: {$statistics} Slowest stage: {$bottleneck['stage']} at {$bottleneck['average_hours']} hours.";
        $nim = $this->nvidiaSuggestion($prompt);

        if ($nim !== null) {
            return ['text' => $nim, 'engine' => 'NVIDIA NIM'];
        }

        $ollama = $this->ollamaSuggestion($prompt);
        if ($ollama !== null) {
            return ['text' => $ollama, 'engine' => 'Ollama / Qwen fallback'];
        }

        return [
            'text' => $bottleneck['stage'].' averages about '.$bottleneck['average_hours'].' hours across '.$bottleneck['event_count'].' events. Consider reviewing queue time, reviewer capacity and an agreed service-level target.',
            'engine' => 'Rules-based fallback',
        ];
    }

    private function nvidiaSuggestion(string $prompt): ?string
    {
        $key = config('services.nvidia_nim.api_key');
        $endpoint = config('services.nvidia_nim.endpoint');
        $model = config('services.nvidia_nim.model');

        if (! $key || ! $endpoint || ! $model) {
            return null;
        }

        try {
            $response = Http::withToken($key)->timeout(15)->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a concise ProcessArc process analysis assistant. Only discuss process-improvement analysis. Do not claim NVIDIA endorsement. Do not invent facts. Suggestions require human review.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'max_tokens' => 120,
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Process Insight Analyzer NVIDIA NIM request failed.', ['message' => $exception->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Process Insight Analyzer NVIDIA NIM returned an unsuccessful response.', ['status' => $response->status()]);

            return null;
        }

        $text = $response->json('choices.0.message.content');

        return is_string($text) && trim($text) !== '' ? trim($text) : null;
    }

    private function ollamaSuggestion(string $prompt): ?string
    {
        $model = config('services.ollama.model');
        if (! $model) {
            return null;
        }

        try {
            $response = Http::timeout(15)->post(rtrim(config('services.ollama.url'), '/').'/api/generate', [
                'model' => $model,
                'prompt' => "You are a concise ProcessArc process analysis assistant. Only discuss process improvement. Do not invent facts. Suggestions require human review.\n\n{$prompt}",
                'stream' => false,
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Process Insight Analyzer Ollama request failed.', ['message' => $exception->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Process Insight Analyzer Ollama returned an unsuccessful response.', ['status' => $response->status()]);

            return null;
        }

        $text = $response->json('response');

        return is_string($text) && trim($text) !== '' ? trim($text) : null;
    }
}
