<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Meter;
use App\Models\Part;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $workOrderStats = WorkOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $priorityStats = WorkOrder::whereIn('status', ['open', 'in_progress'])
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $overdueCount = WorkOrder::whereNotIn('status', ['closed'])
            ->where('due_date', '<', now())
            ->count();

        $lowStockParts = Part::whereColumn('quantity_on_hand', '<=', 'minimum_quantity')
            ->with('category:id,name,color')
            ->get(['id', 'name', 'part_number', 'quantity_on_hand', 'minimum_quantity', 'unit', 'category_id']);

        $metersDue = Meter::whereRaw('(current_reading - last_maintenance_reading) >= frequency')
            ->with('asset:id,name')
            ->get(['id', 'name', 'unit', 'asset_id', 'current_reading', 'last_maintenance_reading', 'frequency'])
            ->each(fn ($m) => $m->append(['units_since_maintenance', 'maintenance_progress']));

        $recentWorkOrders = WorkOrder::with('asset:id,name', 'assignedTo:id,name')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'work_orders' => [
                'by_status'   => $workOrderStats,
                'by_priority' => $priorityStats,
                'overdue'     => $overdueCount,
                'total'       => WorkOrder::count(),
            ],
            'assets'       => ['total' => Asset::count()],
            'parts'        => ['low_stock' => $lowStockParts],
            'meters'       => ['due' => $metersDue],
            'recent_work_orders' => $recentWorkOrders,
        ]);
    }
}
