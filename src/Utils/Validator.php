<?php

namespace App\Utils;

/**
 * Input validation and sanitisation helpers.
 * All methods are static for convenience.
 */
class Validator
{
    /** Dangerous PHP/shell functions that must not appear in submitted code */
    private const DANGEROUS_FUNCTIONS = [
        'eval', 'exec', 'shell_exec', 'system', 'passthru', 'popen', 'proc_open',
        'pcntl_exec', '`',            // backtick operator
        'file_get_contents', 'file_put_contents', 'file_delete', 'unlink',
        'fopen', 'fwrite', 'fputs',
        'include', 'include_once', 'require', 'require_once',
        'preg_replace',               // /e modifier was dangerous
        'base64_decode',
        'assert',
        'create_function',
        'call_user_func', 'call_user_func_array',
        'phpinfo',
        'posix_kill', 'posix_getpwuid',
        'curl_exec', 'curl_multi_exec',
        'move_uploaded_file',
        'parse_str',
        'extract',
        'putenv', 'getenv',
        'dl',
    ];

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    public static function validateEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Password rules: min 8 chars, at least one uppercase, one lowercase,
     * one digit, and one special character.
     */
    public static function validatePassword(string $password): bool
    {
        if (strlen($password) < 8) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        return true;
    }

    /**
     * Username: alphanumeric + underscore, 3–50 characters.
     */
    public static function validateUsername(string $username): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_]{3,50}$/', $username);
    }

    /**
     * Generic string sanitiser: trim + htmlspecialchars.
     */
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Code sanitiser: strips recognised dangerous PHP/shell function calls
     * while preserving legitimate C/Go/Java code.
     * Note: this is a best-effort defence-in-depth measure, not a sandbox.
     */
    public static function sanitizeCode(string $code): string
    {
        foreach (self::DANGEROUS_FUNCTIONS as $func) {
            // Match function-call pattern: func_name followed by optional whitespace and '('
            // or the backtick operator
            if ($func === '`') {
                $code = preg_replace('/`[^`]*`/', '', $code) ?? $code;
            } else {
                $pattern = '/\b' . preg_quote($func, '/') . '\s*\(/i';
                $code    = preg_replace($pattern, '__REMOVED__(', $code) ?? $code;
            }
        }
        return $code;
    }

    /**
     * Return a list of validation errors for the given field => value map.
     * Rules format: ['field' => ['required', 'email', 'min:8', 'max:255']]
     *
     * @param  array<string,mixed>  $data
     * @param  array<string,string[]> $rules
     * @return array<string,string>  field => first error message
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

                switch ($ruleName) {
                    case 'required':
                        if ($value === null || $value === '') {
                            $errors[$field] = "{$field} is required";
                        }
                        break;
                    case 'email':
                        if ($value !== null && !self::validateEmail((string) $value)) {
                            $errors[$field] = "Invalid email address";
                        }
                        break;
                    case 'min':
                        if ($value !== null && strlen((string) $value) < (int) $param) {
                            $errors[$field] = "{$field} must be at least {$param} characters";
                        }
                        break;
                    case 'max':
                        if ($value !== null && strlen((string) $value) > (int) $param) {
                            $errors[$field] = "{$field} must not exceed {$param} characters";
                        }
                        break;
                    case 'username':
                        if ($value !== null && !self::validateUsername((string) $value)) {
                            $errors[$field] = "Username must be 3-50 alphanumeric characters or underscores";
                        }
                        break;
                    case 'password':
                        if ($value !== null && !self::validatePassword((string) $value)) {
                            $errors[$field] = "Password must be at least 8 characters with uppercase, lowercase, number and special character";
                        }
                        break;
                }

                // Only report the first error per field
                if (isset($errors[$field])) {
                    break;
                }
            }
        }

        return $errors;
    }
}
