<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PrivateMediaController
{
    public function show(Request $request, Media $media, ?string $conversion = null): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Must be authenticated
        abort_unless(Auth::check(), 403);

        $model = $media->model ?? $media->model()->getResults();
        if ($model === null && is_string($media->model_type) && class_exists($media->model_type)) {
            $model = $media->model_type::query()->find($media->model_id);
        }

        // If owning model cannot be resolved, do not leak information
        if ($model === null) {
            abort(403);
        }

        // Authorize by user ownership: model must have user_id matching current user
        $ownerId = (int) ($model->user_id ?? $model->user?->getKey() ?? 0);
        abort_unless($ownerId > 0 && $ownerId === (int) $request->user()->getKey(), 403);

        // Build absolute file path for the original or a conversion
        $path = $conversion ? $media->getPath($conversion) : $media->getPath();

        if (! is_file($path)) {
            abort(404);
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';

        // Stream inline with correct content type; do not force download
        return ResponseFactory::file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=0, no-cache',
        ]);
    }
}
