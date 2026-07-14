<?php

namespace Database\Seeders;

use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Pipeline\Models\Deal;
use App\Domain\Pipeline\Models\Pipeline;
use App\Domain\Pipeline\Models\PipelineStage;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PipelineAndDealSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->firstOrFail();
        $agents = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['sales_agent', 'sales_manager']))
            ->pluck('id')
            ->toArray();

        // Create default pipeline
        $pipeline = Pipeline::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Sales Pipeline'],
            [
                'description' => 'Main sales pipeline',
                'currency'    => 'USD',
                'is_default'  => true,
                'is_active'   => true,
            ]
        );

        // Create stages
        $stagesData = [
            ['name' => 'New',          'color' => '#6366f1', 'position' => 1, 'probability' => 10,  'is_won' => false, 'is_lost' => false],
            ['name' => 'Contacted',    'color' => '#3b82f6', 'position' => 2, 'probability' => 25,  'is_won' => false, 'is_lost' => false],
            ['name' => 'Qualified',    'color' => '#f59e0b', 'position' => 3, 'probability' => 50,  'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal',     'color' => '#8b5cf6', 'position' => 4, 'probability' => 70,  'is_won' => false, 'is_lost' => false],
            ['name' => 'Negotiation',  'color' => '#ec4899', 'position' => 5, 'probability' => 85,  'is_won' => false, 'is_lost' => false],
            ['name' => 'Won',          'color' => '#10b981', 'position' => 6, 'probability' => 100, 'is_won' => true,  'is_lost' => false],
            ['name' => 'Lost',         'color' => '#ef4444', 'position' => 7, 'probability' => 0,   'is_won' => false, 'is_lost' => true],
        ];

        $stages = [];
        foreach ($stagesData as $stageData) {
            $stages[$stageData['name']] = PipelineStage::firstOrCreate(
                ['pipeline_id' => $pipeline->id, 'name' => $stageData['name']],
                $stageData
            );
        }

        $contacts = Contact::where('tenant_id', $tenant->id)->get();
        $leads    = Lead::where('tenant_id', $tenant->id)->get();

        $deals = [
            ['title' => 'TechCorp Software License',   'value' => 25000, 'stage' => 'Qualified',   'status' => 'open', 'probability' => 50, 'expected_close_date' => now()->addDays(30)],
            ['title' => 'Startup XYZ Onboarding',      'value' => 8500,  'stage' => 'Proposal',    'status' => 'open', 'probability' => 70, 'expected_close_date' => now()->addDays(14)],
            ['title' => 'Enterprise IO Annual Plan',   'value' => 60000, 'stage' => 'Negotiation', 'status' => 'open', 'probability' => 85, 'expected_close_date' => now()->addDays(7)],
            ['title' => 'Partners Net Integration',    'value' => 15000, 'stage' => 'Won',         'status' => 'won',  'probability' => 100,'expected_close_date' => now()->subDays(5), 'closed_at' => now()->subDays(5)],
            ['title' => 'Cold Prospect Deal',          'value' => 3200,  'stage' => 'Contacted',   'status' => 'open', 'probability' => 25, 'expected_close_date' => now()->addDays(45)],
            ['title' => 'Hot Lead IO Contract',        'value' => 42000, 'stage' => 'Proposal',    'status' => 'open', 'probability' => 70, 'expected_close_date' => now()->addDays(20)],
            ['title' => 'Old Client Renewal',          'value' => 12000, 'stage' => 'Lost',        'status' => 'lost', 'probability' => 0,  'expected_close_date' => now()->subDays(10), 'closed_at' => now()->subDays(10), 'lost_reason' => 'Budget constraints'],
            ['title' => 'New Inbound Lead',            'value' => 5500,  'stage' => 'New',         'status' => 'open', 'probability' => 10, 'expected_close_date' => now()->addDays(60)],
        ];

        foreach ($deals as $i => $data) {
            $stageName = $data['stage'];
            $stage     = $stages[$stageName];
            $contact   = $contacts->get($i % $contacts->count());
            $lead      = $leads->get($i % $leads->count());

            Deal::firstOrCreate(
                ['tenant_id' => $tenant->id, 'title' => $data['title']],
                [
                    'tenant_id'           => $tenant->id,
                    'pipeline_id'         => $pipeline->id,
                    'stage_id'            => $stage->id,
                    'contact_id'          => $contact?->id,
                    'lead_id'             => $lead?->id,
                    'assigned_to'         => $agents[array_rand($agents)],
                    'value'               => $data['value'],
                    'currency'            => 'USD',
                    'probability'         => $data['probability'],
                    'status'              => $data['status'],
                    'expected_close_date' => $data['expected_close_date'],
                    'closed_at'           => $data['closed_at'] ?? null,
                    'lost_reason'         => $data['lost_reason'] ?? null,
                ]
            );
        }

        $this->command->info('Pipeline, stages and ' . count($deals) . ' deals seeded.');
    }
}
