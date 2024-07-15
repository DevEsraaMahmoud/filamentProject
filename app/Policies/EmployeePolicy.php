<?php

namespace App\Policies;

use App\Models\Admin;

class EmployeePolicy
{
    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('employee-view');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermissionTo('employee-view');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('employee-create');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermissionTo('employee-update');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermissionTo('employee-delete');
    }
}
