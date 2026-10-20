<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Process;
use App\Models\ProcessMetric;
use App\Models\OptimizationOpportunity;
use App\Models\WorkflowStage;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction() && ! env('PROCESSARC_ENABLE_DEMO', false)) {
            return;
        }

        $manager = User::updateOrCreate(['email' => 'demo@processarc.net'], [
            'name' => 'Alex Morgan',
            'password' => Hash::make('ProcessArcDemo!2026'),
            'role' => 'manager',
        ]);

        $records = [
            ['name' => 'Invoice to payment', 'category' => 'Finance', 'description' => 'The end-to-end accounts payable process, from invoice receipt through payment release.', 'status' => 'at_risk', 'cycle_time_hours' => 38, 'target_cycle_hours' => 24],
            ['name' => 'Customer onboarding', 'category' => 'Customer operations', 'description' => 'New customer verification, account provisioning and first-value activation.', 'status' => 'active', 'cycle_time_hours' => 42, 'target_cycle_hours' => 36],
            ['name' => 'Employee access request', 'category' => 'IT operations', 'description' => 'Identity and application access provisioning for joiners and role changes.', 'status' => 'active', 'cycle_time_hours' => 9, 'target_cycle_hours' => 12],
            ['name' => 'Order fulfillment', 'category' => 'Supply chain', 'description' => 'Validated orders move through inventory allocation, pick, pack and dispatch.', 'status' => 'paused', 'cycle_time_hours' => 31, 'target_cycle_hours' => 24],
            ['name' => 'Supplier onboarding', 'category' => 'Procurement', 'description' => 'Due diligence, compliance checks and vendor master creation.', 'status' => 'active', 'cycle_time_hours' => 56, 'target_cycle_hours' => 48],
        ];

        foreach ($records as $record) {
            $process = Process::updateOrCreate(['name' => $record['name']], $record + ['owner_id' => $manager->id]);

            foreach (['Intake', 'Review', 'Approval', 'Completion'] as $position => $name) {
                WorkflowStage::updateOrCreate(
                    ['process_id' => $process->id, 'position' => $position + 1],
                    ['name' => $name, 'owner_name' => $manager->name, 'status' => $position === 0 ? 'completed' : ($position === 1 ? 'in_progress' : 'not_started')]
                );
            }

            foreach ([1.18, 1.12, 1.07, 1.03, 1.0] as $week => $factor) {
                $date = now()->startOfDay()->subWeeks(4 - $week)->toDateString();
                ProcessMetric::updateOrCreate(
                    ['process_id' => $process->id, 'measured_at' => $date],
                    ['cycle_time_hours' => round($process->cycle_time_hours * $factor, 1), 'exception_count' => max(0, 8 - $week)]
                );
            }
        }

        $invoice = Process::where('name', 'Invoice to payment')->firstOrFail();
        $onboarding = Process::where('name', 'Customer onboarding')->firstOrFail();
        foreach ([
            [$invoice, 'Standardize invoice exception routing', 'high', 'approved', 'Alex Morgan', 8],
            [$invoice, 'Automate three-way match checks', 'medium', 'identified', 'Jordan Lee', 12],
            [$onboarding, 'Remove duplicate customer data entry', 'high', 'in_progress', 'Sam Rivera', 6],
        ] as [$process, $title, $priority, $status, $owner, $impact]) {
            OptimizationOpportunity::updateOrCreate(
                ['process_id' => $process->id, 'title' => $title],
                ['priority' => $priority, 'status' => $status, 'owner_name' => $owner, 'impact_hours' => $impact]
            );
        }

        foreach ([
            ['Process created', 'Added Invoice to payment to the process registry.'],
            ['Workflow stage updated', 'Marked Review as in progress for Invoice to payment.'],
            ['Opportunity identified', 'Identified Standardize invoice exception routing for Invoice to payment.'],
            ['Opportunity status updated', 'Moved Remove duplicate customer data entry to in progress.'],
        ] as [$event, $description]) {
            ActivityLog::updateOrCreate(
                ['event' => $event, 'description' => $description],
                ['user_id' => $manager->id]
            );
        }
    }
}
