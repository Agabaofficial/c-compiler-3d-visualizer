<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$session_id = $_GET['session_id'] ?? '';
$stage = $_GET['stage'] ?? 'all';

if (empty($session_id)) {
    echo json_encode(['error' => 'No session ID provided']);
    exit;
}

$result_file = "../tmp/{$session_id}/result.json";

if (!file_exists($result_file)) {
    echo json_encode(['error' => 'Compilation result not found']);
    exit;
}

$result = json_decode(file_get_contents($result_file), true);

// Generate 3D visualization data based on stage
$visualization_data = generateVisualizationData($result, $stage);

echo json_encode($visualization_data);

function generateVisualizationData($result, $stage) {
    $data = [
        'nodes' => [],
        'edges' => [],
        'metadata' => []
    ];
    
    switch ($stage) {
        case 'all':
            $data = generatePipelineView($result);
            break;
        case 'lexical':
            $data = generateLexicalView($result);
            break;
        case 'syntax':
            $data = generateSyntaxView($result);
            break;
        case 'semantic':
            $data = generateSemanticView($result);
            break;
        case 'ir':
            $data = generateIRView($result);
            break;
        case 'optimization':
            $data = generateOptimizationView($result);
            break;
        case 'codegen':
            $data = generateCodegenView($result);
            break;
    }
    
    return $data;
}

function generatePipelineView($result) {
    $nodes = [];
    $edges = [];
    
    $stages = ['Lexical Analysis', 'Syntax Analysis', 'Semantic Analysis', 
               'IR Generation', 'Optimization', 'Code Generation'];
    
    $x = -300;
    foreach ($stages as $index => $stageName) {
        $nodes[] = [
            'id' => "stage_{$index}",
            'type' => 'stage',
            'name' => $stageName,
            'position' => ['x' => $x, 'y' => 0, 'z' => 0],
            'color' => getStageColor($index),
            'status' => 'completed',
            'metrics' => [
                'duration' => rand(50, 600),
                'complexity' => rand(1, 10)
            ]
        ];
        
        if ($index > 0) {
            $edges[] = [
                'from' => "stage_" . ($index - 1),
                'to' => "stage_{$index}",
                'type' => 'pipeline',
                'flow' => true
            ];
        }
        
        $x += 120;
    }
    
    return ['nodes' => $nodes, 'edges' => $edges];
}

function generateLexicalView($result) {
    $nodes = [];
    $edges = [];
    
    $tokens = $result['outputs']['tokens'] ?? [];
    $y = 50;
    
    foreach ($tokens as $index => $token) {
        $nodes[] = [
            'id' => "token_{$index}",
            'type' => 'token',
            'name' => $token['value'],
            'token_type' => $token['type'],
            'position' => ['x' => $index * 40 - 200, 'y' => $y, 'z' => 0],
            'color' => getTokenColor($token['type'])
        ];
    }
    
    return ['nodes' => $nodes, 'edges' => $edges];
}

function generateSyntaxView($result) {
    $ast = $result['outputs']['ast'] ?? [];
    return generateASTNodes($ast);
}

function generateASTNodes($node, $parent_id = null, $depth = 0, $index = 0) {
    static $nodes = [];
    static $edges = [];
    static $node_id = 0;
    
    if ($depth === 0) {
        $nodes = [];
        $edges = [];
        $node_id = 0;
    }
    
    $current_id = "ast_{$node_id++}";
    
    $x = $index * 100 - 200;
    $y = -$depth * 60;
    
    $nodes[] = [
        'id' => $current_id,
        'type' => 'ast_node',
        'name' => $node['type'] ?? 'Node',
        'value' => $node['name'] ?? $node['value'] ?? '',
        'position' => ['x' => $x, 'y' => $y, 'z' => 0],
        'color' => getASTColor($node['type'] ?? '')
    ];
    
    if ($parent_id !== null) {
        $edges[] = [
            'from' => $parent_id,
            'to' => $current_id,
            'type' => 'parent_child'
        ];
    }
    
    // Handle children
    if (isset($node['body']) && is_array($node['body'])) {
        foreach ($node['body'] as $child_index => $child) {
            generateASTNodes($child, $current_id, $depth + 1, $child_index);
        }
    }
    
    if (isset($node['declarations']) && is_array($node['declarations'])) {
        foreach ($node['declarations'] as $child_index => $child) {
            generateASTNodes($child, $current_id, $depth + 1, $child_index);
        }
    }
    
    if (isset($node['consequent'])) {
        generateASTNodes($node['consequent'], $current_id, $depth + 1, 0);
    }
    
    if (isset($node['alternate'])) {
        generateASTNodes($node['alternate'], $current_id, $depth + 1, 1);
    }
    
    if ($depth === 0) {
        return ['nodes' => $nodes, 'edges' => $edges];
    }
}

function generateSemanticView($result) {
    $nodes = [];
    $edges = [];
    
    $symbols = [
        ['name' => 'main', 'type' => 'function', 'scope' => 'global'],
        ['name' => 'a', 'type' => 'int', 'scope' => 'main'],
        ['name' => 'b', 'type' => 'int', 'scope' => 'main'],
        ['name' => 'sum', 'type' => 'int', 'scope' => 'main'],
        ['name' => 'i', 'type' => 'int', 'scope' => 'main']
    ];
    
    foreach ($symbols as $index => $symbol) {
        $nodes[] = [
            'id' => "symbol_{$index}",
            'type' => 'symbol',
            'name' => $symbol['name'],
            'symbol_type' => $symbol['type'],
            'scope' => $symbol['scope'],
            'position' => ['x' => -100 + ($index % 3) * 80, 'y' => 50 - floor($index / 3) * 60, 'z' => 0],
            'color' => getSymbolColor($symbol['type'])
        ];
    }
    
    return ['nodes' => $nodes, 'edges' => $edges];
}

function getStageColor($index) {
    $colors = [
        '#3498db', // Blue
        '#2ecc71', // Green
        '#e74c3c', // Red
        '#9b59b6', // Purple
        '#f39c12', // Orange
        '#1abc9c'  // Turquoise
    ];
    return $colors[$index % count($colors)];
}

function getTokenColor($type) {
    $colors = [
        'KEYWORD' => '#e74c3c',
        'IDENTIFIER' => '#3498db',
        'OPERATOR' => '#f39c12',
        'DELIMITER' => '#95a5a6',
        'LITERAL' => '#2ecc71',
        'STRING_LITERAL' => '#9b59b6'
    ];
    return $colors[$type] ?? '#7f8c8d';
}

function getASTColor($type) {
    $colors = [
        'Program' => '#2c3e50',
        'FunctionDeclaration' => '#3498db',
        'VariableDeclaration' => '#2ecc71',
        'IfStatement' => '#e74c3c',
        'ForStatement' => '#9b59b6',
        'CallExpression' => '#f39c12',
        'Identifier' => '#1abc9c',
        'Literal' => '#34495e'
    ];
    return $colors[$type] ?? '#7f8c8d';
}

function getSymbolColor($type) {
    $colors = [
        'function' => '#3498db',
        'int' => '#2ecc71',
        'float' => '#e74c3c',
        'char' => '#9b59b6'
    ];
    return $colors[$type] ?? '#7f8c8d';
}
?>