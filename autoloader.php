<?php

spl_autoload_register(function ($class) {
    // Define the base directories for the namespace prefix
    $baseDirs = [
        'model' => __DIR__ . '/model/',
        'service' => __DIR__ . '/service/',
    ];

    // Iterate through the base directories
    foreach ($baseDirs as $prefix => $baseDir) {
        // Check if the class uses the namespace prefix
        if (strpos($class, $prefix) === 0) {
            // Replace the namespace prefix with the base directory
            $relativeClass = str_replace($prefix, '', $class);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            // If the file exists, require it
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});