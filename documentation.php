<?php
// documentation.php - CompilerHub Documentation
// Turn off error display but log errors
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'docs_errors.log');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 Documentation | CompilerHub Visualizer</title>
    
    <!-- Prism.js for code highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-java.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-c.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-cpp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-swift.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-go.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500&family=Poppins:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Reuse color scheme from index.php */
        :root {
            --primary: #00ff9d;
            --primary-dark: #00d9a6;
            --secondary: #6c63ff;
            --accent: #ff2e63;
            --dark: #0a192f;
            --darker: #071121;
            --light: #ccd6f6;
            --gray: #8892b0;
            --neon-glow: 0 0 20px var(--primary);
            --neon-secondary: 0 0 15px var(--secondary);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 100%);
            color: var(--light);
            overflow-x: hidden;
            min-height: 100vh;
            line-height: 1.6;
        }
        
        /* Tech Grid Background */
        .tech-grid {
            position: fixed;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 157, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 157, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -3;
            animation: gridMove 20s linear infinite;
        }
        
        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header & Navigation */
        .header {
            background: rgba(10, 25, 47, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            position: relative;
        }
        
        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            font-size: 22px;
            box-shadow: var(--neon-glow);
        }
        
        .logo-text {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(0, 255, 157, 0.3);
        }
        
        .nav-links {
            display: flex;
            gap: 35px;
            list-style: none;
        }
        
        .nav-links a {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
            position: relative;
            padding: 5px 0;
            font-family: 'Orbitron', sans-serif;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .nav-links a::before {
            content: '> ';
            color: var(--primary);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .nav-links a:hover::before {
            opacity: 1;
        }
        
        .github-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            font-family: 'Orbitron', sans-serif;
        }
        
        .github-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--neon-glow);
        }
        
        /* Documentation Layout */
        .docs-container {
            display: flex;
            min-height: calc(100vh - 80px);
            padding-top: 100px;
        }
        
        .docs-sidebar {
            width: 280px;
            background: rgba(17, 34, 64, 0.6);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(0, 255, 157, 0.1);
            padding: 30px 20px;
            position: fixed;
            height: calc(100vh - 80px);
            overflow-y: auto;
            scrollbar-width: thin;
        }
        
        .docs-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .docs-sidebar::-webkit-scrollbar-track {
            background: rgba(10, 25, 47, 0.5);
        }
        
        .docs-sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 3px;
        }
        
        .sidebar-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 1.3rem;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 255, 157, 0.2);
        }
        
        .sidebar-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: var(--light);
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-links {
            list-style: none;
            padding-left: 15px;
        }
        
        .sidebar-links li {
            margin-bottom: 12px;
        }
        
        .sidebar-links a {
            color: var(--gray);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .sidebar-links a:hover {
            color: var(--primary);
        }
        
        .sidebar-links a.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .sidebar-links a::before {
            content: '▸';
            font-size: 0.8rem;
            color: var(--primary);
        }
        
        /* Main Content */
        .docs-content {
            flex: 1;
            margin-left: 300px;
            padding: 40px;
            max-width: 1100px;
        }
        
        .docs-hero {
            text-align: center;
            margin-bottom: 60px;
            padding: 40px;
            background: rgba(17, 34, 64, 0.4);
            border-radius: 20px;
            border: 1px solid rgba(0, 255, 157, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .docs-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 3.5rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 157, 0.3);
        }
        
        .docs-subtitle {
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px;
            line-height: 1.8;
        }
        
        .search-box {
            max-width: 600px;
            margin: 30px auto 0;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            background: rgba(10, 25, 47, 0.7);
            border: 1px solid rgba(0, 255, 157, 0.3);
            border-radius: 12px;
            color: var(--light);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: var(--neon-glow);
        }
        
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }
        
        /* Documentation Sections */
        .section-card {
            background: rgba(17, 34, 64, 0.4);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid rgba(0, 255, 157, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s;
        }
        
        .section-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
        }
        
        .section-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            font-size: 24px;
        }
        
        .section-card h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: var(--primary);
            margin: 0;
        }
        
        .section-card h3 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            color: var(--light);
            margin: 30px 0 15px;
            padding-left: 10px;
            border-left: 4px solid var(--primary);
        }
        
        .section-card h4 {
            font-family: 'Inter', sans-serif;
            font-size: 1.2rem;
            color: var(--primary);
            margin: 20px 0 10px;
        }
        
        .section-card p {
            color: var(--gray);
            margin-bottom: 20px;
            line-height: 1.8;
        }
        
        .section-card ul, .section-card ol {
            color: var(--gray);
            margin-bottom: 20px;
            padding-left: 25px;
        }
        
        .section-card li {
            margin-bottom: 10px;
            line-height: 1.7;
        }
        
        .section-card li strong {
            color: var(--light);
        }
        
        /* Code Blocks */
        .code-block {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 12px;
            margin: 25px 0;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 157, 0.2);
        }
        
        .code-header {
            background: rgba(10, 25, 47, 0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .code-language {
            font-family: 'JetBrains Mono', monospace;
            color: var(--primary);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .copy-btn {
            background: rgba(0, 255, 157, 0.1);
            border: 1px solid rgba(0, 255, 157, 0.3);
            color: var(--primary);
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .copy-btn:hover {
            background: rgba(0, 255, 157, 0.2);
            transform: translateY(-2px);
        }
        
        .code-content {
            padding: 20px;
            overflow-x: auto;
        }
        
        .code-content pre {
            margin: 0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        /* Info Boxes */
        .info-box {
            background: rgba(0, 255, 157, 0.05);
            border-left: 4px solid var(--primary);
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .warning-box {
            background: rgba(255, 46, 99, 0.05);
            border-left: 4px solid var(--accent);
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .success-box {
            background: rgba(0, 255, 157, 0.1);
            border-left: 4px solid var(--primary);
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .info-box h4, .warning-box h4, .success-box h4 {
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* API Reference */
        .api-endpoint {
            background: rgba(17, 34, 64, 0.6);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid rgba(0, 255, 157, 0.2);
        }
        
        .endpoint-method {
            display: inline-block;
            padding: 6px 12px;
            background: var(--primary);
            color: var(--dark);
            font-weight: 600;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-right: 15px;
            font-family: 'JetBrains Mono', monospace;
        }
        
        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            color: var(--light);
            font-size: 1.1rem;
        }
        
        .endpoint-description {
            color: var(--gray);
            margin: 15px 0;
            line-height: 1.7;
        }
        
        .endpoint-params {
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .param-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .param-table th {
            text-align: left;
            padding: 12px;
            color: var(--primary);
            font-weight: 600;
            border-bottom: 1px solid rgba(0, 255, 157, 0.2);
        }
        
        .param-table td {
            padding: 12px;
            color: var(--gray);
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
        }
        
        .param-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Quick Links Grid */
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .quick-link-card {
            background: rgba(17, 34, 64, 0.4);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(0, 255, 157, 0.1);
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }
        
        .quick-link-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: var(--neon-glow);
        }
        
        .quick-link-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            font-size: 22px;
            margin-bottom: 20px;
        }
        
        .quick-link-card h3 {
            color: var(--light);
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .quick-link-card p {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            background: rgba(10, 25, 47, 0.98);
            padding: 80px 0 40px;
            border-top: 1px solid rgba(0, 255, 157, 0.1);
            position: relative;
            margin-top: 100px;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            box-shadow: 0 0 20px var(--primary);
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 60px;
        }
        
        .footer-column h3 {
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 1.3rem;
            font-family: 'Orbitron', sans-serif;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 15px;
        }
        
        .footer-links a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .footer-links a::before {
            content: '▸';
            color: var(--primary);
            font-size: 0.8rem;
        }
        
        .copyright {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid rgba(0, 255, 157, 0.1);
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .authors {
            color: var(--primary);
            font-weight: 600;
            margin-top: 10px;
            font-family: 'Orbitron', sans-serif;
        }
        
        /* Mobile Responsive */
        @media (max-width: 1200px) {
            .docs-sidebar {
                width: 250px;
            }
            
            .docs-content {
                margin-left: 270px;
            }
        }
        
        @media (max-width: 992px) {
            .docs-container {
                flex-direction: column;
            }
            
            .docs-sidebar {
                width: 100%;
                position: static;
                height: auto;
                max-height: 300px;
                margin-bottom: 30px;
            }
            
            .docs-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .docs-title {
                font-size: 2.8rem;
            }
            
            .nav-links, .github-btn {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .docs-title {
                font-size: 2.3rem;
            }
            
            .section-card {
                padding: 25px;
            }
            
            .section-card h2 {
                font-size: 1.7rem;
            }
            
            .quick-links-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .docs-title {
                font-size: 2rem;
            }
            
            .section-card {
                padding: 20px;
            }
            
            .docs-hero {
                padding: 25px;
            }
        }
        
        /* Custom scrollbar for main content */
        .docs-content::-webkit-scrollbar {
            width: 10px;
        }
        
        .docs-content::-webkit-scrollbar-track {
            background: rgba(10, 25, 47, 0.5);
        }
        
        .docs-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 5px;
        }
        
        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            text-decoration: none;
            font-size: 20px;
            box-shadow: var(--neon-glow);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s;
            z-index: 100;
        }
        
        .back-to-top.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 30px var(--primary);
        }
        
        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.8rem;
            cursor: pointer;
        }
        
        /* Language badges */
        .language-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 8px;
            margin-bottom: 8px;
            font-family: 'JetBrains Mono', monospace;
        }
        
        .java-badge { background: rgba(0, 115, 150, 0.2); color: #00ADD8; border: 1px solid #00ADD8; }
        .cpp-badge { background: rgba(0, 89, 156, 0.2); color: #00599C; border: 1px solid #00599C; }
        .c-badge { background: rgba(168, 185, 204, 0.2); color: #A8B9CC; border: 1px solid #A8B9CC; }
        .swift-badge { background: rgba(250, 115, 67, 0.2); color: #FA7343; border: 1px solid #FA7343; }
        .brainfuck-badge { background: rgba(44, 62, 80, 0.2); color: #2C3E50; border: 1px solid #2C3E50; }
        .go-badge { background: rgba(0, 173, 216, 0.2); color: #00ADD8; border: 1px solid #00ADD8; }
    </style>
</head>
<body>
    <!-- Tech Grid Background -->
    <div class="tech-grid"></div>
    
    <!-- Header -->
    <header class="header">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="logo-text">CompilerHub Docs</div>
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="#getting-started">Getting Started</a></li>
                    <li><a href="#languages">Languages</a></li>
                    <li><a href="#api">API Reference</a></li>
                </ul>
            </nav>
            
            <a href="https://github.com/Agabaofficial/compiler-visualizer-hub" class="github-btn" target="_blank">
                <i class="fab fa-github"></i> GitHub
            </a>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    
    <!-- Documentation Container -->
    <div class="docs-container">
        <!-- Sidebar -->
        <aside class="docs-sidebar" id="sidebar">
            <div class="sidebar-title">
                <i class="fas fa-book-open"></i> Documentation
            </div>
            
            <div class="sidebar-section">
                <div class="section-title">
                    <i class="fas fa-rocket"></i>
                    <span>Getting Started</span>
                </div>
                <ul class="sidebar-links">
                    <li><a href="#introduction" class="active">Introduction</a></li>
                    <li><a href="#installation">Installation</a></li>
                    <li><a href="#quick-start">Quick Start</a></li>
                    <li><a href="#configuration">Configuration</a></li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <div class="section-title">
                    <i class="fas fa-code"></i>
                    <span>Languages</span>
                </div>
                <ul class="sidebar-links">
                    <li><a href="#java-visualizer">Java Visualizer</a></li>
                    <li><a href="#cpp-visualizer">C++ Visualizer</a></li>
                    <li><a href="#c-visualizer">C Visualizer</a></li>
                    <li><a href="#swift-visualizer">Swift Visualizer</a></li>
                    <li><a href="#brainfuck-visualizer">Brainfuck Visualizer</a></li>
                    <li><a href="#go-visualizer">Go Visualizer</a></li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <div class="section-title">
                    <i class="fas fa-cogs"></i>
                    <span>API Reference</span>
                </div>
                <ul class="sidebar-links">
                    <li><a href="#api-overview">API Overview</a></li>
                    <li><a href="#compilation-api">Compilation API</a></li>
                    <li><a href="#visualization-api">Visualization API</a></li>
                    <li><a href="#analysis-api">Analysis API</a></li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <div class="section-title">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Guides</span>
                </div>
                <ul class="sidebar-links">
                    <li><a href="#educational-use">Educational Use</a></li>
                    <li><a href="#adding-languages">Adding Languages</a></li>
                    <li><a href="#troubleshooting">Troubleshooting</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <div class="section-title">
                    <i class="fas fa-share-alt"></i>
                    <span>Community</span>
                </div>
                <ul class="sidebar-links">
                    <li><a href="#contributing">Contributing</a></li>
                    <li><a href="#license">License</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="docs-content" id="docsContent">
            <!-- Hero Section -->
            <section class="docs-hero" id="introduction">
                <h1 class="docs-title">CompilerHub Documentation</h1>
                <p class="docs-subtitle">
                    Complete guide to using CompilerHub - the advanced compiler visualization platform.
                    Learn how to visualize compilation pipelines across multiple programming languages.
                </p>
                
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search documentation...">
                </div>
            </section>
            
            <!-- Quick Links -->
            <div class="quick-links-grid">
                <a href="#getting-started" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Get Started</h3>
                    <p>Installation guide and quick start tutorial for beginners</p>
                </a>
                
                <a href="#languages" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>Languages</h3>
                    <p>Learn about supported languages and their visualizations</p>
                </a>
                
                <a href="#api" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>API Reference</h3>
                    <p>Complete API documentation for developers</p>
                </a>
                
                <a href="#educational-use" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Education Guide</h3>
                    <p>Using CompilerHub for teaching and learning</p>
                </a>
            </div>
            
            <!-- Getting Started Section -->
            <section class="section-card" id="getting-started">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h2>Getting Started</h2>
                </div>
                
                <h3 id="installation">Installation</h3>
                <p>CompilerHub can be installed locally or deployed to a web server. Follow these steps:</p>
                
                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Prerequisites</h4>
                    <ul>
                        <li>PHP 7.4+ with shell_exec enabled</li>
                        <li>Web server (Apache/Nginx) or built-in PHP server</li>
                        <li>Modern browser with WebGL support</li>
                        <li>Compilers for your target languages (GCC, Java, Go, etc.)</li>
                    </ul>
                </div>
                
                <h4>Step 1: Clone the Repository</h4>
                <div class="code-block">
                    <div class="code-header">
                        <div class="code-language">
                            <i class="fas fa-terminal"></i> Terminal
                        </div>
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    </div>
                    <div class="code-content">
                        <pre><code class="language-bash">git clone https://github.com/Agabaofficial/compiler-visualizer-hub.git
cd compiler-visualizer-hub</code></pre>
                    </div>
                </div>
                
                <h4>Step 2: Set Permissions</h4>
                <div class="code-block">
                    <div class="code-header">
                        <div class="code-language">
                            <i class="fas fa-terminal"></i> Terminal
                        </div>
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    </div>
                    <div class="code-content">
                        <pre><code class="language-bash"># Create temporary directory and set permissions
mkdir -p tmp
chmod 777 tmp/</code></pre>
                    </div>
                </div>
                
                <h4>Step 3: Start the Server</h4>
                <div class="code-block">
                    <div class="code-header">
                        <div class="code-language">
                            <i class="fas fa-server"></i> PHP
                        </div>
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    </div>
                    <div class="code-content">
                        <pre><code class="language-bash"># Using PHP built-in server
php -S localhost:8000

# Or using Apache/Nginx
# Copy files to your web server directory</code></pre>
                    </div>
                </div>
                
                <h3 id="quick-start">Quick Start Tutorial</h3>
                <p>Follow this tutorial to visualize your first compilation:</p>
                
                <ol>
                    <li><strong>Open CompilerHub</strong> in your browser at <code>http://localhost:8000</code></li>
                    <li><strong>Choose a language</strong> from the Languages section</li>
                    <li><strong>Write or paste code</strong> in the editor</li>
                    <li><strong>Click "Compile & Visualize"</strong> to start the pipeline</li>
                    <li><strong>Explore</strong> the 3D visualization and step through stages</li>
                </ol>
                
                <div class="success-box">
                    <h4><i class="fas fa-check-circle"></i> Quick Tip</h4>
                    <p>Try the example code provided in each language visualizer to see the complete compilation pipeline in action.</p>
                </div>
            </section>
            
            <!-- Languages Section -->
            <section class="section-card" id="languages">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h2>Supported Languages</h2>
                </div>
                
                <p>CompilerHub supports visualization for multiple programming languages, each with unique compilation characteristics.</p>
                
                <h3 id="java-visualizer">Java Visualizer</h3>
                <p>Java compilation involves multiple stages from source code to JVM bytecode.</p>
                
                <div class="info-box">
                    <h4><i class="fab fa-java"></i> Java Features</h4>
                    <ul>
                        <li>JVM bytecode generation visualization</li>
                        <li>Class loading and linking process</li>
                        <li>Garbage collection visualization</li>
                        <li>Just-In-Time (JIT) compilation</li>
                        <li>Interface and polymorphism handling</li>
                    </ul>
                </div>
                
                <h4>Example Java Code</h4>
                <div class="code-block">
                    <div class="code-header">
                        <div class="code-language">
                            <i class="fab fa-java"></i> Java
                        </div>
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    </div>
                    <div class="code-content">
                        <pre><code class="language-java">public class Main {
    public static void main(String[] args) {
        System.out.println("Hello, CompilerHub!");
        
        // Demonstrate inheritance
        Animal animal = new Dog();
        animal.makeSound();
    }
}

abstract class Animal {
    abstract void makeSound();
}

class Dog extends Animal {
    void makeSound() {
        System.out.println("Woof!");
    }
}</code></pre>
                    </div>
                </div>
                
                <h3 id="cpp-visualizer">C++ Visualizer</h3>
                <p>C++ compilation includes template instantiation and complex linking processes.</p>
                
                <h3 id="c-visualizer">C Visualizer</h3>
                <p>Direct compilation from C to assembly with memory layout visualization.</p>
                
                <h3 id="swift-visualizer">Swift Visualizer</h3>
                <p>Swift Intermediate Language (SIL) visualization with ARC optimization.</p>
                
                <h3 id="brainfuck-visualizer">Brainfuck Visualizer</h3>
                <p>Minimalist language with tape memory and pointer visualization.</p>
                
                <h3 id="go-visualizer">Go Visualizer</h3>
                <p>Fast compilation with goroutine scheduling and interface table visualization.</p>
                
                <div class="warning-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> Note</h4>
                    <p>Some language visualizers require additional compilers to be installed on your system. Check the specific requirements for each language.</p>
                </div>
            </section>
            
            <!-- API Reference -->
            <section class="section-card" id="api">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h2>API Reference</h2>
                </div>
                
                <h3 id="api-overview">API Overview</h3>
                <p>CompilerHub provides RESTful API endpoints for programmatic access to compilation and visualization features.</p>
                
                <div class="api-endpoint" id="compilation-api">
                    <div>
                        <span class="endpoint-method">POST</span>
                        <span class="endpoint-path">/api/compile.php</span>
                    </div>
                    <p class="endpoint-description">Compile source code and generate visualization data.</p>
                    
                    <div class="endpoint-params">
                        <table class="param-table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>language</code></td>
                                    <td>String</td>
                                    <td>Yes</td>
                                    <td>Programming language (java, cpp, c, swift, brainfuck, go)</td>
                                </tr>
                                <tr>
                                    <td><code>code</code></td>
                                    <td>String</td>
                                    <td>Yes</td>
                                    <td>Source code to compile</td>
                                </tr>
                                <tr>
                                    <td><code>optimization</code></td>
                                    <td>String</td>
                                    <td>No</td>
                                    <td>Optimization level (O0, O1, O2, O3)</td>
                                </tr>
                                <tr>
                                    <td><code>debug</code></td>
                                    <td>Boolean</td>
                                    <td>No</td>
                                    <td>Include debug information</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4>Example Request</h4>
                    <div class="code-block">
                        <div class="code-header">
                            <div class="code-language">
                                <i class="fas fa-code"></i> JSON
                            </div>
                            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        </div>
                        <div class="code-content">
                            <pre><code class="language-json">{
  "language": "java",
  "code": "public class Main { public static void main(String[] args) { System.out.println(\"Hello\"); } }",
  "optimization": "O2",
  "debug": true
}</code></pre>
                        </div>
                    </div>
                    
                    <h4>Example Response</h4>
                    <div class="code-block">
                        <div class="code-header">
                            <div class="code-language">
                                <i class="fas fa-code"></i> JSON
                            </div>
                            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        </div>
                        <div class="code-content">
                            <pre><code class="language-json">{
  "success": true,
  "data": {
    "ast": "...",
    "tokens": [...],
    "bytecode": "...",
    "visualization": {
      "nodes": [...],
      "edges": [...],
      "stages": [...]
    }
  },
  "execution_time": "1.24s"
}</code></pre>
                        </div>
                    </div>
                </div>
                
                <div class="api-endpoint" id="analysis-api">
                    <div>
                        <span class="endpoint-method">POST</span>
                        <span class="endpoint-path">/api/analyze.php</span>
                    </div>
                    <p class="endpoint-description">Analyze code without compilation (syntax checking, tokenization).</p>
                </div>
                
                <div class="api-endpoint" id="visualization-api">
                    <div>
                        <span class="endpoint-method">GET</span>
                        <span class="endpoint-path">/api/visualization.php?id={id}</span>
                    </div>
                    <p class="endpoint-description">Retrieve saved visualization data.</p>
                </div>
            </section>
            
            <!-- Educational Use -->
            <section class="section-card" id="educational-use">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>Educational Use</h2>
                </div>
                
                <h3>Using CompilerHub in Education</h3>
                <p>CompilerHub is designed as an educational tool for computer science students and instructors.</p>
                
                <h4>For Students</h4>
                <ul>
                    <li><strong>Interactive Learning</strong>: Visualize abstract compiler concepts</li>
                    <li><strong>Step-by-Step Exploration</strong>: See how code transforms through compilation stages</li>
                    <li><strong>Language Comparison</strong>: Compare compilation across different languages</li>
                    <li><strong>Debug Practice</strong>: Understand compilation errors visually</li>
                </ul>
                
                <h4>For Instructors</h4>
                <ul>
                    <li><strong>Class Demonstrations</strong>: Live compilation visualization during lectures</li>
                    <li><strong>Assignment Creation</strong>: Design exercises around visualization</li>
                    <li><strong>Grading Assistance</strong>: Visual understanding of student code</li>
                    <li><strong>Research Tool</strong>: Study compilation techniques and optimizations</li>
                </ul>
                
                <div class="info-box">
                    <h4><i class="fas fa-lightbulb"></i> Classroom Integration Ideas</h4>
                    <ul>
                        <li>Use CompilerHub to demonstrate lexical analysis in a compiler design course</li>
                        <li>Compare Java vs C++ compilation in a programming languages course</li>
                        <li>Show memory layout differences in a systems programming course</li>
                        <li>Demonstrate optimization effects in a performance engineering course</li>
                    </ul>
                </div>
            </section>
            
            <!-- Contributing -->
            <section class="section-card" id="contributing">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h2>Contributing to CompilerHub</h2>
                </div>
                
                <p>CompilerHub is an open-source project and welcomes contributions from the community.</p>
                
                <h3>How to Contribute</h3>
                <ol>
                    <li><strong>Fork the repository</strong> on GitHub</li>
                    <li><strong>Create a feature branch</strong> for your changes</li>
                    <li><strong>Make your changes</strong> following the code style</li>
                    <li><strong>Test your changes</strong> thoroughly</li>
                    <li><strong>Submit a pull request</strong> with a clear description</li>
                </ol>
                
                <h3>Areas for Contribution</h3>
                <div class="quick-links-grid">
                    <div class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h3>New Languages</h3>
                        <p>Add support for Python, Rust, JavaScript, or other languages</p>
                    </div>
                    
                    <div class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <h3>UI Improvements</h3>
                        <p>Enhance the 3D visualizations or user interface</p>
                    </div>
                    
                    <div class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h3>Bug Fixes</h3>
                        <p>Identify and fix issues in the codebase</p>
                    </div>
                    
                    <div class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>Documentation</h3>
                        <p>Improve guides, tutorials, and API documentation</p>
                    </div>
                </div>
                
                <h3>Development Setup</h3>
                <div class="code-block">
                    <div class="code-header">
                        <div class="code-language">
                            <i class="fas fa-code"></i> Development
                        </div>
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    </div>
                    <div class="code-content">
                        <pre><code class="language-bash"># Clone your fork
git clone https://github.com/YOUR_USERNAME/compiler-visualizer-hub.git

# Create development branch
git checkout -b feature/your-feature-name

# Make changes and test
# ...

# Commit and push
git add .
git commit -m "Add your feature description"
git push origin feature/your-feature-name</code></pre>
                    </div>
                </div>
            </section>
            
            <!-- FAQ Section -->
            <section class="section-card" id="faq">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h2>Frequently Asked Questions</h2>
                </div>
                
                <h3>General Questions</h3>
                
                <h4>Q: What programming languages are supported?</h4>
                <p>A: Currently supported languages are Java, C++, C, Swift, Brainfuck, and Go. More languages are being added regularly.</p>
                
                <h4>Q: Do I need to install compilers on my system?</h4>
                <p>A: Yes, to compile and visualize code in a specific language, you need the corresponding compiler (GCC for C/C++, Java JDK for Java, etc.).</p>
                
                <h4>Q: Is CompilerHub secure to use?</h4>
                <p>A: CompilerHub runs code in isolated environments with strict resource limits. However, always use it in a trusted environment and avoid running untrusted code.</p>
                
                <h4>Q: Can I use CompilerHub offline?</h4>
                <p>A: Yes, once installed locally, CompilerHub can work offline as long as your browser can access the local server.</p>
                
                <h3>Technical Questions</h3>
                
                <h4>Q: How does the 3D visualization work?</h4>
                <p>A: CompilerHub uses Three.js for WebGL-based 3D rendering. Compilation data is transformed into 3D nodes and edges for visualization.</p>
                
                <h4>Q: Can I extend CompilerHub with my own language?</h4>
                <p>A: Yes! CompilerHub is designed to be extensible. Check the "Adding New Languages" section in the documentation.</p>
                
                <h4>Q: What browsers are supported?</h4>
                <p>A: CompilerHub works on all modern browsers that support WebGL 2.0 (Chrome 79+, Firefox 70+, Safari 15+, Edge 79+).</p>
            </section>
            
            <!-- Contact Section -->
            <section class="section-card" id="contact">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h2>Contact & Support</h2>
                </div>
                
                <h3>Get in Touch</h3>
                <p>Need help or want to contribute? Here's how to reach us:</p>
                
                <div class="quick-links-grid">
                    <a href="https://github.com/Agabaofficial/compiler-visualizer-hub/issues" class="quick-link-card" target="_blank">
                        <div class="quick-link-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h3>Report Issues</h3>
                        <p>Found a bug? Report it on GitHub Issues</p>
                    </a>
                    
                    <a href="https://github.com/Agabaofficial/compiler-visualizer-hub/discussions" class="quick-link-card" target="_blank">
                        <div class="quick-link-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Community</h3>
                        <p>Join discussions on GitHub Discussions</p>
                    </a>
                    
                    <a href="mailto:contact@compilerhub.dev" class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email</h3>
                        <p>Contact us directly for questions</p>
                    </a>
                    
                    <a href="https://github.com/Agabaofficial" class="quick-link-card" target="_blank">
                        <div class="quick-link-icon">
                            <i class="fab fa-github"></i>
                        </div>
                        <h3>GitHub</h3>
                        <p>Follow development on GitHub</p>
                    </a>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>CompilerHub</h3>
                    <p style="color: var(--gray); line-height: 1.8; margin-top: 15px;">
                        Advanced compiler visualization platform for educational and research purposes.
                        Interactive 3D visualizations of programming language compilation pipelines.
                    </p>
                </div>
                
                <div class="footer-column">
                    <h3>Documentation</h3>
                    <ul class="footer-links">
                        <li><a href="#getting-started">Getting Started</a></li>
                        <li><a href="#languages">Languages</a></li>
                        <li><a href="#api">API Reference</a></li>
                        <li><a href="#educational-use">Education Guide</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="https://github.com/Agabaofficial/compiler-visualizer-hub">GitHub</a></li>
                        <li><a href="#contributing">Contributing</a></li>
                        <li><a href="#license">License</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2024 CompilerHub Visualizer Platform. Final Year Project - Computer Science Department.</p>
                <p class="authors">Developed by AGABA OLIVIER & IRADI ARINDA</p>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </a>
    
    <script>
        // Initialize Prism.js
        Prism.highlightAll();
        
        // Copy code functionality
        function copyCode(button) {
            const codeBlock = button.closest('.code-block');
            const codeContent = codeBlock.querySelector('.code-content pre code').textContent;
            
            navigator.clipboard.writeText(codeContent).then(() => {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.style.background = 'var(--primary)';
                button.style.color = 'var(--dark)';
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.background = '';
                    button.style.color = '';
                }, 2000);
            });
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                if(this.getAttribute('href') === '#') return;
                
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Update active link in sidebar
                    document.querySelectorAll('.sidebar-links a').forEach(link => {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            });
        });
        
        // Back to top button
        const backToTop = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        // Update active sidebar link based on scroll
        const sections = document.querySelectorAll('.section-card');
        const navLinks = document.querySelectorAll('.sidebar-links a');
        
        window.addEventListener('scroll', () => {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (scrollY >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
        
        // Search functionality
        const searchInput = document.querySelector('.search-input');
        const sectionCards = document.querySelectorAll('.section-card');
        
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            
            sectionCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const isVisible = text.includes(searchTerm);
                
                card.style.display = isVisible ? 'block' : 'none';
                
                // Also show parent sections if child content matches
                if (isVisible) {
                    let parent = card;
                    while (parent = parent.parentElement) {
                        if (parent.classList && parent.classList.contains('section-card')) {
                            parent.style.display = 'block';
                        }
                    }
                }
            });
        });
        
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && 
                !sidebar.contains(e.target) && 
                e.target !== mobileMenuBtn) {
                sidebar.classList.remove('mobile-open');
            }
        });
        
        // Add CSS for mobile sidebar
        const style = document.createElement('style');
        style.textContent = `
            @media (max-width: 992px) {
                .docs-sidebar {
                    display: none;
                    position: fixed;
                    top: 80px;
                    left: 0;
                    width: 100%;
                    height: calc(100vh - 80px);
                    z-index: 1000;
                    background: rgba(10, 25, 47, 0.98);
                    backdrop-filter: blur(20px);
                }
                
                .docs-sidebar.mobile-open {
                    display: block;
                }
                
                .mobile-menu-btn {
                    display: block !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>