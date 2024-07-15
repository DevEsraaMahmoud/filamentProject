<?php

use App\Models\Admin;
use Spatie\Permission\Models\Permission;

class AdminWithPermissionsFactory {

    public array $permissions = [];

    public string $guard = 'admin';

    public function create($data = null): Admin
    {
        $admin = Admin::factory()->create($data);

        foreach($this->permissions as $permission) {
            $permission = Permission::Create([
                'name' => $permission,
                'guard_name' => $this->guard,
            ]);
            $admin->givePermissionTo($permission);
        }
        return $admin;
    }

    public function withPermissions($permissions = [], $guard = 'admin'): self
    {
        $this->permissions = $permissions;

        $this->guard = $guard;

        return $this;
    }
}