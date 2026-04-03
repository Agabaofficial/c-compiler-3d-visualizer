<?php
/**
 * middleware/errorHandler.php
 * Global error handling middleware shim.
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
