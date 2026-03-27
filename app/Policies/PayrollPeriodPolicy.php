<?php

namespace App\Policies;

use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollPeriodPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->role_name, ['Admin', 'Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PayrollPeriod $payrollPeriod): bool
    {
           return in_array($user->role->role_name, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
          return in_array($user->role->role_name, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PayrollPeriod $payrollPeriod): bool
    {
          return in_array($user->role->role_name, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PayrollPeriod $payrollPeriod): bool
    {
          return in_array($user->role->role_name, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PayrollPeriod $payrollPeriod): bool
    {
           return in_array($user->role->role_name, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PayrollPeriod $payrollPeriod): bool
    {
          return in_array($user->role->role_name, ['Admin', 'Super Admin']);
    }
}
