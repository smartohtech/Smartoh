<?php

// 1. Force productionenvironments
$_ENV['APP_ENV'] = 'production';

// 2. redirect Laravel caches to vercel's only writable directory (/tmp)
$_ENV['APP_STORAGE'] = '/tmp/storage';
$_ENV['CONFIG_CACHE'] = '/tmp/storage/framework/config.php';
$_ENV['ROUTES_CACHE'] = '/tmp/storage/framework/routes.php';
$_ENV['SERVICES_CACHE'] = '/tmp/storage/framework/services.php';
$_ENV['PACKAGES_CACHE'] = '/tmp/storage/framework/packages.php';


//only create the temporary directories if they do not already exixt
$dirs = [
    'tmp/storage/framework/views',
    'tmp/storage/framework/sessions',
    'tmp/storage/framework/cache'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require __DIR__ . '/../public/index.php';