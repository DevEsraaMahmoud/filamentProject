<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class AdminPermissions extends Seeder
{
     /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'admin',
            'password' => Hash::make('123456789'),
        ]);

        collect(config('roles-permissions'))->each(function ($actions, $entity) use ($admin) {
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate(['name' => "{$entity}-{$action}", 'guard_name' => 'admin']);
                $admin->givePermissionTo($permission->name);
            }
        });
    }
}
