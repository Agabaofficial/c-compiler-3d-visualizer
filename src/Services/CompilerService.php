<?php

namespace App\Services;

use App\Models\CompilationSession;
use App\Utils\Validator;

/**
 * CompilerService – wraps language-specific compilation logic and persists
 * the results in the compilation_sessions table.
 */
class CompilerService
{
    private CompilationSession $sessionModel;

    /** Supported languages and their simulated compilation stages */
    private const STAGES = [
        'c'          => ['Lexing', 'Parsing', 'Semantic Analysis', 'IR Generation', 'Optimisation', 'Code Generation'],
        'java'       => ['Lexing', 'Parsing', 'Semantic Analysis', 'Bytecode Generation'],
        'go'         => ['Lexing', 'Parsing', 'Type Checking', 'SSA Generation', 'Machine Code'],
        'brainfuck'  => ['Lexing', 'Parsing', 'Interpretation'],
    ];

    private const SUPPORTED_LANGUAGES = ['c', 'java', 'go', 'brainfuck'];

    public function __construct()
    {
        $this->sessionModel = new CompilationSession();
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Compile the supplied code and persist the session.
     *
     * @param string   $language One of c|java|go|brainfuck
     * @param string   $code     Source code to compile
     * @param int|null $userId   Optional authenticated user
     * @return array  ['session_id', 'status', 'output', 'stages', 'execution_time']
     */
    public function compile(string $language, string $code, ?int $userId = null): array
    {
        $language = strtolower(trim($language));

        if (!in_array($language, self::SUPPORTED_LANGUAGES, true)) {
            throw new \InvalidArgumentException("Unsupported language: {$language}", 400);
        }

        // Sanitise the code before processing
        $code = Validator::sanitizeCode($code);

        // Persist session as pending
        $sessionId = $this->sessionModel->create($userId, $language, $code);

        $startTime = microtime(true);

        try {
            [$output, $stages, $status] = $this->runCompilation($language, $code);
        } catch (\Throwable $e) {
            $output = 'Compilation error: ' . $e->getMessage();
            $stages = [];
            $status = 'error';
        }

        $executionTime = round(microtime(true) - $startTime, 4);

        // Update with results
        $this->sessionModel->update($sessionId, $output, $status, $executionTime);

        return [
            'session_id'     => $sessionId,
            'status'         => $status,
            'output'         => $output,
            'stages'         => $stages,
            'execution_time' => $executionTime,
        ];
    }

    /**
     * Return paginated compilation history for a user.
     */
    public function getHistory(int $userId, int $page = 1, int $limit = 20): array
    {
        return $this->sessionModel->findByUser($userId, $page, $limit);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    /**
     * Run the simulated compilation pipeline for the given language.
     * In a real deployment this would shell out to gcc/javac/etc. via a
     * sandboxed subprocess; here we perform static analysis on the AST
     * nodes we build from the code string.
     *
     * @return array [string $output, array $stages, string $status]
     */
    private function runCompilation(string $language, string $code): array
    {
        $stages  = self::STAGES[$language] ?? ['Parsing'];
        $results = [];

        foreach ($stages as $stage) {
            $results[] = [
                'stage'   => $stage,
                'status'  => 'ok',
                'message' => "{$stage} completed successfully",
            ];
        }

        // Lightweight syntax check: look for unbalanced braces
        $openBraces  = substr_count($code, '{');
        $closeBraces = substr_count($code, '}');

        if (in_array($language, ['c', 'java', 'go'], true) && $openBraces !== $closeBraces) {
            $results[] = [
                'stage'   => 'Syntax Check',
                'status'  => 'warning',
                'message' => "Unbalanced braces: {$openBraces} opening vs {$closeBraces} closing",
            ];
        }

        $tokens = $this->tokenize($code);
        $ast    = $this->buildAST($tokens, $language);

        $output = json_encode([
            'stages'      => $results,
            'tokens'      => $tokens,
            'ast'         => $ast,
            'token_count' => count($tokens),
            'language'    => $language,
        ], JSON_PRETTY_PRINT);

        return [$output, $results, 'success'];
    }

    /**
     * Very simple tokeniser that produces token tuples useful for visualisation.
     */
    private function tokenize(string $code): array
    {
        $patterns = [
            'KEYWORD'    => '\b(int|float|double|char|void|return|if|else|for|while|do|break|continue|switch|case|default|struct|typedef|const|static|extern|class|public|private|protected|new|import|package|func|var|type|range|map|chan|go|defer|select|interface|string|bool|true|false|null|nil|println|printf|scanf)\b',
            'NUMBER'     => '\b\d+(\.\d+)?\b',
            'STRING'     => '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"',
            'CHAR'       => "'(?:[^'\\\\]|\\\\.)'",  // single-char literal: 'x' or '\n'
            'COMMENT'    => '//[^\n]*|/\*.*?\*/',
            'OPERATOR'   => '[+\-*/%&|^~<>!=]=?|&&|\|\||<<|>>|\+\+|--|->',
            'PUNCTUATION'=> '[{}()\[\];,.]',
            'IDENTIFIER' => '[A-Za-z_]\w*',
            'WHITESPACE' => '\s+',
        ];

        $combined = '(' . implode(')|(', array_values($patterns)) . ')';
        $keys     = array_keys($patterns);
        $tokens   = [];

        if (!preg_match_all('/' . $combined . '/s', $code, $matches, PREG_SET_ORDER)) {
            return $tokens;
        }

        foreach ($matches as $match) {
            foreach ($keys as $i => $type) {
                if (isset($match[$i + 1]) && $match[$i + 1] !== '') {
                    if ($type !== 'WHITESPACE') {
                        $tokens[] = ['type' => $type, 'value' => $match[$i + 1]];
                    }
                    break;
                }
            }
        }

        return $tokens;
    }

    /**
     * Build a minimal AST skeleton from the token stream.
     */
    private function buildAST(array $tokens, string $language): array
    {
        $ast = [
            'type'     => 'Program',
            'language' => $language,
            'body'     => [],
        ];

        $i = 0;
        while ($i < count($tokens)) {
            $token = $tokens[$i];

            if ($token['type'] === 'KEYWORD' && in_array($token['value'], ['int', 'void', 'float', 'double', 'char', 'string', 'func', 'bool'], true)) {
                // Possible function or variable declaration
                $node = ['type' => 'Declaration', 'dataType' => $token['value'], 'children' => []];
                if (isset($tokens[$i + 1]) && $tokens[$i + 1]['type'] === 'IDENTIFIER') {
                    $node['name'] = $tokens[$i + 1]['value'];
                    $i++;
                }
                $ast['body'][] = $node;
            } elseif ($token['type'] === 'KEYWORD' && $token['value'] === 'return') {
                $node = ['type' => 'ReturnStatement', 'children' => []];
                if (isset($tokens[$i + 1])) {
                    $node['children'][] = ['type' => 'Expression', 'value' => $tokens[$i + 1]['value']];
                }
                $ast['body'][] = $node;
            }
            $i++;
        }

        return $ast;
    }
}
