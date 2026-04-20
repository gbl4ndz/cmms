<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function store(Model $model, UploadedFile $file, int $userId, string $collection = 'other'): Media
    {
        $directory = strtolower(class_basename($model)) . 's/' . $model->id . '/' . $collection;
        $path      = $file->store($directory, 'public');

        return $model->media()->create([
            'disk'        => 'public',
            'path'        => $path,
            'filename'    => $file->getClientOriginalName(),
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
            'collection'  => $collection,
            'uploaded_by' => $userId,
        ]);
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }
}
