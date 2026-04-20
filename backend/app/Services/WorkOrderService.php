<?php

namespace App\Services;

use App\Models\Part;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\WorkOrderUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkOrderService
{
    public function create(array $data, int $createdBy): WorkOrder
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $data['wo_number']  = $this->generateWoNumber();
            $data['created_by'] = $createdBy;

            $wo = WorkOrder::create($data);

            WorkOrderUpdate::create([
                'work_order_id' => $wo->id,
                'user_id'       => $createdBy,
                'comment'       => 'Work order created.',
                'type'          => 'comment',
            ]);

            return $wo;
        });
    }

    public function updateStatus(WorkOrder $workOrder, string $newStatus, string $comment, int $userId): WorkOrder
    {
        if (!$workOrder->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from '{$workOrder->status}' to '{$newStatus}'.",
            ]);
        }

        return DB::transaction(function () use ($workOrder, $newStatus, $comment, $userId) {
            $oldStatus = $workOrder->status;

            $workOrder->update([
                'status'       => $newStatus,
                'started_at'   => $newStatus === 'in_progress' && !$workOrder->started_at ? now() : $workOrder->started_at,
                'completed_at' => $newStatus === 'closed' ? now() : $workOrder->completed_at,
            ]);

            WorkOrderUpdate::create([
                'work_order_id' => $workOrder->id,
                'user_id'       => $userId,
                'comment'       => $comment ?: "Status changed to {$newStatus}.",
                'status_from'   => $oldStatus,
                'status_to'     => $newStatus,
                'type'          => 'status_change',
            ]);

            return $workOrder->fresh();
        });
    }

    public function addPart(WorkOrder $workOrder, array $data, int $userId): WorkOrderPart
    {
        return DB::transaction(function () use ($workOrder, $data, $userId) {
            $part = Part::findOrFail($data['part_id']);

            if ($part->quantity_on_hand < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock. Available: {$part->quantity_on_hand} {$part->unit}.",
                ]);
            }

            $woPart = WorkOrderPart::create([
                'work_order_id' => $workOrder->id,
                'part_id'       => $part->id,
                'quantity'      => $data['quantity'],
                'unit_cost'     => $part->unit_cost,
                'added_by'      => $userId,
            ]);

            // Deduct from inventory
            $part->decrement('quantity_on_hand', $data['quantity']);

            return $woPart->load('part');
        });
    }

    public function removePart(WorkOrderPart $woPart): void
    {
        DB::transaction(function () use ($woPart) {
            // Return quantity to inventory
            $woPart->part->increment('quantity_on_hand', $woPart->quantity);
            $woPart->delete();
        });
    }

    private function generateWoNumber(): string
    {
        $year  = now()->format('Y');
        $count = WorkOrder::whereYear('created_at', $year)->count() + 1;
        return sprintf('WO-%s-%05d', $year, $count);
    }
}
