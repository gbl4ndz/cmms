<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkOrder\AddCommentRequest;
use App\Http\Requests\WorkOrder\AddPartRequest;
use App\Http\Requests\WorkOrder\StoreWorkOrderRequest;
use App\Http\Requests\WorkOrder\UpdateStatusRequest;
use App\Http\Requests\WorkOrder\UpdateWorkOrderRequest;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\WorkOrderUpdate;
use App\Services\WorkOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function __construct(private WorkOrderService $service) {}

    public function index(Request $request): JsonResponse
    {
        $workOrders = WorkOrder::query()
            ->with([
                'asset:id,name',
                'assignedTo:id,name',
                'createdBy:id,name',
            ])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->asset_id, fn ($q, $id) => $q->where('asset_id', $id))
            ->when($request->assigned_to, fn ($q, $id) => $q->where('assigned_to', $id))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%")
                ->orWhere('wo_number', 'like', "%{$s}%"))
            ->when($request->due_before, fn ($q, $d) => $q->where('due_date', '<=', $d))
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'on_hold', 'closed')")
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->paginate($request->per_page ?? 15);

        return response()->json($workOrders);
    }

    public function store(StoreWorkOrderRequest $request): JsonResponse
    {
        $workOrder = $this->service->create($request->validated(), $request->user()->id);
        return response()->json(
            $workOrder->load('asset:id,name', 'assignedTo:id,name', 'createdBy:id,name'),
            201
        );
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        return response()->json($workOrder->load([
            'asset.location:id,name',
            'asset.area:id,name',
            'assignedTo:id,name,email',
            'createdBy:id,name',
            'updates.user:id,name',
            'parts.part:id,name,unit',
            'parts.addedBy:id,name',
            'media',
        ]));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $workOrder->update($request->validated());
        return response()->json(
            $workOrder->load('asset:id,name', 'assignedTo:id,name', 'createdBy:id,name')
        );
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(UpdateStatusRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $workOrder = $this->service->updateStatus(
            $workOrder,
            $request->status,
            $request->comment ?? '',
            $request->user()->id
        );

        return response()->json($workOrder->load('updates.user:id,name'));
    }

    public function addComment(AddCommentRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $update = WorkOrderUpdate::create([
            'work_order_id' => $workOrder->id,
            'user_id'       => $request->user()->id,
            'comment'       => $request->comment,
            'type'          => 'comment',
        ]);

        return response()->json($update->load('user:id,name'), 201);
    }

    public function addPart(AddPartRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $woPart = $this->service->addPart($workOrder, $request->validated(), $request->user()->id);
        return response()->json($woPart, 201);
    }

    public function removePart(WorkOrder $workOrder, WorkOrderPart $part): JsonResponse
    {
        abort_if($part->work_order_id !== $workOrder->id, 404);
        $this->service->removePart($part);
        return response()->json(null, 204);
    }
}
