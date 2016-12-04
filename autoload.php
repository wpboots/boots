<?php

// Require exceptions
require_once __DIR__ . '/src/Exception/exceptions.php';

// Require interfaces
require_once __DIR__ . '/src/Contract/contracts.php';

if (!class_exists('Boots\Dispenser')) {
    require_once __DIR__ . '/src/Dispenser.php';
}
if (!class_exists('Boots\Boots')) {
    require_once __DIR__ . '/src/Boots.php';
}
