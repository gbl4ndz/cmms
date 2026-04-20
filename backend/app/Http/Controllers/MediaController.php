<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Media;
use App\Models\WorkOrder;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private MediaService $service) {}

    public function uploadToAsset(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'file'       => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt'],
            'collection' => ['nullable', 'in:images,documents,manuals,other'],
        ]);

        $media = $this->service->store(
            $asset,
            $request->file('file'),
            $request->user()->id,
            $request->collection ?? 'other'
        );

        return response()->json($media, 201);
    }

    public function uploadToWorkOrder(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $request->validate([
            'file'       => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt'],
            'collection' => ['nullable', 'in:images,documents,other'],
        ]);

        $media = $this->service->store(
            $workOrder,
            $request->file('file'),
            $request->user()->id,
            $request->collection ?? 'other'
        );

        return response()->json($media, 201);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->service->delete($media);
        return response()->json(null, 204);
    }
}
