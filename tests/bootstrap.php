<?php

require_once __DIR__ . '/../../rapyd-admin/vendor/autoload.php';

spl_autoload_register(function ($class) {
    $mappings = [
        'App\\Modules\\Shop\\Tests\\' => __DIR__ . '/',
        'App\\Modules\\Shop\\'        => __DIR__ . '/../',
    ];

    foreach ($mappings as $prefix => $base) {
        if (strncmp($prefix, $class, strlen($prefix)) === 0) {
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = $base . $relative . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});
