<?php

namespace App\MediaLibrary;

use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\UrlGenerator\BaseUrlGenerator;

class PrivateUrlGenerator extends BaseUrlGenerator
{
    /**
     * Generate a URL to the media file using a secure, authorized route.
     */
    public function getUrl(): string
    {
        $parameters = ['media' => $this->media->getKey()];

        if (! empty($this->conversionName)) {
            $parameters['conversion'] = $this->conversionName;
        }

        return route('media.show', $parameters);
    }

    public function getPath(): string
    {
        // Absolute filesystem path where the media is stored
        return $this->getDisk()->path('/').$this->getPathRelativeToRoot();
    }

    public function getTemporaryUrl($expiration, array $options = []): string
    {
        // We rely on authenticated route; for temporary links you could sign the route.
        return $this->getUrl();
    }

    public function getResponsiveImagesDirectoryUrl(): string
    {
        // Route will stream the right conversion/responsive image
        return $this->getUrl();
    }
}
