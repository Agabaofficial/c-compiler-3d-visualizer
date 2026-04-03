<?php

namespace App\Middleware;

use App\Utils\Logger;
use Throwable;

/**
 * ErrorHandlingMiddleware – global error / exception handlers + JSON error helpers.
 */
class ErrorHandlingMiddleware
{
    private Logger $logger;
    private bool   $debug;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->debug  = defined('DEBUG_MODE') ? DEBUG_MODE : (getenv('DEBUG_MODE') === 'true');
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Wrap a callable in a try/catch and return a JSON error on failure.
     */
    public function handle(callable $callable): mixed
    {
        try {
            return $callable();
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Register global error and exception handlers.
     * Call once at application bootstrap.
     */
    public function setHandlers(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
            }
        });
    }

    /**
     * Handle an uncaught exception: log it and emit a JSON error response.
     */
    public function handleException(Throwable $e): never
    {
        $code = $e->getCode();
        // Normalise HTTP status codes
        if ($code < 100 || $code > 599) {
            $code = 500;
        }

        $this->logger->error($e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $this->debug ? $e->getTraceAsString() : null,
        ]);

        $details = null;
        if ($this->debug) {
            $details = [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        }

        $this->jsonError($code, $e->getMessage(), $details);
    }

    /**
     * PHP error handler (registered via set_error_handler).
     *
     * @throws \ErrorException to convert PHP errors into exceptions
     */
    public function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): bool
    {
        if (!(error_reporting() & $errno)) {
            return false; // Error is suppressed by @ operator
        }

        $this->logger->error($errstr, [
            'errno'   => $errno,
            'file'    => $errfile,
            'line'    => $errline,
        ]);

        throw new \ErrorException($errstr, 500, $errno, $errfile, $errline);
    }

    /**
     * Output a JSON error response and exit.
     *
     * @param int    $code    HTTP status code
     * @param string $message Human-readable error message
     * @param mixed  $details Extra data included only in debug mode
     */
    public function jsonError(int $code, string $message, mixed $details = null): never
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json');
        }

        $body = [
            'success' => false,
            'error'   => $message,
            'code'    => $code,
        ];

        if ($details !== null && $this->debug) {
            $body['details'] = $details;
        }

        echo json_encode($body);
        exit;
    }
}
