<?php

use App\Models\Admin;
use Spatie\Permission\Models\Permission;

class PermissionFactory {

    public function create($permissions = [], $guard = 'admin')
    {
        foreach($permissions as $permission) {
            $permission = Permission::Create([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }
    }
}