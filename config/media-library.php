<?php

use App\MediaLibrary\PrivateUrlGenerator;
use App\MediaLibrary\TenantPathGenerator;

return [
    // Store original & conversions on the local (private) disk by default unless overridden per collection
    'disk_name' => env('MEDIA_DISK', 'local'),

    // Generate paths prefixed by user_id to isolate tenant data
    'path_generator' => TenantPathGenerator::class,

    // Always generate URLs that go through our authorized route
    'url_generator' => PrivateUrlGenerator::class,

    // Keep versioning on to bust caches when files update
    'version_urls' => true,

    // Other defaults left as package defaults
];
