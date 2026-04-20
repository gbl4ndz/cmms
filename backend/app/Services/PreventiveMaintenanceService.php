<?php

namespace App\Services;

use App\Models\Meter;
use App\Models\WorkOrder;
use App\Models\WorkOrderUpdate;
use Illuminate\Support\Facades\DB;

class PreventiveMaintenanceService
{
    public function __construct(private WorkOrderService $workOrderService) {}

    /**
     * Check if a meter has crossed its maintenance threshold and,
     * if no open preventive WO exists for this meter, create one.
     *
     * Returns the created WorkOrder or null if not triggered.
     */
    public function checkAndTrigger(Meter $meter, int $triggeredByUserId): ?WorkOrder
    {
        if (!$meter->isDueForMaintenance()) {
            return null;
        }

        // Avoid duplicate open WOs for same meter
        $alreadyOpen = WorkOrder::where('asset_id', $meter->asset_id)
            ->where('type', 'preventive')
            ->whereNotIn('status', ['closed'])
            ->whereJsonContains('description', "Meter: {$meter->name}")
            ->exists();

        // Fallback: broader check if JSON search not supported
        if (!$alreadyOpen) {
            $alreadyOpen = WorkOrder::where('asset_id', $meter->asset_id)
                ->where('type', 'preventive')
                ->whereNotIn('status', ['closed'])
                ->where('title', 'like', "%{$meter->name}%")
                ->exists();
        }

        if ($alreadyOpen) {
            return null;
        }

        return DB::transaction(function () use ($meter, $triggeredByUserId) {
            $wo = $this->workOrderService->create([
                'title'       => "Preventive Maintenance — {$meter->asset->name} ({$meter->name})",
                'description' => "Auto-generated: Meter \"{$meter->name}\" reached {$meter->current_reading} {$meter->unit}. "
                               . "Maintenance interval: every {$meter->frequency} {$meter->unit}. "
                               . "Meter: {$meter->name}",
                'type'        => 'preventive',
                'priority'    => 'medium',
                'asset_id'    => $meter->asset_id,
            ], $triggeredByUserId);

            return $wo;
        });
    }
}
