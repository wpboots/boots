<?php

define('WP_USE_THEMES', false);

require __DIR__ . '/../vendor/autoload.php';

if (!getenv('ABSPATH')) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
    $dotenv->load();
    $dotenv->required('ABSPATH');
}

require rtrim(getenv('ABSPATH'), '/') . '/wp-load.php';