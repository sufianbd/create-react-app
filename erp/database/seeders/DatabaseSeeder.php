<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $tenant = Tenant::create([
            'name'      => 'Demo Company',
            'slug'      => 'demo',
            'domain'    => null,
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'name'      => 'Admin User',
            'email'     => 'admin@example.com',
            'tenant_id' => $tenant->id,
        ]);

        $admin->assignRole('super-admin');

        $this->call(InventorySeeder::class);
        $this->call(FinanceSeeder::class);
    }
}
