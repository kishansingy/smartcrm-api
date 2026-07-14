<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Leads
            'leads.view', 'leads.create', 'leads.update', 'leads.delete', 'leads.import', 'leads.export',
            // Contacts
            'contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete',
            // Pipeline
            'pipeline.view', 'pipeline.create', 'pipeline.update', 'pipeline.delete',
            // Tasks
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
            // Reports
            'reports.view', 'reports.export',
            // Settings
            'settings.view', 'settings.update',
            // Users
            'users.view', 'users.create', 'users.update', 'users.delete',
            // WhatsApp
            'whatsapp.send', 'whatsapp.view',
            // Calls
            'calls.view', 'calls.make',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Super Admin — all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin — all except super
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Sales Manager
        $salesManager = Role::firstOrCreate(['name' => 'sales_manager']);
        $salesManager->givePermissionTo([
            'leads.view', 'leads.create', 'leads.update', 'leads.import', 'leads.export',
            'contacts.view', 'contacts.create', 'contacts.update',
            'pipeline.view', 'pipeline.create', 'pipeline.update',
            'tasks.view', 'tasks.create', 'tasks.update',
            'reports.view', 'reports.export',
            'whatsapp.send', 'whatsapp.view',
            'calls.view', 'calls.make',
        ]);

        // Sales Agent
        $salesAgent = Role::firstOrCreate(['name' => 'sales_agent']);
        $salesAgent->givePermissionTo([
            'leads.view', 'leads.create', 'leads.update',
            'contacts.view', 'contacts.create', 'contacts.update',
            'pipeline.view', 'pipeline.update',
            'tasks.view', 'tasks.create', 'tasks.update',
            'whatsapp.send', 'whatsapp.view',
            'calls.view', 'calls.make',
        ]);

        // Viewer
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'leads.view', 'contacts.view', 'pipeline.view',
            'tasks.view', 'reports.view', 'whatsapp.view', 'calls.view',
        ]);
    }
}
