<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Create tmp directory if it doesn't exist
if (!is_dir('../tmp')) {
    mkdir('../tmp', 0777, true);
}

// Generate unique session ID
$session_id = uniqid('compile_', true);
$tmp_dir = "../tmp/{$session_id}";
mkdir($tmp_dir, 0777, true);

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$source_code = $data['source_code'] ?? '';

if (empty($source_code)) {
    echo json_encode(['error' => 'No source code provided']);
    exit;
}

// Save source code to temporary file
$source_file = "{$tmp_dir}/source.c";
file_put_contents($source_file, $source_code);

// Simulate compilation pipeline stages
$result = [
    'session_id' => $session_id,
    'success' => true,
    'stages' => [],
    'outputs' => []
];

// Stage 1: Lexical Analysis
$result['stages'][] = [
    'name' => 'Lexical Analysis',
    'status' => 'completed',
    'duration' => rand(50, 200),
    'tokens' => generateTokens($source_code)
];

// Stage 2: Syntax Analysis
$result['stages'][] = [
    'name' => 'Syntax Analysis',
    'status' => 'completed',
    'duration' => rand(100, 300),
    'ast' => generateAST($source_code)
];

// Stage 3: Semantic Analysis
$result['stages'][] = [
    'name' => 'Semantic Analysis',
    'status' => 'completed',
    'duration' => rand(80, 250),
    'symbol_table' => generateSymbolTable($source_code)
];

// Stage 4: IR Generation
$result['stages'][] = [
    'name' => 'IR Generation',
    'status' => 'completed',
    'duration' => rand(150, 400),
    'ir_code' => generateIR($source_code)
];

// Stage 5: Optimization
$result['stages'][] = [
    'name' => 'Optimization',
    'status' => 'completed',
    'duration' => rand(200, 500),
    'optimizations' => generateOptimizations()
];

// Stage 6: Code Generation
$result['stages'][] = [
    'name' => 'Code Generation',
    'status' => 'completed',
    'duration' => rand(250, 600),
    'assembly' => generateAssembly($source_code)
];

$result['outputs'] = [
    'tokens' => $result['stages'][0]['tokens'],
    'ast' => $result['stages'][1]['ast'],
    'ir' => $result['stages'][3]['ir_code'],
    'asm' => $result['stages'][5]['assembly']
];

// Save compilation result
file_put_contents("{$tmp_dir}/result.json", json_encode($result, JSON_PRETTY_PRINT));

echo json_encode($result);

// Helper functions for simulation
function generateTokens($code) {
    $tokens = [];
    $lines = explode("\n", $code);
    
    $keywords = ['int', 'return', 'if', 'else', 'for', 'while', 'printf', 'main'];
    $operators = ['=', '+', '-', '*', '/', '>', '<', '==', '!='];
    $delimiters = [';', '(', ')', '{', '}', ','];
    
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $words = preg_split('/\s+|(?<=[(){};=+-\/*<>])|(?=[(){};=+-\/*<>])/', $line, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($words as $word) {
            $type = 'IDENTIFIER';
            
            if (in_array($word, $keywords)) $type = 'KEYWORD';
            elseif (in_array($word, $operators)) $type = 'OPERATOR';
            elseif (in_array($word, $delimiters)) $type = 'DELIMITER';
            elseif (is_numeric($word)) $type = 'LITERAL';
            elseif (strpos($word, '"') !== false) $type = 'STRING_LITERAL';
            
            $tokens[] = [
                'type' => $type,
                'value' => $word,
                'line' => $lineNum + 1
            ];
        }
    }
    
    return $tokens;
}

function generateAST($code) {
    return [
        'type' => 'Program',
        'body' => [
            [
                'type' => 'FunctionDeclaration',
                'name' => 'main',
                'params' => [],
                'body' => [
                    [
                        'type' => 'VariableDeclaration',
                        'declarations' => [
                            ['type' => 'Identifier', 'name' => 'a', 'init' => ['type' => 'Literal', 'value' => 5]],
                            ['type' => 'Identifier', 'name' => 'b', 'init' => ['type' => 'Literal', 'value' => 10]],
                            ['type' => 'Identifier', 'name' => 'sum', 'init' => [
                                'type' => 'BinaryExpression',
                                'operator' => '+',
                                'left' => ['type' => 'Identifier', 'name' => 'a'],
                                'right' => ['type' => 'Identifier', 'name' => 'b']
                            ]]
                        ]
                    ],
                    [
                        'type' => 'IfStatement',
                        'test' => [
                            'type' => 'BinaryExpression',
                            'operator' => '>',
                            'left' => ['type' => 'Identifier', 'name' => 'sum'],
                            'right' => ['type' => 'Literal', 'value' => 10]
                        ],
                        'consequent' => [
                            'type' => 'BlockStatement',
                            'body' => [[
                                'type' => 'CallExpression',
                                'callee' => ['type' => 'Identifier', 'name' => 'printf'],
                                'arguments' => [
                                    ['type' => 'Literal', 'value' => 'Sum is greater than 10: %d\\n'],
                                    ['type' => 'Identifier', 'name' => 'sum']
                                ]
                            ]]
                        ],
                        'alternate' => [
                            'type' => 'BlockStatement',
                            'body' => [[
                                'type' => 'CallExpression',
                                'callee' => ['type' => 'Identifier', 'name' => 'printf'],
                                'arguments' => [
                                    ['type' => 'Literal', 'value' => 'Sum is 10 or less: %d\\n'],
                                    ['type' => 'Identifier', 'name' => 'sum']
                                ]
                            ]]
                        ]
                    ],
                    [
                        'type' => 'ForStatement',
                        'init' => [
                            'type' => 'VariableDeclaration',
                            'declarations' => [[
                                'type' => 'Identifier',
                                'name' => 'i',
                                'init' => ['type' => 'Literal', 'value' => 0]
                            ]]
                        ],
                        'test' => [
                            'type' => 'BinaryExpression',
                            'operator' => '<',
                            'left' => ['type' => 'Identifier', 'name' => 'i'],
                            'right' => ['type' => 'Literal', 'value' => 3]
                        ],
                        'update' => [
                            'type' => 'UpdateExpression',
                            'operator' => '++',
                            'argument' => ['type' => 'Identifier', 'name' => 'i']
                        ],
                        'body' => [
                            'type' => 'BlockStatement',
                            'body' => [[
                                'type' => 'CallExpression',
                                'callee' => ['type' => 'Identifier', 'name' => 'printf'],
                                'arguments' => [
                                    ['type' => 'Literal', 'value' => 'Iteration %d\\n'],
                                    ['type' => 'Identifier', 'name' => 'i']
                                ]
                            ]]
                        ]
                    ],
                    [
                        'type' => 'ReturnStatement',
                        'argument' => ['type' => 'Literal', 'value' => 0]
                    ]
                ]
            ]
        ]
    ];
}

