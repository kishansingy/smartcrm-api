<?php

namespace Database\Seeders;

use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Task\Models\Task;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tenant   = Tenant::where('slug', 'demo')->firstOrFail();
        $users    = User::where('tenant_id', $tenant->id)->pluck('id')->toArray();
        $contacts = Contact::where('tenant_id', $tenant->id)->pluck('id')->toArray();
        $leads    = Lead::where('tenant_id', $tenant->id)->pluck('id')->toArray();

        $tasks = [
            ['title' => 'Follow up with TechCorp',        'type' => 'call',      'priority' => 'high',   'status' => 'pending',    'due_date' => now()->addDay(),    'contact_idx' => 0],
            ['title' => 'Send Startup XYZ proposal',      'type' => 'email',     'priority' => 'high',   'status' => 'pending',    'due_date' => now()->addDays(2),  'contact_idx' => 1],
            ['title' => 'Schedule demo with Enterprise',  'type' => 'meeting',   'priority' => 'medium', 'status' => 'pending',    'due_date' => now()->addDays(3),  'contact_idx' => 3],
            ['title' => 'Prepare contract for Partners',  'type' => 'proposal',  'priority' => 'medium', 'status' => 'completed',  'due_date' => now()->subDay(),    'contact_idx' => 4, 'completed_at' => now()->subHours(2)],
            ['title' => 'Overdue check-in call',          'type' => 'call',      'priority' => 'low',    'status' => 'pending',    'due_date' => now()->subDays(3),  'contact_idx' => 2],
            ['title' => 'Qualify hot lead IO',            'type' => 'call',      'priority' => 'high',   'status' => 'pending',    'due_date' => now()->addHours(4), 'lead_idx'    => 2],
            ['title' => 'Send follow-up email to Kevin',  'type' => 'email',     'priority' => 'medium', 'status' => 'pending',    'due_date' => now()->addDays(1),  'lead_idx'    => 1],
            ['title' => 'Review proposal from Mike',      'type' => 'follow_up', 'priority' => 'medium', 'status' => 'in_progress','due_date' => now()->addDays(2),  'lead_idx'    => 4],
        ];

        foreach ($tasks as $data) {
            $assignedTo = $users[array_rand($users)];
            $contactId  = isset($data['contact_idx']) ? ($contacts[$data['contact_idx']] ?? null) : null;
            $leadId     = isset($data['lead_idx'])    ? ($leads[$data['lead_idx']]    ?? null) : null;

            Task::firstOrCreate(
                ['tenant_id' => $tenant->id, 'title' => $data['title']],
                [
                    'tenant_id'    => $tenant->id,
                    'created_by'   => $assignedTo,
                    'assigned_to'  => $assignedTo,
                    'contact_id'   => $contactId,
                    'lead_id'      => $leadId,
                    'title'        => $data['title'],
                    'type'         => $data['type'],
                    'priority'     => $data['priority'],
                    'status'       => $data['status'],
                    'due_date'     => $data['due_date'],
                    'completed_at' => $data['completed_at'] ?? null,
                ]
            );
        }

        $this->command->info(count($tasks) . ' tasks seeded.');
    }
}
