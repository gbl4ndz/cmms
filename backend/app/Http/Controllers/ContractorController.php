<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contractor\StoreContractorRequest;
use App\Http\Requests\Contractor\UpdateContractorRequest;
use App\Models\Contractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contractors = Contractor::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->withCount('assets')
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($contractors);
    }

    public function store(StoreContractorRequest $request): JsonResponse
    {
        $contractor = Contractor::create($request->validated());
        return response()->json($contractor, 201);
    }

    public function show(Contractor $contractor): JsonResponse
    {
        return response()->json($contractor->load('assets'));
    }

    public function update(UpdateContractorRequest $request, Contractor $contractor): JsonResponse
    {
        $contractor->update($request->validated());
        return response()->json($contractor);
    }

    public function destroy(Contractor $contractor): JsonResponse
    {
        $contractor->delete();
        return response()->json(null, 204);
    }
}
