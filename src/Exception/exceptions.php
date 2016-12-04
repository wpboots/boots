<?php

if (!class_exists('Boots\Exception\UnkownTypeException')) {
    require_once __DIR__ . '/UnkownTypeException.php';
}
if (!class_exists('Boots\Exception\InvalidConfigException')) {
    require_once __DIR__ . '/InvalidConfigException.php';
}
if (!class_exists('Boots\Exception\InvalidExtensionException')) {
    require_once __DIR__ . '/InvalidExtensionException.php';
}
if (!class_exists('Boots\Exception\FileNotFoundException')) {
    require_once __DIR__ . '/FileNotFoundException.php';
}
if (!class_exists('Boots\Exception\ClassNotFoundException')) {
    require_once __DIR__ . '/ClassNotFoundException.php';
}
if (!class_exists('Boots\Exception\NotFoundException')) {
    require_once __DIR__ . '/NotFoundException.php';
}
