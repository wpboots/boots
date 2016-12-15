<?php

// project-specific namespace prefix
$prefix = 'Boots\\';

// base directory for the namespace prefix
$base_dir = __DIR__ . '/src/';

// project-specific version
$manifest = ['version' => '', 'extensions' => []];
$manifestFile = __DIR__ . '/boots.php';
if (is_file($manifestFile)) {
    $manifest = manifestFile;
}
$suffix = str_replace('.', '_', $manifest['version']);
$suffix = empty($suffix) ? '' : "_{$suffix}";

// Register autoloader
// @see http://www.php-fig.org/psr/psr-4/examples/
spl_autoload_register(function ($class) use ($base_dir, $prefix, $suffix) {
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    // get the relative class name
    $relative_class = substr($class, $len);
    // strip off the version suffix
    $search = '/'.preg_quote($suffix).'$/';
    $relative_class = preg_replace($search, '', $relative_class);
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

return "Boots\\Boots{$suffix}";
