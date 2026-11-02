<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gis_map' => [
        'default_lat'            => (float) env('SMARTOH_MAP_DEFAULT_LAT', 0.0236),
        'default_lng'            => (float) env('SMARTOH_MAP_DEFAULT_LNG', 37.9062),
        'default_zoom'           => (int) env('SMARTOH_MAP_DEFAULT_ZOOM', 6),
        'tile_url_dark'          => env('SMARTOH_MAP_TILE_URL_DARK', 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'),
        'tile_url_street'        => env('SMARTOH_MAP_TILE_URL_STREET', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'tile_url_topo'          => env('SMARTOH_MAP_TILE_URL_TOPO', 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png'),
        'tile_attribution'       => env('SMARTOH_MAP_TILE_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions" target="_blank" rel="noopener">CARTO</a>'),
        'geocoder_enabled'       => (bool) env('SMARTOH_GEOCODER_ENABLED', true),
        'geocoder_url'           => env('SMARTOH_GEOCODER_URL', 'https://nominatim.openstreetmap.org/search'),
        'geocoder_api_key'       => env('SMARTOH_GEOCODER_API_KEY'),
        'geocoder_country_codes' => env('SMARTOH_GEOCODER_COUNTRY_CODES', 'ke'),
        'geocoder_user_agent'    => env('SMARTOH_GEOCODER_USER_AGENT', 'SmartohTechnologiesISPBilling/2.4 (noc@smartoh.net)'),
        'geocoder_cache_hours'   => (int) env('SMARTOH_GEOCODER_CACHE_TTL_HOURS', 72),
        'geocoder_rate_limit'    => (int) env('SMARTOH_GEOCODER_RATE_LIMIT_PER_MIN', 30),
    ],

];
