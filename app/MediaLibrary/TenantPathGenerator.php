<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class TenantPathGenerator implements PathGenerator
{
    protected function userPrefix(Media $media): string
    {
        $model = $media->model;
        // Prefer explicit user_id on the owning model; fallback to related user if available
        $userId = (int) ($model->user_id ?? $model->user?->getKey() ?? 0);

        return $userId > 0 ? "users/{$userId}" : 'users/unknown';
    }

    public function getPath(Media $media): string
    {
        return $this->userPrefix($media).'/'.$media->collection_name.'/'.$media->getKey().'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->userPrefix($media).'/'.$media->collection_name.'/'.$media->getKey().'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->userPrefix($media).'/'.$media->collection_name.'/'.$media->getKey().'/responsive-images/';
    }
}
