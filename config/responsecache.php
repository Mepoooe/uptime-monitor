<?php

return [
    /*
     * Determine if the response cache middleware should be enabled.
     */
    'enabled' => env('RESPONSE_CACHE_ENABLED', false),

    /*
     * When using the default CacheRequestFilter this setting controls the
     * default number of seconds responses must be cached.
     */
    'cache_lifetime_in_seconds' => (int) env('RESPONSE_CACHE_LIFETIME', 60 * 60 * 24 * 7 * 4), // Defaults to 1 month.

    /*
     * Here you may define the cache store that should be used to store
     * requests. This can be the name of any store that is
     * configured in app/config/cache.php
     */
    'cache_store' => env('RESPONSE_CACHE_DRIVER', 'redis'),

    /*
     * If the cache driver you configured supports tags, you may specify a tag name
     * here. All responses will be tagged. When clearing the responsecache only
     * items with that tag will be flushed.
     *
     * You may use a string or an array here.
     */
    'cache_tag' => env('RESPONSE_CACHE_TAG', 'responsecache'),
];
