<?php
/**
 * middleware/auth.php
 * Global auth middleware shim.
 * Include this file to make AuthMiddleware available.
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/src/Config/Constants.php';
\App\Config\Constants::load();

require_once APP_ROOT . '/src/Config/Database.php';
require_once APP_ROOT . '/src/Utils/Logger.php';
require_once APP_ROOT . '/src/Utils/JWTHandler.php';
require_once APP_ROOT . '/src/Utils/Validator.php';
require_once APP_ROOT . '/src/Models/User.php';
require_once APP_ROOT . '/src/Services/AuthService.php';
require_once APP_ROOT . '/src/Middleware/AuthMiddleware.php';
