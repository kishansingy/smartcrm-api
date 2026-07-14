<?php

namespace Database\Seeders;

use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactAndLeadSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->firstOrFail();
        $agents = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['sales_agent', 'sales_manager']))
            ->pluck('id')
            ->toArray();

        $contacts = [
            ['first_name' => 'Alice',   'last_name' => 'Johnson',  'email' => 'alice@techcorp.com',    'phone' => env('TEST_PHONE_NUMBER', '+91-XXXXXXXXXX'), 'company' => 'TechCorp',       'type' => 'business',    'status' => 'active',   'source' => 'website'],
            ['first_name' => 'Bob',     'last_name' => 'Williams', 'email' => 'bob@startupxyz.com',    'phone' => '+1-555-100-0002', 'company' => 'Startup XYZ',    'type' => 'business',    'status' => 'active',   'source' => 'referral'],
            ['first_name' => 'Carol',   'last_name' => 'Davis',    'email' => 'carol@example.com',     'phone' => '+1-555-100-0003', 'company' => null,             'type' => 'individual',  'status' => 'active',   'source' => 'cold_call'],
            ['first_name' => 'David',   'last_name' => 'Brown',    'email' => 'david@enterprise.io',   'phone' => '+1-555-100-0004', 'company' => 'Enterprise IO',  'type' => 'business',    'status' => 'active',   'source' => 'linkedin'],
            ['first_name' => 'Emma',    'last_name' => 'Wilson',   'email' => 'emma@partners.net',     'phone' => '+1-555-100-0005', 'company' => 'Partners Net',   'type' => 'partner',     'status' => 'active',   'source' => 'referral'],
            ['first_name' => 'Frank',   'last_name' => 'Moore',    'email' => 'frank@oldclient.com',   'phone' => '+1-555-100-0006', 'company' => 'Old Client Inc', 'type' => 'business',    'status' => 'inactive', 'source' => 'website'],
            ['first_name' => 'Grace',   'last_name' => 'Taylor',   'email' => 'grace@vendor.co',       'phone' => '+1-555-100-0007', 'company' => 'Vendor Co',      'type' => 'vendor',      'status' => 'active',   'source' => 'email'],
            ['first_name' => 'Henry',   'last_name' => 'Anderson', 'email' => 'henry@prospect.com',    'phone' => '+1-555-100-0008', 'company' => 'Prospect Ltd',   'type' => 'business',    'status' => 'active',   'source' => 'cold_call'],
        ];

        foreach ($contacts as $data) {
            Contact::firstOrCreate(
                ['email' => $data['email'], 'tenant_id' => $tenant->id],
                array_merge($data, [
                    'tenant_id'   => $tenant->id,
                    'assigned_to' => $agents[array_rand($agents)],
                    'tags'        => ['demo'],
                    'notes'       => 'Seeded test contact.',
                ])
            );
        }

        $leads = [
            ['first_name' => 'Ivan',    'last_name' => 'Clark',    'email' => 'ivan@newlead.com',    'phone' => '+1-555-200-0001', 'company' => 'New Lead Co',    'source' => 'website',      'status' => 'new',          'priority' => 'high',   'score' => 80],
            ['first_name' => 'Julia',   'last_name' => 'Lewis',    'email' => 'julia@prospect2.com', 'phone' => '+1-555-200-0002', 'company' => 'Prospect Two',   'source' => 'referral',     'status' => 'contacted',    'priority' => 'medium', 'score' => 60],
            ['first_name' => 'Kevin',   'last_name' => 'Walker',   'email' => 'kevin@hotlead.io',    'phone' => '+1-555-200-0003', 'company' => 'Hot Lead IO',    'source' => 'social_media', 'status' => 'qualified',    'priority' => 'high',   'score' => 90],
            ['first_name' => 'Laura',   'last_name' => 'Hall',     'email' => 'laura@coldlead.net',  'phone' => '+1-555-200-0004', 'company' => null,             'source' => 'phone',        'status' => 'new',          'priority' => 'low',    'score' => 30],
            ['first_name' => 'Mike',    'last_name' => 'Young',    'email' => 'mike@followup.com',   'phone' => '+1-555-200-0005', 'company' => 'Followup Inc',   'source' => 'email',        'status' => 'proposal',     'priority' => 'medium', 'score' => 70],
            ['first_name' => 'Nancy',   'last_name' => 'King',     'email' => 'nancy@enterprise2.com','phone'=> '+1-555-200-0006', 'company' => 'Enterprise Two', 'source' => 'website',      'status' => 'won',          'priority' => 'high',   'score' => 95],
        ];

        foreach ($leads as $data) {
            Lead::firstOrCreate(
                ['email' => $data['email'], 'tenant_id' => $tenant->id],
                array_merge($data, [
                    'tenant_id'   => $tenant->id,
                    'assigned_to' => $agents[array_rand($agents)],
                    'notes'       => 'Seeded test lead.',
                ])
            );
        }

        $this->command->info('Contacts (' . count($contacts) . ') and Leads (' . count($leads) . ') seeded.');
    }
}
