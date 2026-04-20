<?php

namespace App\Observers;

use App\Models\WorkOrder;

class WorkOrderObserver
{
    /**
     * Automatically set started_at when WO moves to in_progress,
     * and completed_at when closed — even if updated outside the service.
     */
    public function updating(WorkOrder $workOrder): void
    {
        if ($workOrder->isDirty('status')) {
            if ($workOrder->status === 'in_progress' && !$workOrder->started_at) {
                $workOrder->started_at = now();
            }
            if ($workOrder->status === 'closed' && !$workOrder->completed_at) {
                $workOrder->completed_at = now();
            }
        }
    }
}
