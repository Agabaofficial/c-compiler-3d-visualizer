<?php
/**
 * api/v1/admin/logs.php
 * GET /api/v1/admin/logs  (admin only)
 * Reads from logs/error.log and logs/access.log
 */
define('APP_ROOT', dirname(dirname(dirname(dirname(__FILE__)))));
require_once APP_ROOT . '/api/index.php';
