<?php
/**
 * middleware/logger.php – Logging middleware shim.
 *
 * Include this file to make the Logger utility and the
 * ErrorHandlingMiddleware available from any entry point.
 * It also registers the global error/exception handlers immediately.
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/src/Config/Constants.php';
\App\Config\Constants::load();

require_once APP_ROOT . '/src/Utils/Logger.php';
require_once APP_ROOT . '/src/Middleware/ErrorHandlingMiddleware.php';

// Register global handlers immediately
(new \App\Middleware\ErrorHandlingMiddleware())->setHandlers();
