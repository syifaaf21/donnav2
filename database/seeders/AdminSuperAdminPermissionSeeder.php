<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSuperAdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // All permissions
        $allPermissions = ['document_control', 'document_review', 'ftpp'];

        // Update all users with admin or super admin role
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super admin']);
        })->get();

        foreach ($users as $user) {
            $user->permissions = $allPermissions;
            $user->save();
        }
    }
}
