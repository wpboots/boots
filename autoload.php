<?php

if (!class_exists('Boots\Exception\UnkownTypeException')) {
    require_once __DIR__ . '/src/Exception/UnkownTypeException.php';
}
if (!class_exists('Boots\Exception\InvalidConfigException')) {
    require_once __DIR__ . '/src/Exception/InvalidConfigException.php';
}
if (!class_exists('Boots\Exception\InvalidExtensionException.php')) {
    require_once __DIR__ . '/src/Exception/InvalidExtensionException.php';
}
if (!interface_exists('Boots\RepositoryInterface')) {
    require_once __DIR__ . '/src/RepositoryInterface.php';
}
if (!class_exists('Boots\Boots')) {
    require_once __DIR__ . '/src/Boots.php';
}