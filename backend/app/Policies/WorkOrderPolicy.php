<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        // Admins can update any; regular users only their own assigned WOs
        return $user->isAdmin()
            || $workOrder->created_by  === $user->id
            || $workOrder->assigned_to === $user->id;
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        // Only admins or creators can delete; closed WOs are immutable
        return $user->isAdmin()
            && $workOrder->status !== 'closed';
    }

    public function updateStatus(User $user, WorkOrder $workOrder): bool
    {
        return $user->isAdmin()
            || $workOrder->assigned_to === $user->id;
    }
}
