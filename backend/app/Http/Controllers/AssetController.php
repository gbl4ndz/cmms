<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assets = Asset::query()
            ->with(['location:id,name', 'area:id,name', 'contractor:id,name', 'category:id,name,color'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->location_id, fn ($q, $id) => $q->where('location_id', $id))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($assets);
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $asset = Asset::create($request->validated());
        return response()->json(
            $asset->load('location:id,name', 'area:id,name', 'contractor:id,name', 'category:id,name,color'),
            201
        );
    }

    public function show(Asset $asset): JsonResponse
    {
        return response()->json($asset->load(
            'location', 'area', 'contractor', 'category',
            'meters', 'media', 'workOrders'
        ));
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $asset->update($request->validated());
        return response()->json(
            $asset->load('location:id,name', 'area:id,name', 'contractor:id,name', 'category:id,name,color')
        );
    }

    public function destroy(Asset $asset): JsonResponse
    {
        $asset->delete();
        return response()->json(null, 204);
    }
}
