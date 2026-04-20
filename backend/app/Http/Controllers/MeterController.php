<?php

namespace App\Http\Controllers;

use App\Http\Requests\Meter\StoreMeterRequest;
use App\Http\Requests\MeterReading\StoreMeterReadingRequest;
use App\Models\Meter;
use App\Services\MeterService;
use App\Services\PreventiveMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeterController extends Controller
{
    public function __construct(
        private MeterService $service,
        private PreventiveMaintenanceService $pmService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $meters = Meter::query()
            ->with('asset:id,name')
            ->when($request->asset_id, fn ($q, $id) => $q->where('asset_id', $id))
            ->when($request->due, fn ($q) => $q->whereRaw('(current_reading - last_maintenance_reading) >= frequency'))
            ->orderBy('asset_id')
            ->paginate($request->per_page ?? 15);

        $meters->getCollection()->each(fn ($m) => $m->append(['units_since_maintenance', 'maintenance_progress']));

        return response()->json($meters);
    }

    public function store(StoreMeterRequest $request): JsonResponse
    {
        $meter = Meter::create($request->validated());
        return response()->json($meter->load('asset:id,name'), 201);
    }

    public function show(Meter $meter): JsonResponse
    {
        $meter->append(['units_since_maintenance', 'maintenance_progress']);
        return response()->json($meter->load(['asset:id,name', 'readings.recordedBy:id,name']));
    }

    public function destroy(Meter $meter): JsonResponse
    {
        $meter->delete();
        return response()->json(null, 204);
    }

    public function addReading(StoreMeterReadingRequest $request, Meter $meter): JsonResponse
    {
        $reading = $this->service->recordReading(
            $meter,
            $request->reading_value,
            $request->notes,
            $request->user()->id
        );

        // Auto-create preventive WO if threshold crossed
        $woCreated = $this->pmService->checkAndTrigger($meter->fresh(), $request->user()->id);

        return response()->json([
            'reading'              => $reading,
            'preventive_wo_created' => $woCreated ? [
                'id'        => $woCreated->id,
                'wo_number' => $woCreated->wo_number,
                'title'     => $woCreated->title,
            ] : null,
        ], 201);
    }

    public function resetBaseline(Request $request, Meter $meter): JsonResponse
    {
        $meter = $this->service->resetMaintenanceBaseline($meter);
        $meter->append(['units_since_maintenance', 'maintenance_progress']);
        return response()->json($meter);
    }
}
