<?php
/**
 * api/v1/auth/register.php
 * POST /api/v1/auth/register
 */

// Walk up three levels: auth -> v1 -> api -> project root
define('APP_ROOT', dirname(dirname(dirname(dirname(__FILE__)))));
require_once APP_ROOT . '/api/index.php';
