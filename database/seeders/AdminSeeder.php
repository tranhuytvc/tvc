<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Seed all permissions
        foreach (Permission::$all as $group => $items) {
            foreach ($items as $slug => $label) {
                Permission::firstOrCreate(['slug' => $slug], [
                    'label' => $label,
                    'group' => $group,
                ]);
            }
        }

        // Create super admin (idempotent)
        User::firstOrCreate(['email' => 'admin@admin.com'], [
            'name'           => 'Super Admin',
            'password'       => Hash::make('admin123'),
            'is_super_admin' => true,
            'is_active'      => true,
        ]);

        $this->command->info('Super admin: admin@admin.com / admin123');
    }
}
