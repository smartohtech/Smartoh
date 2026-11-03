<?php

// 1. Force productionenvironments
$_ENV['APP_ENV'] = 'production';

// 2. redirect Laravel caches to vercel's only writable directory (/tmp)
$_ENV['APP_STORAGE'] = '/tmp/storage';
$_ENV['CONFIG_CACHE'] = '/tmp/storage/framework/config.php';
$_ENV['ROUTES_CACHE'] = '/tmp/storage/framework/routes.php';
$_ENV['SERVICES_CACHE'] = '/tmp/storage/framework/services.php';
$_ENV['PACKAGES_CACHE'] = '/tmp/storage/framework/packages.php';

// 3. Create the temporary storage directories if they don't exist
if (!is_dir('/tmp/storage')) {
    exec('rm -rf /tmp/storage');
}

mkdir('/tmp/storage/framework/views', 0755, true);
mkdir('/tmp/storage/framework/sessions', 0755, true);
mkdir('/tmp/storage/framework/cache', 0755, true);

require __DIR__ . '/../public/index.php';