<?php
/**
 * api/index.php – Main API router.
 *
 * Parses the incoming request URI and dispatches to the appropriate
 * controller method.  All class files are required manually so no
 * autoloader dependency is needed.
 */

// -----------------------------------------------------------------------
// Bootstrap
// -----------------------------------------------------------------------
define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/src/Config/Constants.php';
\App\Config\Constants::load();

require_once APP_ROOT . '/src/Config/Database.php';
require_once APP_ROOT . '/src/Utils/Logger.php';
require_once APP_ROOT . '/src/Utils/JWTHandler.php';
require_once APP_ROOT . '/src/Utils/Validator.php';
require_once APP_ROOT . '/src/Models/User.php';
require_once APP_ROOT . '/src/Models/CompilationSession.php';
require_once APP_ROOT . '/src/Models/Visualization.php';
require_once APP_ROOT . '/src/Models/Analytics.php';
require_once APP_ROOT . '/src/Services/AuthService.php';
require_once APP_ROOT . '/src/Services/CompilerService.php';
require_once APP_ROOT . '/src/Services/CacheService.php';
require_once APP_ROOT . '/src/Services/VisualizationService.php';
require_once APP_ROOT . '/src/Middleware/AuthMiddleware.php';
require_once APP_ROOT . '/src/Middleware/SecurityMiddleware.php';
require_once APP_ROOT . '/src/Middleware/ErrorHandlingMiddleware.php';
require_once APP_ROOT . '/src/Middleware/RateLimitMiddleware.php';
require_once APP_ROOT . '/src/Controllers/AuthController.php';
require_once APP_ROOT . '/src/Controllers/CompilationController.php';
require_once APP_ROOT . '/src/Controllers/UserController.php';
require_once APP_ROOT . '/src/Controllers/AdminController.php';
require_once APP_ROOT . '/src/Controllers/VisualizationController.php';

// -----------------------------------------------------------------------
// Global middleware
// -----------------------------------------------------------------------
$errorHandler = new \App\Middleware\ErrorHandlingMiddleware();
$errorHandler->setHandlers();

$security = new \App\Middleware\SecurityMiddleware();
$security->handle(); // Sets CORS headers and handles OPTIONS preflight

$rateLimiter = new \App\Middleware\RateLimitMiddleware();
$rateLimiter->handle(); // Enforces rate limit by IP

// -----------------------------------------------------------------------
// Routing
// -----------------------------------------------------------------------
$method     = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');

// Strip a /api/v1 prefix if present so the router works whether the file
// is accessed directly or via an index.php rewrite.
$prefix = '/api/v1';
if (strpos($requestUri, $prefix) === 0) {
    $path = substr($requestUri, strlen($prefix));
} else {
    // Strip anything before /auth, /compilation, /visualizations, /admin
    $path = preg_replace('#^.*(/(auth|compilation|visualizations|admin).*)$#', '$1', $requestUri) ?: $requestUri;
}

$path = $path === '' ? '/' : $path;

// Extract a trailing numeric segment as a route parameter ({id})
$routeId   = null;
$pathBase  = $path;
if (preg_match('#^(.*)/(\d+)$#', $path, $m)) {
    $pathBase = $m[1];
    $routeId  = (int) $m[2];
}

// -----------------------------------------------------------------------
// Route table
// -----------------------------------------------------------------------
$routes = [
    'POST /auth/register'              => ['AuthController',         'register'],
    'POST /auth/login'                 => ['AuthController',         'login'],
    'POST /auth/refresh'               => ['AuthController',         'refresh'],
    'POST /auth/logout'                => ['AuthController',         'logout'],
    'POST /compilation/compile'        => ['CompilationController',  'compile'],
    'GET /compilation/history'         => ['CompilationController',  'history'],
    'POST /visualizations/save'        => ['VisualizationController','save'],
    'GET /visualizations/load'         => ['VisualizationController','load'],
    'GET /visualizations/list'         => ['VisualizationController','list'],
    'DELETE /visualizations'           => ['VisualizationController','delete'],
    'GET /admin/stats'                 => ['AdminController',        'stats'],
    'GET /admin/users'                 => ['AdminController',        'users'],
    'GET /admin/logs'                  => ['AdminController',        'logs'],
    'DELETE /admin/users'              => ['AdminController',        'deleteUser'],
    'PUT /admin/users'                 => ['AdminController',        'updateUser'],
];

$routeKey = "{$method} {$pathBase}";

header('Content-Type: application/json');

if (isset($routes[$routeKey])) {
    [$controllerName, $actionName] = $routes[$routeKey];
    $fqcn       = "\\App\\Controllers\\{$controllerName}";
    $controller = new $fqcn();

    if ($routeId !== null) {
        $controller->$actionName($routeId);
    } else {
        $controller->$actionName();
    }
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error'   => "Route not found: {$method} {$path}",
        'code'    => 404,
    ]);
}
