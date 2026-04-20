<?php

namespace App\Http\Controllers;

use App\Http\Requests\Area\StoreAreaRequest;
use App\Http\Requests\Area\UpdateAreaRequest;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $areas = Area::query()
            ->with('location:id,name')
            ->when($request->location_id, fn ($q, $id) => $q->where('location_id', $id))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->withCount('assets')
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($areas);
    }

    public function store(StoreAreaRequest $request): JsonResponse
    {
        $area = Area::create($request->validated());
        return response()->json($area->load('location:id,name'), 201);
    }

    public function show(Area $area): JsonResponse
    {
        return response()->json($area->load('location', 'assets'));
    }

    public function update(UpdateAreaRequest $request, Area $area): JsonResponse
    {
        $area->update($request->validated());
        return response()->json($area->load('location:id,name'));
    }

    public function destroy(Area $area): JsonResponse
    {
        $area->delete();
        return response()->json(null, 204);
    }
}
