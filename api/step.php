<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$session_id = $_GET['session_id'] ?? '';
$current_step = intval($_GET['step'] ?? 0);
$action = $_GET['action'] ?? 'next'; // 'next', 'prev', 'jump'

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

// Get total number of steps (stages)
$total_steps = count($result['stages'] ?? []);

// Calculate next step based on action
switch ($action) {
    case 'next':
        $next_step = min($current_step + 1, $total_steps - 1);
        break;
    case 'prev':
        $next_step = max($current_step - 1, 0);
        break;
    case 'jump':
        $next_step = min(intval($_GET['to'] ?? $current_step), $total_steps - 1);
        break;
    default:
        $next_step = $current_step;
}

// Get current stage data
$current_stage = $result['stages'][$next_step] ?? null;

if (!$current_stage) {
    echo json_encode(['error' => 'Invalid step']);
    exit;
}

// Generate step data with animation instructions
$step_data = [
    'current_step' => $next_step,
    'total_steps' => $total_steps,
    'stage' => $current_stage,
    'animations' => generateAnimations($next_step, $current_stage),
    'highlights' => generateHighlights($next_step),
    'explanations' => generateExplanations($next_step)
];

echo json_encode($step_data);

function generateAnimations($step, $stage) {
    $animations = [];
    
    switch ($step) {
        case 0: // Lexical Analysis
            $animations[] = [
                'type' => 'highlight_tokens',
                'duration' => 2000,
                'elements' => ['token_*'],
                'color' => '#f39c12'
            ];
            break;
            
        case 1: // Syntax Analysis
            $animations[] = [
                'type' => 'build_ast',
                'duration' => 3000,
                'direction' => 'top_down'
            ];
            break;
            
        case 2: // Semantic Analysis
            $animations[] = [
                'type' => 'connect_symbols',
                'duration' => 2000,
                'elements' => ['symbol_*']
            ];
            break;
            
        case 3: // IR Generation
            $animations[] = [
                'type' => 'flow_animation',
                'duration' => 2500,
                'path' => 'linear',
                'speed' => 'medium'
            ];
            break;
            
        case 4: // Optimization
            $animations[] = [
                'type' => 'transform',
                'duration' => 2000,
                'before' => 'ir_node',
                'after' => 'optimized_node'
            ];
            break;
            
        case 5: // Code Generation
            $animations[] = [
                'type' => 'assembly_build',
                'duration' => 3000,
                'direction' => 'sequential'
            ];
            break;
    }
    
    return $animations;
}

function generateHighlights($step) {
    $highlights = [];
    
    $step_colors = [
        '#3498db', '#2ecc71', '#e74c3c', 
        '#9b59b6', '#f39c12', '#1abc9c'
    ];
    
    $highlights[] = [
        'element' => "stage_{$step}",
        'color' => $step_colors[$step] ?? '#3498db',
        'intensity' => 0.8,
        'pulse' => true
    ];
    
    return $highlights;
}

function generateExplanations($step) {
    $explanations = [
        [
            'step' => 0,
            'title' => 'Lexical Analysis',
            'description' => 'Breaking source code into tokens (keywords, identifiers, operators, etc.)',
            'details' => 'Scanner reads characters and groups them into tokens according to language grammar.'
        ],
        [
            'step' => 1,
            'title' => 'Syntax Analysis',
            'description' => 'Parsing tokens to build Abstract Syntax Tree (AST)',
            'details' => 'Parser checks syntax validity and creates tree structure representing program hierarchy.'
        ],
        [
            'step' => 2,
            'title' => 'Semantic Analysis',
            'description' => 'Validating program meaning and building symbol table',
            'details' => 'Type checking, scope resolution, and semantic rule verification.'
        ],
        [
            'step' => 3,
            'title' => 'IR Generation',
            'description' => 'Generating Intermediate Representation (IR) code',
            'details' => 'Converting AST to platform-independent intermediate code for optimization.'
        ],
        [
            'step' => 4,
            'title' => 'Optimization',
            'description' => 'Applying optimizations to improve code efficiency',
            'details' => 'Constant folding, dead code elimination, loop optimizations, etc.'
        ],
        [
            'step' => 5,
            'title' => 'Code Generation',
            'description' => 'Generating target assembly/machine code',
            'details' => 'Converting optimized IR to specific architecture assembly code.'
        ]
    ];
    
    return $explanations[$step] ?? $explanations[0];
}
?>