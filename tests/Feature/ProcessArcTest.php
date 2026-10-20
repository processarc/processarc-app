<?php

namespace Tests\Feature;

use App\Models\Process;
use App\Models\OptimizationOpportunity;
use App\Models\User;
use App\Models\WorkflowStage;
use App\Models\ProcessMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProcessArcTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_product_pages_render_with_their_positioning(): void
    {
        $this->get('/')->assertOk()->assertSee('See the work');
        $this->get('/architecture')->assertOk()->assertSee('SQL data layer');
        $this->get('/product')->assertOk()->assertSee('NVIDIA NeMo')->assertSee('NOT INTEGRATED');
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('product'));
        $this->get('/platform/nvidia')
            ->assertStatus(301)
            ->assertRedirect(route('product'));
        $this->get('/company')->assertOk()->assertSee('Pirashanth Mahendran');
        $this->get('/resources')->assertOk()->assertSee('Explore the demo workspace');
        $this->get('/privacy')->assertOk()->assertSee('Privacy notice');
        $this->get('/terms')->assertOk()->assertSee('Terms of use');
        $this->get('/security')->assertOk()->assertSee('Security overview');
        $this->get('/ai-governance')->assertOk()->assertSee('AI usage & governance');
    }

    public function test_process_insight_analyzer_uses_rules_when_ai_providers_are_unavailable(): void
    {
        config([
            'services.nvidia_nim.api_key' => null,
            'services.nvidia_nim.model' => null,
            'services.ollama.model' => null,
        ]);

        $this->postJson(route('api.process-insights'), ['sample' => true])
            ->assertOk()
            ->assertJsonPath('event_count', 12)
            ->assertJsonPath('nvidia_integrated', false)
            ->assertJsonPath('engine', 'Rules-based fallback');
    }

    public function test_process_insight_analyzer_only_marks_nvidia_when_nim_answers(): void
    {
        Http::fake([
            'https://nim.example.test/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Review the longest stage with its owner.']]],
            ]),
        ]);
        config([
            'services.nvidia_nim.api_key' => 'test-key',
            'services.nvidia_nim.endpoint' => 'https://nim.example.test/v1/chat/completions',
            'services.nvidia_nim.model' => 'test-model',
            'services.ollama.model' => null,
        ]);

        $this->postJson(route('api.process-insights'), ['sample' => true])
            ->assertOk()
            ->assertJsonPath('engine', 'NVIDIA NIM')
            ->assertJsonPath('nvidia_integrated', true);

        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://nim.example.test/v1/chat/completions'
            && $request['model'] === 'test-model'
            && ! str_contains($request['messages'][1]['content'], 'Invoice to payment'));
    }

    public function test_process_insight_analyzer_uses_ollama_when_configured(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/generate' => Http::response(['response' => 'Review the longest stage.']),
        ]);
        config([
            'services.nvidia_nim.api_key' => null,
            'services.nvidia_nim.model' => null,
            'services.ollama.model' => 'qwen2.5:7b',
        ]);

        $this->postJson(route('api.process-insights'), ['sample' => true])
            ->assertOk()
            ->assertJsonPath('engine', 'Ollama / Qwen fallback')
            ->assertJsonPath('nvidia_integrated', false);
    }

    public function test_process_insight_analyzer_falls_back_when_nim_fails(): void
    {
        Http::fake([
            'https://nim.example.test/v1/chat/completions' => Http::response([], 503),
        ]);
        config([
            'services.nvidia_nim.api_key' => 'test-key',
            'services.nvidia_nim.endpoint' => 'https://nim.example.test/v1/chat/completions',
            'services.nvidia_nim.model' => 'test-model',
            'services.ollama.model' => null,
        ]);

        $this->postJson(route('api.process-insights'), ['sample' => true])
            ->assertOk()
            ->assertJsonPath('engine', 'Rules-based fallback')
            ->assertJsonPath('nvidia_integrated', false);
    }

    public function test_process_insight_analyzer_handles_csv_rows_with_missing_trailing_columns(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'events.csv',
            "\xEF\xBB\xBFstage,start,end,owner\nReview,2026-01-01 08:00,2026-01-01 10:00\n"
        );
        config(['services.ollama.model' => null]);

        $this->postJson(route('api.process-insights'), ['file' => $file])
            ->assertOk()
            ->assertJsonPath('event_count', 1)
            ->assertJsonPath('stages.0.stage', 'Review');
    }

    public function test_process_insight_analyzer_rejects_more_than_500_events(): void
    {
        $rows = ['stage,start,end'];
        for ($index = 0; $index < 501; $index++) {
            $rows[] = 'Review,2026-01-01 08:00,2026-01-01 09:00';
        }
        $file = UploadedFile::fake()->createWithContent('events.csv', implode("\n", $rows));

        $this->postJson(route('api.process-insights'), ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_process_insight_analyzer_rejects_files_over_200_kb(): void
    {
        $csv = "stage,start,end\nReview,2026-01-01 08:00,2026-01-01 09:00\n";
        $csv .= str_repeat('x', 200 * 1024 + 1 - strlen($csv));
        $file = UploadedFile::fake()->createWithContent('events.csv', $csv);

        $this->postJson(route('api.process-insights'), ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_process_insight_analyzer_reports_rows_missing_required_values(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'events.csv',
            "stage,start,end\nReview,2026-01-01 08:00\n"
        );

        $this->postJson(route('api.process-insights'), ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_process_insight_analyzer_rejects_duplicate_csv_headers(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'events.csv',
            "stage,start,end,end\nReview,2026-01-01 08:00,2026-01-01 10:00,2026-01-01 11:00\n"
        );

        $this->postJson(route('api.process-insights'), ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_application_requires_authentication(): void
    {
        $this->get('/app')->assertRedirect(route('login'));
    }

    public function test_seeded_demo_user_can_sign_in(): void
    {
        $this->seed();

        $this->post('/login', [
            'email' => 'demo@processarc.net',
            'password' => 'ProcessArcDemo!2026',
        ])->assertRedirect(route('app.dashboard'));

        $this->assertAuthenticated();
        $this->get('/app')->assertOk()->assertSee('Process registry')->assertSee('ProcessArc Assistant');
    }

    public function test_manager_can_create_process_and_initial_metric_snapshot(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->post('/app/processes', [
            'name' => 'Purchase approval',
            'category' => 'Finance',
            'description' => 'Approval from request through final decision.',
            'status' => 'active',
            'cycle_time_hours' => 12,
            'target_cycle_hours' => 8,
            'exception_count' => 3,
        ])->assertRedirect();

        $process = Process::where('name', 'Purchase approval')->firstOrFail();
        $this->assertDatabaseHas('process_metrics', [
            'process_id' => $process->id,
            'cycle_time_hours' => 12,
        ]);
        $this->assertDatabaseHas('activity_logs', ['event' => 'Process created']);

        $this->put(route('app.processes.update', $process), [
            'name' => $process->name,
            'category' => $process->category,
            'description' => $process->description,
            'status' => $process->status,
            'cycle_time_hours' => 13.5,
            'target_cycle_hours' => $process->target_cycle_hours,
            'exception_count' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('process_metrics', [
            'process_id' => $process->id,
            'cycle_time_hours' => 13.5,
            'exception_count' => 2,
        ]);
        $this->assertSame(1, ProcessMetric::where('process_id', $process->id)->count());
    }

    public function test_manager_can_assign_a_process_owner_and_dependencies(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $owner = User::factory()->create(['name' => 'Operations lead']);
        $prerequisite = Process::create(['name' => 'Vendor validation', 'category' => 'Procurement', 'status' => 'active']);

        $this->actingAs($manager)->post('/app/processes', [
            'name' => 'Purchase order release',
            'category' => 'Procurement',
            'status' => 'active',
            'cycle_time_hours' => 4,
            'target_cycle_hours' => 2,
            'exception_count' => 0,
            'owner_id' => $owner->id,
            'dependency_ids_submitted' => '1',
            'dependency_ids' => [$prerequisite->id],
        ])->assertRedirect();

        $process = Process::where('name', 'Purchase order release')->firstOrFail();
        $this->assertSame($owner->id, $process->owner_id);
        $this->assertDatabaseHas('process_dependencies', [
            'process_id' => $process->id,
            'depends_on_process_id' => $prerequisite->id,
        ]);
        $this->get(route('app.processes.show', $process))->assertSee('Vendor validation');
    }

    public function test_analyst_cannot_create_processes(): void
    {
        $analyst = User::factory()->create(['role' => 'analyst']);

        $this->actingAs($analyst)->post('/app/processes', [])->assertForbidden();
        $this->assertDatabaseCount('processes', 0);
    }

    public function test_registry_search_filters_process_names(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        Process::create(['name' => 'Invoice review', 'category' => 'Finance', 'status' => 'active', 'owner_id' => $manager->id]);
        Process::create(['name' => 'Customer activation', 'category' => 'Customer operations', 'status' => 'active', 'owner_id' => $manager->id]);

        $this->actingAs($manager)
            ->get('/app/processes?q=invoice')
            ->assertOk()
            ->assertSee('Invoice review')
            ->assertDontSee('Customer activation');
    }

    public function test_workflow_opportunity_and_reporting_pages_work(): void
    {
        $this->seed();
        $manager = User::where('email', 'demo@processarc.net')->firstOrFail();
        $process = Process::where('name', 'Invoice to payment')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('app.processes.show', $process))
            ->assertOk()
            ->assertSee('Workflow map');
        $this->get(route('app.workflows.index'))->assertOk()->assertSee('Invoice to payment');
        $this->get(route('app.analysis'))->assertOk()->assertSee('Exception tracking');
        $this->get(route('app.optimizations.index'))->assertOk()->assertSee('Improvement opportunities');
        $this->get(route('app.activity.index'))
            ->assertOk()
            ->assertSee('Recent changes')
            ->assertSee('Standardize invoice exception routing');

        $this->post(route('app.workflows.store', $process), ['name' => 'Compliance check'])
            ->assertRedirect();
        $stage = WorkflowStage::where('process_id', $process->id)->where('name', 'Compliance check')->firstOrFail();
        $this->patch(route('app.workflows.update', $stage), ['status' => 'completed'])
            ->assertRedirect();
        $this->assertDatabaseHas('workflow_stages', ['id' => $stage->id, 'status' => 'completed']);

        $this->post(route('app.optimizations.store-from-workspace'), [
            'process_id' => $process->id,
            'title' => 'Reduce duplicate approvals',
            'description' => 'Review repeated approval checks.',
            'priority' => 'medium',
            'owner_name' => 'Finance',
            'impact_hours' => 4,
        ])->assertRedirect();
        $opportunity = OptimizationOpportunity::where('title', 'Reduce duplicate approvals')->firstOrFail();
        $this->patch(route('app.optimizations.update', $opportunity), ['status' => 'in_progress'])
            ->assertRedirect();
        $this->assertDatabaseHas('optimization_opportunities', ['id' => $opportunity->id, 'status' => 'in_progress']);
    }

    public function test_unconfigured_local_model_returns_a_clear_message(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $process = Process::create(['name' => 'Expense review', 'category' => 'Finance', 'status' => 'active']);
        config(['services.ollama.model' => null]);

        $this->actingAs($manager)
            ->from('/app')
            ->post(route('app.insights.generate'), ['process_id' => $process->id])
            ->assertRedirect('/app')
            ->assertSessionHas('error');
    }

    public function test_local_model_can_return_a_process_recommendation(): void
    {
        Http::fake(['http://127.0.0.1:11434/api/generate' => Http::response(['response' => 'Review the approval queue first.'], 200)]);
        config(['services.ollama.model' => 'qwen2.5:7b']);
        $manager = User::factory()->create(['role' => 'manager']);
        $process = Process::create(['name' => 'Invoice review', 'category' => 'Finance', 'status' => 'active']);

        $this->actingAs($manager)
            ->from('/app')
            ->post(route('app.insights.generate'), ['process_id' => $process->id])
            ->assertRedirect('/app')
            ->assertSessionHas('insight.text', 'Review the approval queue first.');

        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'http://127.0.0.1:11434/api/generate'
            && $request['model'] === 'qwen2.5:7b'
            && str_contains($request['prompt'], 'Invoice review'));
    }
}
