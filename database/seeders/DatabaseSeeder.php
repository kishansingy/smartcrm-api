<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,  // 1. roles & permissions first
            TenantAndUserSeeder::class,         // 2. tenant + users with roles
            ContactAndLeadSeeder::class,        // 3. contacts & leads
            PipelineAndDealSeeder::class,       // 4. pipeline, stages, deals
            TaskSeeder::class,                  // 5. tasks linked to contacts/leads
            CallLogSeeder::class,               // 6. call history with AI summaries
        ]);
    }
}
