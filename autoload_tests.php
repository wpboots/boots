<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

if (getenv('CI_TEST')) {
    require rtrim(getenv('CI_TEST'), '/') . '/tests/phpunit/includes/bootstrap.php';
} else {
    define('WP_USE_THEMES', false);
    $dotenv->required('ABSPATH');
    require rtrim(getenv('ABSPATH'), '/') . '/wp-load.php';
}

require __DIR__ . '/autoload.php';

if (!class_exists('Boots\Locator')) {
    require_once __DIR__ . '/src/Locator.php';
}
if (!class_exists('Boots\Dispenser')) {
    require_once __DIR__ . '/src/Dispenser.php';
}
if (!class_exists('Boots\Repository')) {
    require_once __DIR__ . '/src/Repository.php';
}