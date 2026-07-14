<?php

namespace Database\Seeders;

use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name'   => 'Demo Corp',
                'domain' => 'demo.test',
                'plan'   => 'pro',
                'status' => 'active',
            ]
        );

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Admin User',
                'password'  => Hash::make('password'),
                'phone'     => env('TEST_PHONE_NUMBER', '+91-XXXXXXXXXX'),
                'status'    => 'active',
            ]
        );
        $admin->syncRoles(['admin']);

        // Sales Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.test'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Sarah Manager',
                'password'  => Hash::make('password'),
                'phone'     => '+1-555-000-0002',
                'status'    => 'active',
            ]
        );
        $manager->syncRoles(['sales_manager']);

        // Sales Agent 1
        $agent1 = User::firstOrCreate(
            ['email' => 'agent1@demo.test'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'John Agent',
                'password'  => Hash::make('password'),
                'phone'     => '+1-555-000-0003',
                'status'    => 'active',
            ]
        );
        $agent1->syncRoles(['sales_agent']);

        // Sales Agent 2
        $agent2 = User::firstOrCreate(
            ['email' => 'agent2@demo.test'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Lisa Agent',
                'password'  => Hash::make('password'),
                'phone'     => '+1-555-000-0004',
                'status'    => 'active',
            ]
        );
        $agent2->syncRoles(['sales_agent']);

        // Viewer
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@demo.test'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'View Only',
                'password'  => Hash::make('password'),
                'phone'     => '+1-555-000-0005',
                'status'    => 'active',
            ]
        );
        $viewer->syncRoles(['viewer']);

        $this->command->info('Tenant & users seeded.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['admin',        'admin@demo.test',   'password'],
                ['sales_manager','manager@demo.test', 'password'],
                ['sales_agent',  'agent1@demo.test',  'password'],
                ['sales_agent',  'agent2@demo.test',  'password'],
                ['viewer',       'viewer@demo.test',  'password'],
            ]
        );
    }
}
