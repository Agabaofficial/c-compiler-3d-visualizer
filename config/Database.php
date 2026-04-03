<?php
/**
 * config/Database.php – Root-level database entry point.
 *
 * Provides the App\Config\Database singleton for code that expects
 * the class to be reachable from the project root (e.g. admin pages
 * or scripts that are not loaded through api/index.php).
 *
 * The actual implementation lives in src/Config/Database.php so that
 * both include paths resolve to the same singleton instance.
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/src/Config/Constants.php';
\App\Config\Constants::load();

require_once APP_ROOT . '/src/Config/Database.php';
