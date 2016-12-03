<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

if (getenv('CI_TEST')) {
    require rtrim(getenv('CI_TEST'), '/') . '/tests/phpunit/includes/bootstrap.php';
} else {
    define('WP_USE_THEMES', false);
    $dotenv->required('ABSPATH');
    require rtrim(getenv('ABSPATH'), '/') . '/wp-load.php';
}