<?php

namespace App\Http\Controllers;

use App\Http\Requests\Part\StorePartRequest;
use App\Http\Requests\Part\UpdatePartRequest;
use App\Models\Part;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $parts = Part::query()
            ->with('category:id,name,color')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('part_number', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->low_stock, fn ($q) => $q->whereColumn('quantity_on_hand', '<=', 'minimum_quantity'))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($parts);
    }

    public function store(StorePartRequest $request): JsonResponse
    {
        $part = Part::create($request->validated());
        return response()->json($part->load('category:id,name,color'), 201);
    }

    public function show(Part $part): JsonResponse
    {
        return response()->json($part->load('category'));
    }

    public function update(UpdatePartRequest $request, Part $part): JsonResponse
    {
        $part->update($request->validated());
        return response()->json($part->load('category:id,name,color'));
    }

    public function destroy(Part $part): JsonResponse
    {
        $part->delete();
        return response()->json(null, 204);
    }
}