function generateSymbolTable($code) {
    return [
        'global' => [
            ['name' => 'main', 'type' => 'function', 'return_type' => 'int', 'scope' => 'global']
        ],
        'main' => [
            ['name' => 'a', 'type' => 'int', 'initialized' => true, 'scope' => 'local'],
            ['name' => 'b', 'type' => 'int', 'initialized' => true, 'scope' => 'local'],
            ['name' => 'sum', 'type' => 'int', 'initialized' => true, 'scope' => 'local'],
            ['name' => 'i', 'type' => 'int', 'initialized' => true, 'scope' => 'local']
        ]
    ];
}

function generateIR($code) {
    return [
        ['op' => 'ALLOC', 'dest' => 'a', 'type' => 'int'],
        ['op' => 'ALLOC', 'dest' => 'b', 'type' => 'int'],
        ['op' => 'ALLOC', 'dest' => 'sum', 'type' => 'int'],
        ['op' => 'STORE', 'dest' => 'a', 'value' => 5],
        ['op' => 'STORE', 'dest' => 'b', 'value' => 10],
        ['op' => 'LOAD', 'dest' => 't1', 'src' => 'a'],
        ['op' => 'LOAD', 'dest' => 't2', 'src' => 'b'],
        ['op' => 'ADD', 'dest' => 'sum', 'src1' => 't1', 'src2' => 't2'],
        ['op' => 'CMP', 'dest' => 't3', 'src1' => 'sum', 'src2' => 10],
        ['op' => 'JLE', 'target' => 'L1'],
        ['op' => 'CALL', 'func' => 'printf', 'args' => ['"Sum is greater than 10: %d\\n"', 'sum']],
        ['op' => 'JMP', 'target' => 'L2'],
        ['label' => 'L1'],
        ['op' => 'CALL', 'func' => 'printf', 'args' => ['"Sum is 10 or less: %d\\n"', 'sum']],
        ['label' => 'L2'],
        ['op' => 'STORE', 'dest' => 'i', 'value' => 0],
        ['label' => 'L3'],
        ['op' => 'CMP', 'dest' => 't4', 'src1' => 'i', 'src2' => 3],
        ['op' => 'JGE', 'target' => 'L4'],
        ['op' => 'CALL', 'func' => 'printf', 'args' => ['"Iteration %d\\n"', 'i']],
        ['op' => 'INC', 'dest' => 'i'],
        ['op' => 'JMP', 'target' => 'L3'],
        ['label' => 'L4'],
        ['op' => 'RET', 'value' => 0]
    ];
}

function generateOptimizations() {
    return [
        'constant_folding' => true,
        'dead_code_elimination' => false,
        'common_subexpression' => true,
        'loop_unrolling' => false
    ];
}

function generateAssembly($code) {
    return [
        '.section .text',
        '.globl main',
        'main:',
        '    push %rbp',
        '    mov %rsp, %rbp',
        '    sub $16, %rsp',
        '    movl $5, -4(%rbp)    # a = 5',
        '    movl $10, -8(%rbp)   # b = 10',
        '    movl -4(%rbp), %eax',
        '    addl -8(%rbp), %eax',
        '    movl %eax, -12(%rbp) # sum = a + b',
        '    cmpl $10, -12(%rbp)',
        '    jle .L1',
        '    # printf for greater than 10',
        '    jmp .L2',
        '.L1:',
        '    # printf for less or equal',
        '.L2:',
        '    movl $0, -16(%rbp)   # i = 0',
        '.L3:',
        '    cmpl $3, -16(%rbp)',
        '    jge .L4',
        '    # printf for iteration',
        '    incl -16(%rbp)',
        '    jmp .L3',
        '.L4:',
        '    movl $0, %eax',
        '    leave',
        '    ret'
    ];
}
?>