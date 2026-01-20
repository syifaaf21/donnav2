<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
        RoleSeeder::class,
        ProcessSeeder::class,
        DepartmentSeeder::class,
        AuditSeeder::class,
        UserSeeder::class,
        ProductSeeder::class,
        ModelSeeder::class,
        PartNumberSeeder::class,
        StatusSeeder::class,
        DocumentSeeder::class,
        // DocumentMappingSeeder::class,
        SubAuditSeeder::class,
        FindingCategoriesSeeder::class,
        KlausulsSeeder::class,
        HeadKlausulsSeeder::class,
        SubKlausulsSeeder::class,
        AddLeadAuditorRoleSeeder::class,
        AddDraftStatusSeeder::class,
        AddDraftFindingStatusSeeder::class,
        ]);

    }
}
