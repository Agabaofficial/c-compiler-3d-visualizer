<?php
/**
 * middleware/security.php
 * Global security middleware shim.
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/src/Config/Constants.php';
\App\Config\Constants::load();

require_once APP_ROOT . '/src/Utils/Logger.php';
require_once APP_ROOT . '/src/Utils/Validator.php';
require_once APP_ROOT . '/src/Middleware/SecurityMiddleware.php';

// Apply security headers immediately
(new \App\Middleware\SecurityMiddleware())->handle();
