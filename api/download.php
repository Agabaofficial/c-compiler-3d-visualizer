<?php
header('Access-Control-Allow-Origin: *');

$session_id = $_GET['session_id'] ?? '';
$type = $_GET['type'] ?? ''; // 'ast', 'cfg', 'asm', 'all', 'tokens', 'ir'
$format = $_GET['format'] ?? 'json'; // 'json', 'txt', 'dot', 'png'

if (empty($session_id) || empty($type)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$result_file = "../tmp/{$session_id}/result.json";

if (!file_exists($result_file)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Compilation result not found']);
    exit;
}

$result = json_decode(file_get_contents($result_file), true);

switch ($type) {
    case 'ast':
        $content = generateASTDownload($result, $format);
        $filename = "ast_visualization.{$format}";
        break;
        
    case 'cfg':
        $content = generateCFGDownload($result, $format);
        $filename = "control_flow_graph.{$format}";
        break;
        
    case 'asm':
        $content = generateAsmDownload($result, $format);
        $filename = "assembly_code.{$format}";
        break;
        
    case 'tokens':
        $content = generateTokensDownload($result, $format);
        $filename = "tokens_list.{$format}";
        break;
        
    case 'ir':
        $content = generateIRDownload($result, $format);
        $filename = "intermediate_code.{$format}";
        break;
        
    case 'all':
        // Create zip file
        $zip_filename = createDownloadZip($session_id, $result);
        serveFile($zip_filename, "compilation_results.zip", "application/zip");
        exit;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid download type']);
        exit;
}

// Set appropriate headers and serve content
$mime_types = [
    'json' => 'application/json',
    'txt' => 'text/plain',
    'dot' => 'text/plain',
    'png' => 'image/png',
    'svg' => 'image/svg+xml'
];

$content_type = $mime_types[$format] ?? 'application/octet-stream';
header("Content-Type: {$content_type}");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Content-Length: " . strlen($content));

echo $content;

function generateASTDownload($result, $format) {
    $ast = $result['outputs']['ast'] ?? [];
    
    switch ($format) {
        case 'json':
            return json_encode($ast, JSON_PRETTY_PRINT);
            
        case 'dot':
            return astToDOT($ast);
            
        case 'txt':
            return astToText($ast);
            
        default:
            return json_encode($ast, JSON_PRETTY_PRINT);
    }
}

function generateCFGDownload($result, $format) {
    $cfg = generateCFGFromIR($result['outputs']['ir'] ?? []);
    
    switch ($format) {
        case 'json':
            return json_encode($cfg, JSON_PRETTY_PRINT);
            
        case 'dot':
            return cfgToDOT($cfg);
            
        case 'txt':
            return cfgToText($cfg);
            
        default:
            return json_encode($cfg, JSON_PRETTY_PRINT);
    }
}

function generateAsmDownload($result, $format) {
    $asm = $result['outputs']['asm'] ?? [];
    
    switch ($format) {
        case 'txt':
            return implode("\n", $asm);
            
        case 'json':
            return json_encode($asm, JSON_PRETTY_PRINT);
            
        default:
            return implode("\n", $asm);
    }
}

function generateTokensDownload($result, $format) {
    $tokens = $result['outputs']['tokens'] ?? [];
    
    switch ($format) {
        case 'json':
            return json_encode($tokens, JSON_PRETTY_PRINT);
            
        case 'txt':
            $output = "";
            foreach ($tokens as $token) {
                $output .= sprintf("%-15s %-20s Line %d\n", 
                    $token['type'], 
                    $token['value'],
                    $token['line']
                );
            }
            return $output;
            
        default:
            return json_encode($tokens, JSON_PRETTY_PRINT);
    }
}

function generateIRDownload($result, $format) {
    $ir = $result['outputs']['ir'] ?? [];
    
    switch ($format) {
        case 'json':
            return json_encode($ir, JSON_PRETTY_PRINT);
            
        case 'txt':
            $output = "";
            foreach ($ir as $instruction) {
                if (isset($instruction['label'])) {
                    $output .= $instruction['label'] . ":\n";
                } else {
                    $output .= "    " . $instruction['op'];
                    if (isset($instruction['dest'])) $output .= " " . $instruction['dest'];
                    if (isset($instruction['src'])) $output .= ", " . $instruction['src'];
                    if (isset($instruction['src1'])) $output .= ", " . $instruction['src1'];
                    if (isset($instruction['src2'])) $output .= ", " . $instruction['src2'];
                    if (isset($instruction['value'])) $output .= ", " . $instruction['value'];
                    $output .= "\n";
                }
            }
            return $output;
            
        default:
            return json_encode($ir, JSON_PRETTY_PRINT);
    }
}

function astToDOT($ast) {
    $dot = "digraph AST {\n";
    $dot .= "  node [shape=box, style=filled, color=lightblue];\n\n";
    
    $dot .= generateDOTNodes($ast, 0);
    
    $dot .= "}\n";
    return $dot;
}

function generateDOTNodes($node, &$id) {
    static $counter = 0;
    $current_id = $counter++;
    
    $label = $node['type'] ?? 'Node';
    if (isset($node['name'])) $label .= "\\n" . $node['name'];
    if (isset($node['value'])) $label .= "\\n" . $node['value'];
    
    $dot = "  node{$current_id} [label=\"{$label}\"];\n";
    
    // Handle children
    if (isset($node['body']) && is_array($node['body'])) {
        foreach ($node['body'] as $child) {
            $child_id = $counter;
            $dot .= generateDOTNodes($child, $child_id);
            $dot .= "  node{$current_id} -> node{$child_id};\n";
        }
    }
    
    return $dot;
}

function cfgToDOT($cfg) {
    $dot = "digraph CFG {\n";
    $dot .= "  node [shape=box, style=rounded];\n\n";
    
    foreach ($cfg['nodes'] as $node) {
        $label = str_replace('"', '\\"', $node['label']);
        $dot .= "  {$node['id']} [label=\"{$label}\"];\n";
    }
    
    foreach ($cfg['edges'] as $edge) {
        $dot .= "  {$edge['from']} -> {$edge['to']}";
        if (isset($edge['label'])) {
            $dot .= " [label=\"{$edge['label']}\"]";
        }
        $dot .= ";\n";
    }
    
    $dot .= "}\n";
    return $dot;
}

function generateCFGFromIR($ir) {
    $nodes = [];
    $edges = [];
    $current_node = null;
    
    foreach ($ir as $index => $instruction) {
        if (isset($instruction['label'])) {
            $current_node = $instruction['label'];
            $nodes[] = [
                'id' => $current_node,
                'label' => $current_node . ":",
                'instructions' => []
            ];
        } elseif ($current_node) {
            // Add instruction to current node
            $node_index = array_search($current_node, array_column($nodes, 'id'));
            if ($node_index !== false) {
                $nodes[$node_index]['instructions'][] = $instruction['op'];
            }
        }
        
        // Add edges for jumps
        if (isset($instruction['target'])) {
            $edges[] = [
                'from' => $current_node ?? 'start',
                'to' => $instruction['target'],
                'label' => 'jump'
            ];
        }
    }
    
    return ['nodes' => $nodes, 'edges' => $edges];
}

function createDownloadZip($session_id, $result) {
    $zip = new ZipArchive();
    $zip_filename = "../tmp/{$session_id}/compilation_results.zip";
    
    if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        // Add AST
        $zip->addFromString('ast.json', json_encode($result['outputs']['ast'] ?? [], JSON_PRETTY_PRINT));
        
        // Add tokens
        $zip->addFromString('tokens.json', json_encode($result['outputs']['tokens'] ?? [], JSON_PRETTY_PRINT));
        
        // Add IR
        $zip->addFromString('ir.json', json_encode($result['outputs']['ir'] ?? [], JSON_PRETTY_PRINT));
        
        // Add assembly
        $zip->addFromString('assembly.txt', implode("\n", $result['outputs']['asm'] ?? []));
        
        // Add CFG
        $cfg = generateCFGFromIR($result['outputs']['ir'] ?? []);
        $zip->addFromString('cfg.dot', cfgToDOT($cfg));
        
        // Add summary
        $summary = [
            'timestamp' => date('Y-m-d H:i:s'),
            'stages' => array_column($result['stages'] ?? [], 'name'),
            'total_duration' => array_sum(array_column($result['stages'] ?? [], 'duration'))
        ];
        $zip->addFromString('summary.json', json_encode($summary, JSON_PRETTY_PRINT));
        
        $zip->close();
    }
    
    return $zip_filename;
}

function serveFile($filepath, $filename, $content_type) {
    if (file_exists($filepath)) {
        header("Content-Type: {$content_type}");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Content-Length: " . filesize($filepath));
        readfile($filepath);
    }
}
?>