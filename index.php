<?php
// index.php - Modern Compiler Visualizer Hub
// Turn off error display but log errors
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'hub_errors.log');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚡ CompilerHub | Advanced Compiler Visualization Platform</title>
    
    <!-- Three.js for 3D background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <!-- Three.js Effects -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/effects/OutlineEffect.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/EffectComposer.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/RenderPass.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/UnrealBloomPass.js"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500&family=Poppins:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- AOS for scroll animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Typed.js for typing effect -->
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
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
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 100%);
            color: var(--light);
            overflow-x: hidden;
            min-height: 100vh;
            line-height: 1.6;
        }
        
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
        
        #canvas3d {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            opacity: 0.4;
        }
        
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
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
            animation: pulseGlow 2s infinite;
        }
        
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px var(--primary); }
            50% { box-shadow: 0 0 30px var(--primary), 0 0 40px var(--secondary); }
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
        
        .logo-badge {
            position: absolute;
            top: -8px;
            right: -30px;
            background: var(--accent);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
            transform: rotate(15deg);
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
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .github-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
            z-index: -1;
        }
        
        .github-btn:hover::before {
            left: 100%;
        }
        
        .github-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--neon-glow);
        }
        
        /* Hero Section */
        .hero {
            padding: 200px 0 150px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0, 255, 157, 0.1) 0%, transparent 70%);
            filter: blur(100px);
            z-index: -1;
        }
        
        .hero-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 4.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.1;
            text-shadow: 0 0 30px rgba(0, 255, 157, 0.3);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: var(--gray);
            max-width: 800px;
            margin: 0 auto 50px;
            line-height: 1.8;
        }
        
        .hero-typed {
            font-size: 1.5rem;
            color: var(--primary);
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 40px;
            min-height: 60px;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 80px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            position: relative;
            padding: 20px;
            background: rgba(17, 34, 64, 0.3);
            border-radius: 15px;
            border: 1px solid rgba(0, 255, 157, 0.1);
            min-width: 180px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: var(--neon-glow);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
        }
        
        /* Animated Workflow Section */
        .workflow-section {
            padding: 150px 0;
            position: relative;
            overflow: hidden;
        }
        
        .workflow-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 60px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .workflow-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary);
            text-shadow: 0 0 20px rgba(0, 255, 157, 0.5);
        }
        
        .workflow-subtitle {
            text-align: center;
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 60px;
        }
        
        .workflow-stages {
            display: flex;
            justify-content: space-between;
            width: 100%;
            position: relative;
            margin-bottom: 60px;
        }
        
        .workflow-stages::before {
            content: '';
            position: absolute;
            top: 40px;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
            z-index: 1;
        }
        
        .workflow-stage {
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .stage-icon {
            width: 80px;
            height: 80px;
            background: var(--dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 4px solid var(--primary);
            color: var(--primary);
            font-size: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .stage-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(0, 255, 157, 0.1), transparent);
            animation: rotate 4s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .stage-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .stage-desc {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Code Editor Demo */
        .code-demo {
            width: 100%;
            background: rgba(10, 25, 47, 0.8);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 157, 0.2);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }
        
        .demo-header {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .demo-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .demo-controls {
            display: flex;
            gap: 10px;
        }
        
        .control-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(0, 255, 157, 0.1);
            border: 1px solid rgba(0, 255, 157, 0.2);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .control-btn:hover {
            background: rgba(0, 255, 157, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.2);
        }
        
        .code-container {
            padding: 30px;
            position: relative;
            min-height: 400px;
        }
        
        .code-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            line-height: 1.6;
            color: var(--light);
            white-space: pre;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(0, 255, 157, 0.1);
            min-height: 300px;
        }
        
        .code-line {
            display: block;
            margin-bottom: 5px;
        }
        
        .code-line .token.keyword { color: #ff6b6b; }
        .code-line .token.type { color: #64ffda; }
        .code-line .token.string { color: #feca57; }
        .code-line .token.comment { color: #8892b0; }
        .code-line .token.function { color: #6c63ff; }
        .code-line .token.number { color: #00d9a6; }
        
        .cursor {
            display: inline-block;
            width: 8px;
            height: 20px;
            background: var(--primary);
            margin-left: 2px;
            vertical-align: middle;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        
        .compile-btn-container {
            text-align: center;
            margin-top: 40px;
        }
        
        .compile-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
            padding: 15px 40px;
            border-radius: 10px;
            border: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .compile-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--neon-glow);
        }
        
        .compile-btn:active {
            transform: translateY(-1px);
        }
        
        /* Visualization Demo */
        .visualization-demo {
            width: 100%;
            height: 500px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            border: 1px solid rgba(0, 255, 157, 0.2);
            position: relative;
            overflow: hidden;
            display: none;
        }
        
        .viz-canvas {
            width: 100%;
            height: 100%;
        }
        
        /* Languages Grid */
        .languages-section {
            padding: 150px 0;
            position: relative;
        }
        
        .languages-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 20%, rgba(108, 99, 255, 0.1) 0%, transparent 50%);
            z-index: -1;
        }
        
        .section-title {
            text-align: center;
            font-family: 'Orbitron', sans-serif;
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: var(--primary);
            text-shadow: 0 0 20px rgba(0, 255, 157, 0.5);
        }
        
        .section-subtitle {
            text-align: center;
            color: var(--gray);
            max-width: 800px;
            margin: 0 auto 80px;
            font-size: 1.2rem;
            line-height: 1.8;
        }
        
        .languages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 40px;
            margin-bottom: 100px;
        }
        
        .language-card {
            background: linear-gradient(145deg, rgba(17, 34, 64, 0.8), rgba(10, 25, 47, 0.8));
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s;
            border: 1px solid rgba(0, 255, 157, 0.1);
            backdrop-filter: blur(10px);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }
        
        .language-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(0, 255, 157, 0.05), transparent);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s;
        }
        
        .language-card:hover {
            transform: translateY(-15px) rotateX(5deg);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), var(--neon-glow);
            border-color: var(--primary);
        }
        
        .language-card:hover::before {
            opacity: 1;
        }
        
        .card-header {
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .language-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .language-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: inherit;
            border-radius: inherit;
            filter: blur(10px);
            opacity: 0.6;
            z-index: -1;
        }
        
        .language-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--light);
            font-family: 'Orbitron', sans-serif;
        }
        
        .language-status {
            margin-left: auto;
            padding: 5px 15px;
            background: rgba(0, 255, 157, 0.1);
            color: var(--primary);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: 'Orbitron', sans-serif;
        }
        
        .card-body {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .language-description {
            color: var(--gray);
            line-height: 1.8;
            margin-bottom: 25px;
            flex-grow: 1;
        }
        
        .language-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            border: 1px solid rgba(0, 255, 157, 0.1);
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .language-link {
            display: block;
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            font-family: 'Orbitron', sans-serif;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .language-link:hover {
            transform: translateY(-3px);
            box-shadow: var(--neon-glow);
        }
        
        /* Features Visualization */
        .features-visualization {
            padding: 150px 0;
            background: linear-gradient(135deg, rgba(10, 25, 47, 0.9), rgba(17, 34, 64, 0.9));
            position: relative;
            overflow: hidden;
        }
        
        .features-visualization::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(108, 99, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            position: relative;
            z-index: 1;
        }
        
        .feature-item {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            border: 1px solid rgba(0, 255, 157, 0.1);
            transition: all 0.4s;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .feature-item:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: var(--neon-glow);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 35px;
            color: var(--dark);
            position: relative;
            overflow: hidden;
        }
        
        .feature-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 3s linear infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--light);
            font-family: 'Orbitron', sans-serif;
        }
        
        .feature-description {
            color: var(--gray);
            line-height: 1.8;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 150px 0;
            text-align: center;
            position: relative;
        }
        
        .cta-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .cta-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 4rem;
            margin-bottom: 30px;
            color: var(--primary);
            text-shadow: 0 0 30px rgba(0, 255, 157, 0.5);
        }
        
        .cta-subtitle {
            font-size: 1.3rem;
            color: var(--gray);
            margin-bottom: 60px;
            line-height: 1.8;
        }
        
        .cta-buttons {
            display: flex;
            gap: 25px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 18px 35px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
        }
        
        .btn-secondary {
            background: rgba(0, 255, 157, 0.1);
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn:hover {
            transform: translateY(-5px);
            box-shadow: var(--neon-glow);
        }
        
        /* Footer */
        .footer {
            background: rgba(10, 25, 47, 0.98);
            padding: 80px 0 40px;
            border-top: 1px solid rgba(0, 255, 157, 0.1);
            position: relative;
            overflow: hidden;
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
        
        /* Language-specific colors */
        .java { background: linear-gradient(135deg, #007396, #f89820); }
        .cplusplus { background: linear-gradient(135deg, #00599C, #004482); }
        .c { background: linear-gradient(135deg, #A8B9CC, #555555); }
        .swift { background: linear-gradient(135deg, #FA7343, #F05138); }
        .brainfuck { background: linear-gradient(135deg, #2C3E50, #34495E); }
        .go { background: linear-gradient(135deg, #00ADD8, #00B4A0); }
        
        /* Mobile Navigation */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.8rem;
            cursor: pointer;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3.5rem;
            }
            
            .section-title {
                font-size: 3rem;
            }
            
            .languages-grid {
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            }
        }
        
        @media (max-width: 992px) {
            .nav-links, .github-btn {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero {
                padding: 180px 0 100px;
            }
            
            .hero-title {
                font-size: 3rem;
            }
            
            .workflow-stages {
                flex-wrap: wrap;
                gap: 40px;
                justify-content: center;
            }
            
            .workflow-stages::before {
                display: none;
            }
            
            .cta-title {
                font-size: 3rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 350px;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .stat-item {
                min-width: 150px;
                padding: 15px;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
            
            .languages-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .stat-item {
                min-width: 100%;
            }
            
            .code-display {
                font-size: 12px;
                padding: 15px;
            }
            
            .language-card {
                margin: 0 10px;
            }
        }
        
        /* Scroll animations */
        [data-aos] {
            pointer-events: none;
        }
        
        .aos-animate {
            pointer-events: auto;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(10, 25, 47, 0.8);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            border: 2px solid var(--dark);
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }
        
        /* Mobile menu */
        .mobile-nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 300px;
            height: 100%;
            background: rgba(10, 25, 47, 0.98);
            backdrop-filter: blur(20px);
            z-index: 1001;
            transition: right 0.3s;
            padding: 100px 30px 30px;
            display: flex;
            flex-direction: column;
            gap: 25px;
            border-left: 1px solid rgba(0, 255, 157, 0.1);
        }
        
        .mobile-nav.active {
            right: 0;
        }
        
        .mobile-nav-close {
            position: absolute;
            top: 25px;
            right: 25px;
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.8rem;
            cursor: pointer;
        }
        
        .mobile-nav-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .mobile-nav-links a {
            color: var(--gray);
            text-decoration: none;
            font-size: 1.2rem;
            transition: color 0.3s;
            display: block;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
            font-family: 'Orbitron', sans-serif;
        }
        
        .mobile-nav-links a:hover {
            color: var(--primary);
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            display: none;
            backdrop-filter: blur(5px);
        }
        
        .overlay.active {
            display: block;
        }
        
        /* Terminal-style typing cursor */
        .typing-cursor {
            display: inline-block;
            width: 3px;
            height: 1em;
            background-color: var(--primary);
            margin-left: 2px;
            vertical-align: middle;
            animation: blink 1s infinite;
        }
        
        /* Floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: var(--primary);
            pointer-events: none;
        }
        
        /* Glitch effect */
        .glitch {
            position: relative;
        }
        
        .glitch::before,
        .glitch::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .glitch::before {
            left: 2px;
            text-shadow: -2px 0 #ff00ff;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim 5s infinite linear alternate-reverse;
        }
        
        .glitch::after {
            left: -2px;
            text-shadow: -2px 0 #00ffff;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim2 5s infinite linear alternate-reverse;
        }
        
        @keyframes glitch-anim {
            0% { clip: rect(42px, 9999px, 44px, 0); }
            5% { clip: rect(12px, 9999px, 59px, 0); }
            10% { clip: rect(48px, 9999px, 29px, 0); }
            15% { clip: rect(42px, 9999px, 73px, 0); }
            20% { clip: rect(63px, 9999px, 27px, 0); }
            25% { clip: rect(34px, 9999px, 55px, 0); }
            30% { clip: rect(86px, 9999px, 73px, 0); }
            35% { clip: rect(20px, 9999px, 20px, 0); }
            40% { clip: rect(26px, 9999px, 60px, 0); }
            45% { clip: rect(25px, 9999px, 66px, 0); }
            50% { clip: rect(57px, 9999px, 98px, 0); }
            55% { clip: rect(5px, 9999px, 46px, 0); }
            60% { clip: rect(82px, 9999px, 31px, 0); }
            65% { clip: rect(54px, 9999px, 27px, 0); }
            70% { clip: rect(28px, 9999px, 99px, 0); }
            75% { clip: rect(45px, 9999px, 69px, 0); }
            80% { clip: rect(23px, 9999px, 85px, 0); }
            85% { clip: rect(54px, 9999px, 84px, 0); }
            90% { clip: rect(45px, 9999px, 47px, 0); }
            95% { clip: rect(37px, 9999px, 20px, 0); }
            100% { clip: rect(4px, 9999px, 91px, 0); }
        }
        
        @keyframes glitch-anim2 {
            0% { clip: rect(65px, 9999px, 100px, 0); }
            5% { clip: rect(52px, 9999px, 74px, 0); }
            10% { clip: rect(79px, 9999px, 85px, 0); }
            15% { clip: rect(75px, 9999px, 5px, 0); }
            20% { clip: rect(67px, 9999px, 61px, 0); }
            25% { clip: rect(14px, 9999px, 79px, 0); }
            30% { clip: rect(1px, 9999px, 66px, 0); }
            35% { clip: rect(86px, 9999px, 30px, 0); }
            40% { clip: rect(23px, 9999px, 98px, 0); }
            45% { clip: rect(85px, 9999px, 72px, 0); }
            50% { clip: rect(71px, 9999px, 75px, 0); }
            55% { clip: rect(2px, 9999px, 48px, 0); }
            60% { clip: rect(30px, 9999px, 16px, 0); }
            65% { clip: rect(59px, 9999px, 50px, 0); }
            70% { clip: rect(41px, 9999px, 62px, 0); }
            75% { clip: rect(2px, 9999px, 82px, 0); }
            80% { clip: rect(47px, 9999px, 73px, 0); }
            85% { clip: rect(3px, 9999px, 27px, 0); }
            90% { clip: rect(40px, 9999px, 86px, 0); }
            95% { clip: rect(45px, 9999px, 72px, 0); }
            100% { clip: rect(23px, 9999px, 49px, 0); }
        }
    </style>
</head>
<body>
    <!-- Tech Grid Background -->
    <div class="tech-grid"></div>
    
    <!-- 3D Background Canvas -->
    <div id="canvas3d"></div>
    
    <!-- Particles Container -->
    <div class="particles-container" id="particlesContainer"></div>
    
    <!-- Overlay for mobile menu -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <button class="mobile-nav-close" id="mobileNavClose">
            <i class="fas fa-times"></i>
        </button>
        <ul class="mobile-nav-links">
            <li><a href="#workflow">Live Workflow</a></li>
            <li><a href="#languages">Languages</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#cta">Get Started</a></li>
        </ul>
        <a href="https://github.com/Agabaofficial/compiler-visualizer-hub" class="github-btn" target="_blank">
            <i class="fab fa-github"></i> Source Code
        </a>
    </div>
    
    <!-- Header -->
    <header class="header">
        <div class="container nav-container">
            <a href="#" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="logo-text">CompilerHub</div>
                <div class="logo-badge">v2.0</div>
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="#workflow">Live Workflow</a></li>
                    <li><a href="#languages">Languages</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#cta">Get Started</a></li>
                </ul>
            </nav>
            
            <a href="https://github.com/Agabaofficial/compiler-visualizer-hub" class="github-btn" target="_blank">
                <i class="fab fa-github"></i> <span class="github-text">View Code</span>
            </a>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero" data-aos="fade-up">
        <div class="container">
            <h1 class="hero-title glitch" data-text="COMPILER VISUALIZER HUB">
                <span>COMPILER VISUALIZER HUB</span>
            </h1>
            <p class="hero-subtitle">
                Advanced 3D visualization platform for understanding compiler internals across multiple programming languages.
                Real-time pipeline simulation with interactive node exploration.
            </p>
            
            <div class="hero-typed" id="typed-text"></div>
            
            <div class="hero-stats">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                    <span class="stat-number" id="statLanguages">6</span>
                    <span class="stat-label">Programming Languages</span>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <span class="stat-number" id="statVisualizations">24+</span>
                    <span class="stat-label">Visualization Modes</span>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <span class="stat-number" id="statUsers">1.2k+</span>
                    <span class="stat-label">Active Users</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Animated Workflow Section -->
    <section id="workflow" class="workflow-section">
        <div class="container">
            <h2 class="workflow-title" data-aos="fade-up">Live Compilation Workflow</h2>
            <p class="workflow-subtitle" data-aos="fade-up" data-aos-delay="100">
                Watch a C program go through the entire compilation pipeline in real-time. 
                From source code to executable, visualized step by step.
            </p>
            
            <div class="workflow-stages" data-aos="fade-up" data-aos-delay="200">
                <div class="workflow-stage">
                    <div class="stage-icon">
                        <i class="fas fa-keyboard"></i>
                    </div>
                    <h3 class="stage-title">Source Code</h3>
                    <p class="stage-desc">Typing...</p>
                </div>
                
                <div class="workflow-stage">
                    <div class="stage-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="stage-title">Lexical Analysis</h3>
                    <p class="stage-desc">Tokenizing...</p>
                </div>
                
                <div class="workflow-stage">
                    <div class="stage-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="stage-title">Syntax Analysis</h3>
                    <p class="stage-desc">Building AST...</p>
                </div>
                
                <div class="workflow-stage">
                    <div class="stage-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="stage-title">Semantic Analysis</h3>
                    <p class="stage-desc">Type checking...</p>
                </div>
                
                <div class="workflow-stage">
                    <div class="stage-icon">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <h3 class="stage-title">Code Generation</h3>
                    <p class="stage-desc">Generating IR...</p>
                </div>
                
                <div class="workflow-stage">
                    <div class="stage-icon">
                        <i class="fas fa-file-code"></i>
                    </div>
                    <h3 class="stage-title">Assembly</h3>
                    <p class="stage-desc">Final output...</p>
                </div>
            </div>
            
            <div class="workflow-container">
                <!-- Code Editor Demo -->
                <div class="code-demo" id="codeDemo" data-aos="fade-up" data-aos-delay="300">
                    <div class="demo-header">
                        <div class="demo-title">
                            <i class="fas fa-code"></i> main.c
                        </div>
                        <div class="demo-controls">
                            <button class="control-btn" id="playBtn">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="control-btn" id="resetBtn">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button class="control-btn" id="fullscreenBtn">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="code-container">
                        <div class="code-display" id="codeDisplay">
                            <div class="code-line"><span class="token comment">// C Program to Demonstrate Compilation Pipeline</span></div>
                            <div class="code-line"><span class="token comment">// Type: int main() {</span></div>
                            <div class="code-line"><span class="token keyword">int</span> <span class="token function">main</span>() {</div>
                            <div class="code-line">    <span class="token keyword">int</span> a = <span class="token number">10</span>;</div>
                            <div class="code-line">    <span class="token keyword">int</span> b = <span class="token number">20</span>;</div>
                            <div class="code-line">    <span class="token keyword">int</span> sum = a + b;</div>
                            <div class="code-line">    </div>
                            <div class="code-line">    <span class="token keyword">if</span> (sum > <span class="token number">25</span>) {</div>
                            <div class="code-line">        <span class="token function">printf</span>(<span class="token string">"Sum is greater than 25\n"</span>);</div>
                            <div class="code-line">    } <span class="token keyword">else</span> {</div>
                            <div class="code-line">        <span class="token function">printf</span>(<span class="token string">"Sum is 25 or less\n"</span>);</div>
                            <div class="code-line">    }</div>
                            <div class="code-line">    </div>
                            <div class="code-line">    <span class="token keyword">return</span> <span class="token number">0</span>;</div>
                            <div class="code-line">}</div>
                        </div>
                        
                        <div class="compile-btn-container">
                            <button class="compile-btn" id="compileBtn">
                                <i class="fas fa-play-circle"></i> Compile & Visualize
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Visualization Demo -->
                <div class="visualization-demo" id="vizDemo" data-aos="fade-up" data-aos-delay="400">
                    <canvas class="viz-canvas" id="workflowCanvas"></canvas>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Languages Section -->
    <section id="languages" class="languages-section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Supported Languages</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Explore compiler internals across diverse programming paradigms. 
                Each language features unique visualization modes and pipeline stages.
            </p>
            
            <div class="languages-grid">
                <!-- Java Card -->
                <div class="language-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card-header">
                        <div class="language-icon java">
                            <i class="fab fa-java"></i>
                        </div>
                        <h3 class="language-title">Java</h3>
                        <div class="language-status">JVM</div>
                    </div>
                    <div class="card-body">
                        <p class="language-description">
                            Full JVM pipeline visualization including bytecode generation, 
                            class loading, garbage collection, and JIT compilation. 
                            Explore Java's write-once-run-anywhere architecture.
                        </p>
                        
                        <div class="language-stats">
                            <div class="stat">
                                <span class="stat-value">8</span>
                                <span class="stat-label">Stages</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">3D</span>
                                <span class="stat-label">JVM Heap</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">Live</span>
                                <span class="stat-label">GC View</span>
                            </div>
                        </div>
                        
                        <a href="java.php" class="language-link">
                            <i class="fas fa-rocket"></i> Launch Java Visualizer
                        </a>
                    </div>
                </div>
                
                <!-- C++ Card -->
                <div class="language-card" data-aos="zoom-in" data-aos-delay="150">
                    <div class="card-header">
                        <div class="language-icon cplusplus">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 class="language-title">C++</h3>
                        <div class="language-status">Native</div>
                    </div>
                    <div class="card-body">
                        <p class="language-description">
                            Complex template instantiation, header processing, 
                            and linker visualization. See how C++ compiles to 
                            efficient native code with multiple compilation units.
                        </p>
                        
                        <div class="language-stats">
                            <div class="stat">
                                <span class="stat-value">7</span>
                                <span class="stat-label">Stages</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">ASM</span>
                                <span class="stat-label">x86/ARM</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">Templates</span>
                                <span class="stat-label">Visual</span>
                            </div>
                        </div>
                        
                        <a href="c-plus.php" class="language-link">
                            <i class="fas fa-rocket"></i> Launch C++ Visualizer
                        </a>
                    </div>
                </div>
                
                <!-- C Card -->
                <div class="language-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="card-header">
                        <div class="language-icon c">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h3 class="language-title">C</h3>
                        <div class="language-status">System</div>
                    </div>
                    <div class="card-body">
                        <p class="language-description">
                            Direct compilation to assembly and machine code. 
                            Memory layout visualization, pointer operations, 
                            and preprocessor expansion. The foundation of systems programming.
                        </p>
                        
                        <div class="language-stats">
                            <div class="stat">
                                <span class="stat-value">6</span>
                                <span class="stat-label">Stages</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">Low</span>
                                <span class="stat-label">Level View</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">Memory</span>
                                <span class="stat-label">Layout</span>
                            </div>
                        </div>
                        
                        <a href="c.php" class="language-link">
                            <i class="fas fa-rocket"></i> Launch C Visualizer
                        </a>
                    </div>
                </div>
                
                <!-- Swift Card -->
                <div class="language-card" data-aos="zoom-in" data-aos-delay="250">
                    <div class="card-header">
                        <div class="language-icon swift">
                            <i class="fab fa-swift"></i>
                        </div>
                        <h3 class="language-title">Swift</h3>
                        <div class="language-status">Modern</div>
                    </div>
                    <div class="card-body">
                        <p class="language-description">
                            Swift Intermediate Language (SIL) visualization, 
                            ARC optimization, protocol witness tables, and 
                            generic specialization. Experience modern language features.
                        </p>
                        
                        <div class="language-stats">
                            <div class="stat">
                                <span class="stat-value">9</span>
                                <span class="stat-label">Stages</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">SIL</span>
                                <span class="stat-label">Visual</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">ARC</span>
                                <span class="stat-label">Live</span>
                            </div>
                        </div>
                        
                        <a href="swift.php" class="language-link">
                            <i class="fas fa-rocket"></i> Launch Swift Visualizer
                        </a>
                    </div>
                </div>
                
                <!-- Brainfuck Card -->
                <div class="language-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="card-header">
                        <div class="language-icon brainfuck">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="language-title">Brainfuck</h3>
                        <div class="language-status">Esoteric</div>
                    </div>
                    <div class="card-body">
                        <p class="language-description">
                            Minimalist language with tape memory visualization. 
                            Watch pointer movements, cell operations, and loop 
                            execution in real-time. The ultimate simplicity test.
                        </p>
                        
                        <div class="language-stats">
                            <div class="stat">
                                <span class="stat-value">3</span>
                                <span class="stat-label">Stages</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">Tape</span>
                                <span class="stat-label">Memory</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">8</span>
                                <span class="stat-label">Commands</span>
                            </div>
                        </div>
                        
                        <a href="brain-fuck.php" class="language-link">
                            <i class="fas fa-rocket"></i> Launch Brainfuck Visualizer
                        </a>
                    </div>
                </div>
                
                <!-- Go Card -->
                <div class="language-card" data-aos="zoom-in" data-aos-delay="350">
                    <div class="card-header">
                        <div class="language-icon go">
                            <img src="https://raw.githubusercontent.com/golang-samples/gopher-vector/master/gopher.png" 
                                 style="width: 32px; height: 32px; filter: brightness(0) invert(1);" 
                                 alt="Go Gopher">
                        </div>
                        <h3 class="language-title">Go</h3>
                        <div class="language-status">Concurrent</div>
                    </div>
                    <div class="card-body">
                        <p class="language-description">
                            Fast compilation pipeline, goroutine scheduling, 
                            channel operations, and interface tables. Visualize 
                            Go's unique concurrency model and efficient compilation.
                        </p>
                        
                        <div class="language-stats">
                            <div class="stat">
                                <span class="stat-value">7</span>
                                <span class="stat-label">Stages</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">Goroutines</span>
                                <span class="stat-label">Visual</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">Fast</span>
                                <span class="stat-label">Compile</span>
                            </div>
                        </div>
                        
                        <a href="go.php" class="language-link">
                            <i class="fas fa-rocket"></i> Launch Go Visualizer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Visualization -->
    <section id="features" class="features-visualization">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Advanced Features</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Cutting-edge visualization tools powered by Three.js and modern web technologies
            </p>
            
            <div class="feature-grid">
                <div class="feature-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <h3 class="feature-title">Interactive 3D</h3>
                    <p class="feature-description">
                        Rotate, zoom, and explore compiler internals in three dimensions with 
                        real-time WebGL rendering powered by Three.js
                    </p>
                </div>
                
                <div class="feature-item" data-aos="fade-up" data-aos-delay="150">
                    <div class="feature-icon">
                        <i class="fas fa-code-branch"></i>
                    </div>
                    <h3 class="feature-title">Pipeline View</h3>
                    <p class="feature-description">
                        Step through compilation stages with detailed explanations. 
                        Watch data flow between lexical, syntax, and semantic analysis
                    </p>
                </div>
                
                <div class="feature-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="feature-title">AST Explorer</h3>
                    <p class="feature-description">
                        Interactive Abstract Syntax Trees with node highlighting, 
                        zoom, and expand/collapse. Visualize parse tree structures
                    </p>
                </div>
                
                <div class="feature-item" data-aos="fade-up" data-aos-delay="250">
                    <div class="feature-icon">
                        <i class="fas fa-memory"></i>
                    </div>
                    <h3 class="feature-title">Memory Models</h3>
                    <p class="feature-description">
                        JVM heap, C stack frames, Swift ARC, and Brainfuck tape 
                        visualization. Watch memory allocation in real-time
                    </p>
                </div>
                
                <div class="feature-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-download"></i>
                    </div>
                    <h3 class="feature-title">Export System</h3>
                    <p class="feature-description">
                        Download generated ASTs, intermediate code, and 3D visualizations 
                        for offline study, presentations, and academic research
                    </p>
                </div>
                
                <div class="feature-item" data-aos="fade-up" data-aos-delay="350">
                    <div class="feature-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="feature-title">Educational</h3>
                    <p class="feature-description">
                        Perfect for computer science education. Used by universities 
                        worldwide for compiler design and programming language courses
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section id="cta" class="cta-section">
        <div class="container">
            <div class="cta-container">
                <h2 class="cta-title" data-aos="fade-up">Start Exploring Now</h2>
                <p class="cta-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Whether you're a student learning compiler design, a developer optimizing code,
                    or a researcher exploring language implementation, CompilerHub provides
                    unparalleled insights into programming language internals.
                </p>
                
                <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
                    <a href="#languages" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Explore All Languages
                    </a>
                    <a href="https://github.com/Agabaofficial/compiler-visualizer-hub" class="btn btn-secondary" target="_blank">
                        <i class="fab fa-github"></i> View Source Code
                    </a>
                </div>
            </div>
        </div>
    </section>
    
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
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#workflow">Live Workflow</a></li>
                        <li><a href="#languages">Languages</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#cta">Get Started</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Languages</h3>
                    <ul class="footer-links">
                        <li><a href="java.php">Java Visualizer</a></li>
                        <li><a href="c-plus.php">C++ Visualizer</a></li>
                        <li><a href="c.php">C Visualizer</a></li>
                        <li><a href="swift.php">Swift Visualizer</a></li>
                        <li><a href="brain-fuck.php">Brainfuck Visualizer</a></li>
                        <li><a href="go.php">Go Visualizer</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Connect</h3>
                    <ul class="footer-links">
                        <li><a href="https://github.com/Agabaofficial" target="_blank">
                            <i class="fab fa-github"></i> GitHub
                        </a></li>
                        <li><a href="mailto:contact@compilerhub.dev">
                            <i class="fas fa-envelope"></i> Email
                        </a></li>
                        <li><a href="#">
                            <i class="fas fa-book"></i> Documentation
                        </a></li>
                        <li><a href="#">
                            <i class="fas fa-code-branch"></i> Contribute
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2024 CompilerHub Visualizer Platform. Final Year Project - Computer Science Department.</p>
                <p class="authors">Developed by AGABA OLIVIER & IRADI ARINDA</p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1200,
            once: true,
            offset: 100,
            disable: 'mobile'
        });
        
        // Typed.js for hero section
        const typed = new Typed('#typed-text', {
            strings: [
                '> Initializing compiler visualization engine...',
                '> Loading 3D rendering pipeline...',
                '> Connecting to language compilers...',
                '> Ready for interactive exploration.'
            ],
            typeSpeed: 50,
            backSpeed: 30,
            loop: true,
            cursorChar: '_',
            smartBackspace: true
        });
        
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileNav = document.getElementById('mobileNav');
        const mobileNavClose = document.getElementById('mobileNavClose');
        const overlay = document.getElementById('overlay');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileNav.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        mobileNavClose.addEventListener('click', () => {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        overlay.addEventListener('click', () => {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Close mobile menu when clicking on a link
        document.querySelectorAll('.mobile-nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                mobileNav.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
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
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Animated counter for statistics
        function animateCounter(element, target, suffix = '', duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if(start >= target) {
                    element.textContent = target + suffix;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start) + suffix;
                }
            }, 16);
        }
        
        // Initialize counters when they come into view
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    const statLanguages = document.getElementById('statLanguages');
                    const statVisualizations = document.getElementById('statVisualizations');
                    const statUsers = document.getElementById('statUsers');
                    
                    animateCounter(statLanguages, 6);
                    animateCounter(statVisualizations, 24, '+');
                    animateCounter(statUsers, 1200, '+');
                    
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        const heroSection = document.querySelector('.hero');
        if(heroSection) {
            observer.observe(heroSection);
        }
        
        // Create floating particles
        function createParticles() {
            const container = document.getElementById('particlesContainer');
            const particleCount = 100;
            
            for(let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random properties
                const size = Math.random() * 4 + 1;
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;
                const opacity = Math.random() * 0.5 + 0.1;
                const color = Math.random() > 0.7 ? 'var(--secondary)' : 
                             Math.random() > 0.5 ? 'var(--accent)' : 'var(--primary)';
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${x}%`;
                particle.style.top = `${y}%`;
                particle.style.background = color;
                particle.style.opacity = opacity;
                particle.style.animation = `float ${duration}s ease-in-out ${delay}s infinite`;
                
                // Add floating animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes float {
                        0%, 100% { transform: translate(0, 0) rotate(0deg); }
                        25% { transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) rotate(90deg); }
                        50% { transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) rotate(180deg); }
                        75% { transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) rotate(270deg); }
                    }
                `;
                document.head.appendChild(style);
                
                container.appendChild(particle);
            }
        }
        
        // Workflow animation
        const codeDisplay = document.getElementById('codeDisplay');
        const compileBtn = document.getElementById('compileBtn');
        const codeDemo = document.getElementById('codeDemo');
        const vizDemo = document.getElementById('vizDemo');
        const stageDescriptions = document.querySelectorAll('.stage-desc');
        const stageIcons = document.querySelectorAll('.stage-icon');
        
        let isTyping = false;
        let currentLine = 0;
        const codeLines = [
            '// C Program to Demonstrate Compilation Pipeline',
            '#include <stdio.h>',
            '',
            'int main() {',
            '    int a = 10;',
            '    int b = 20;',
            '    int sum = a + b;',
            '    ',
            '    if (sum > 25) {',
            '        printf("Sum is greater than 25\\n");',
            '    } else {',
            '        printf("Sum is 25 or less\\n");',
            '    }',
            '    ',
            '    for (int i = 0; i < 3; i++) {',
            '        printf("Iteration %d\\n", i);',
            '    }',
            '    ',
            '    return 0;',
            '}'
        ];
        
        function typeCode() {
            if (isTyping) return;
            isTyping = true;
            currentLine = 0;
            codeDisplay.innerHTML = '';
            
            function typeLine() {
                if (currentLine < codeLines.length) {
                    const line = codeLines[currentLine];
                    const lineElement = document.createElement('div');
                    lineElement.className = 'code-line';
                    lineElement.innerHTML = highlightSyntax(line);
                    codeDisplay.appendChild(lineElement);
                    
                    // Add cursor to current line
                    const cursor = document.createElement('span');
                    cursor.className = 'cursor';
                    lineElement.appendChild(cursor);
                    
                    // Scroll to bottom
                    codeDisplay.scrollTop = codeDisplay.scrollHeight;
                    
                    // Simulate typing delay
                    setTimeout(() => {
                        cursor.remove();
                        currentLine++;
                        typeLine();
                    }, line.length * 20 + 200);
                } else {
                    isTyping = false;
                    // Enable compile button
                    compileBtn.style.animation = 'pulseGlow 1s infinite';
                    compileBtn.disabled = false;
                }
            }
            
            typeLine();
        }
        
        function highlightSyntax(line) {
            // Simple syntax highlighting
            return line
                .replace(/\b(int|return|if|else|for)\b/g, '<span class="token keyword">$1</span>')
                .replace(/\b(main|printf)\b/g, '<span class="token function">$1</span>')
                .replace(/\b(a|b|sum|i)\b/g, '<span class="token variable">$1</span>')
                .replace(/(\d+)/g, '<span class="token number">$1</span>')
                .replace(/(".*?")/g, '<span class="token string">$1</span>')
                .replace(/\/\/.*/g, '<span class="token comment">$&</span>');
        }
        
        // Start typing animation when page loads
        setTimeout(typeCode, 1000);
        
        // Compile button click handler
        compileBtn.addEventListener('click', function() {
            if (isTyping) return;
            
            // Reset button animation
            this.style.animation = '';
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-cog fa-spin"></i> Compiling...';
            
            // Animate through compilation stages
            const stages = [
                {name: 'Source Code', desc: 'Complete', icon: 'fas fa-check'},
                {name: 'Lexical Analysis', desc: 'Tokenizing...', icon: 'fas fa-code'},
                {name: 'Syntax Analysis', desc: 'Building AST...', icon: 'fas fa-project-diagram'},
                {name: 'Semantic Analysis', desc: 'Type checking...', icon: 'fas fa-brain'},
                {name: 'Code Generation', desc: 'Generating IR...', icon: 'fas fa-microchip'},
                {name: 'Assembly', desc: 'Final output...', icon: 'fas fa-file-code'}
            ];
            
            let stageIndex = 0;
            
            function animateStage() {
                if (stageIndex < stages.length) {
                    // Update stage description
                    if (stageIndex > 0) {
                        stageDescriptions[stageIndex - 1].textContent = 'Complete';
                        stageIcons[stageIndex - 1].style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
                    }
                    
                    if (stageIndex < stages.length) {
                        stageDescriptions[stageIndex].textContent = stages[stageIndex].desc;
                        stageIcons[stageIndex].innerHTML = `<i class="${stages[stageIndex].icon}"></i>`;
                        stageIcons[stageIndex].style.borderColor = 'var(--accent)';
                        stageIcons[stageIndex].style.boxShadow = '0 0 20px var(--accent)';
                    }
                    
                    stageIndex++;
                    setTimeout(animateStage, 800);
                } else {
                    // All stages complete
                    compileBtn.innerHTML = '<i class="fas fa-check"></i> Compilation Complete!';
                    compileBtn.style.background = 'linear-gradient(135deg, var(--secondary), var(--accent))';
                    
                    // Show visualization
                    setTimeout(() => {
                        codeDemo.style.display = 'none';
                        vizDemo.style.display = 'block';
                        initWorkflowVisualization();
                    }, 1000);
                }
            }
            
            animateStage();
        });
        
        // Three.js 3D Background
        function initThreeJS() {
            const canvas = document.getElementById('canvas3d');
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
            
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setClearColor(0x000000, 0);
            canvas.appendChild(renderer.domElement);
            
            // Add post-processing effects
            const composer = new THREE.EffectComposer(renderer);
            const renderPass = new THREE.RenderPass(scene, camera);
            composer.addPass(renderPass);
            
            const bloomPass = new THREE.UnrealBloomPass(
                new THREE.Vector2(window.innerWidth, window.innerHeight),
                0.5, // strength
                0.4, // radius
                0.85 // threshold
            );
            composer.addPass(bloomPass);
            
            // Create floating compiler nodes
            const nodes = [];
            const nodeColors = [
                0x00ff9d, // Java green
                0x00599C, // C++ blue
                0xA8B9CC, // C gray
                0xFA7343, // Swift orange
                0x2C3E50, // Brainfuck dark
                0x00ADD8  // Go blue
            ];
            
            const nodeGeometries = [
                new THREE.IcosahedronGeometry(2, 1), // Java
                new THREE.OctahedronGeometry(2, 1),  // C++
                new THREE.TetrahedronGeometry(2, 1), // C
                new THREE.DodecahedronGeometry(2, 0), // Swift
                new THREE.BoxGeometry(2, 2, 2),      // Brainfuck
                new THREE.SphereGeometry(2, 16, 16)  // Go
            ];
            
            for(let i = 0; i < 6; i++) {
                const material = new THREE.MeshPhongMaterial({
                    color: nodeColors[i],
                    emissive: nodeColors[i],
                    emissiveIntensity: 0.3,
                    transparent: true,
                    opacity: 0.7,
                    shininess: 100,
                    wireframe: false
                });
                
                const node = new THREE.Mesh(nodeGeometries[i], material);
                
                // Position in a helix
                const angle = (i / 6) * Math.PI * 2;
                const radius = 15;
                node.position.x = Math.cos(angle) * radius;
                node.position.z = Math.sin(angle) * radius;
                node.position.y = (i - 3) * 4;
                
                node.userData = {
                    originalY: node.position.y,
                    speed: 0.5 + Math.random() * 0.5,
                    angle: angle,
                    radius: radius
                };
                
                scene.add(node);
                nodes.push(node);
                
                // Add connections between nodes
                if (i > 0) {
                    const geometry = new THREE.CylinderGeometry(0.1, 0.1, 1, 8);
                    const connectionMaterial = new THREE.MeshBasicMaterial({
                        color: 0x00ff9d,
                        transparent: true,
                        opacity: 0.2
                    });
                    
                    const connection = new THREE.Mesh(geometry, connectionMaterial);
                    
                    const prevNode = nodes[i-1];
                    const midPoint = new THREE.Vector3().addVectors(node.position, prevNode.position).multiplyScalar(0.5);
                    connection.position.copy(midPoint);
                    
                    const distance = node.position.distanceTo(prevNode.position);
                    connection.scale.y = distance;
                    
                    connection.lookAt(prevNode.position);
                    connection.rotateX(Math.PI / 2);
                    
                    scene.add(connection);
                }
            }
            
            // Add central processor node
            const processorGeometry = new THREE.TorusKnotGeometry(3, 1, 100, 16);
            const processorMaterial = new THREE.MeshPhongMaterial({
                color: 0x6c63ff,
                emissive: 0x6c63ff,
                emissiveIntensity: 0.5,
                transparent: true,
                opacity: 0.8
            });
            const processor = new THREE.Mesh(processorGeometry, processorMaterial);
            scene.add(processor);
            
            // Add lights
            const ambientLight = new THREE.AmbientLight(0x404040, 0.5);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 20, 15);
            scene.add(directionalLight);
            
            const pointLight = new THREE.PointLight(0x00ff9d, 1, 100);
            pointLight.position.set(0, 0, 0);
            scene.add(pointLight);
            
            // Camera position
            camera.position.set(0, 10, 40);
            
            // Animation loop
            let time = 0;
            function animate() {
                requestAnimationFrame(animate);
                time += 0.01;
                
                // Animate nodes
                nodes.forEach((node, index) => {
                    node.userData.angle += 0.002 * node.userData.speed;
                    node.position.x = Math.cos(node.userData.angle + time) * node.userData.radius;
                    node.position.z = Math.sin(node.userData.angle + time) * node.userData.radius;
                    node.position.y = node.userData.originalY + Math.sin(time * 0.5 + index) * 3;
                    
                    node.rotation.x += 0.01 * node.userData.speed;
                    node.rotation.y += 0.01 * node.userData.speed;
                });
                
                // Animate processor
                processor.rotation.x += 0.01;
                processor.rotation.y += 0.01;
                processor.scale.setScalar(1 + Math.sin(time) * 0.1);
                
                // Move point light
                pointLight.position.x = Math.sin(time * 0.5) * 20;
                pointLight.position.z = Math.cos(time * 0.5) * 20;
                
                // Rotate camera
                camera.position.x = Math.sin(time * 0.05) * 40;
                camera.position.z = Math.cos(time * 0.05) * 40;
                camera.lookAt(0, 0, 0);
                
                composer.render();
            }
            
            // Handle window resize
            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
                composer.setSize(window.innerWidth, window.innerHeight);
            });
            
            animate();
        }
        
        // Workflow visualization
        function initWorkflowVisualization() {
            const canvas = document.getElementById('workflowCanvas');
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({ antialias: true });
            
            renderer.setSize(canvas.clientWidth, canvas.clientHeight);
            renderer.setClearColor(0x0a192f, 1);
            canvas.appendChild(renderer.domElement);
            
            // Create compilation pipeline visualization
            const pipelineStages = ['Lexical', 'Syntax', 'Semantic', 'IR', 'Optimize', 'CodeGen'];
            const stageNodes = [];
            
            pipelineStages.forEach((stage, i) => {
                const geometry = new THREE.BoxGeometry(4, 4, 4);
                const material = new THREE.MeshPhongMaterial({
                    color: 0x00ff9d,
                    emissive: 0x00ff9d,
                    emissiveIntensity: 0.2,
                    transparent: true,
                    opacity: 0.8
                });
                
                const node = new THREE.Mesh(geometry, material);
                node.position.x = (i - (pipelineStages.length - 1) / 2) * 8;
                node.userData.stage = stage;
                
                scene.add(node);
                stageNodes.push(node);
                
                // Add stage label
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = 256;
                canvas.height = 128;
                
                context.fillStyle = 'rgba(10, 25, 47, 0.9)';
                context.fillRect(0, 0, canvas.width, canvas.height);
                
                context.strokeStyle = '#00ff9d';
                context.lineWidth = 2;
                context.strokeRect(1, 1, canvas.width - 2, canvas.height - 2);
                
                context.font = 'bold 24px Orbitron';
                context.fillStyle = '#00ff9d';
                context.textAlign = 'center';
                context.fillText(stage, canvas.width / 2, 50);
                
                context.font = '14px Inter';
                context.fillStyle = '#8892b0';
                context.fillText(`Stage ${i + 1}`, canvas.width / 2, 80);
                
                const texture = new THREE.CanvasTexture(canvas);
                const spriteMaterial = new THREE.SpriteMaterial({ map: texture });
                const sprite = new THREE.Sprite(spriteMaterial);
                sprite.position.copy(node.position);
                sprite.position.y = 6;
                sprite.scale.set(8, 4, 1);
                scene.add(sprite);
            });
            
            // Add connections
            for(let i = 0; i < stageNodes.length - 1; i++) {
                const curve = new THREE.CatmullRomCurve3([
                    stageNodes[i].position.clone(),
                    new THREE.Vector3(
                        (stageNodes[i].position.x + stageNodes[i + 1].position.x) / 2,
                        stageNodes[i].position.y + 3,
                        stageNodes[i].position.z
                    ),
                    stageNodes[i + 1].position.clone()
                ]);
                
                const geometry = new THREE.TubeGeometry(curve, 20, 0.2, 8, false);
                const material = new THREE.MeshBasicMaterial({
                    color: 0x6c63ff,
                    transparent: true,
                    opacity: 0.5
                });
                
                const tube = new THREE.Mesh(geometry, material);
                scene.add(tube);
            }
            
            // Add data particles flowing through pipeline
            const particles = [];
            for(let i = 0; i < 20; i++) {
                const geometry = new THREE.SphereGeometry(0.3, 8, 8);
                const material = new THREE.MeshBasicMaterial({
                    color: 0xff2e63,
                    transparent: true,
                    opacity: 0.8
                });
                
                const particle = new THREE.Mesh(geometry, material);
                particle.userData = {
                    progress: Math.random(),
                    speed: 0.2 + Math.random() * 0.3,
                    stage: Math.floor(Math.random() * pipelineStages.length)
                };
                
                // Position at random stage
                const stageIndex = particle.userData.stage;
                particle.position.copy(stageNodes[stageIndex].position);
                particle.position.y += 1;
                
                scene.add(particle);
                particles.push(particle);
            }
            
            // Add lights
            const ambientLight = new THREE.AmbientLight(0x404040, 0.5);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 20, 15);
            scene.add(directionalLight);
            
            // Camera position
            camera.position.set(0, 10, 30);
            
            // Controls
            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            
            // Animation
            let animationTime = 0;
            function animateWorkflow() {
                requestAnimationFrame(animateWorkflow);
                animationTime += 0.01;
                
                // Animate stage nodes
                stageNodes.forEach((node, i) => {
                    node.rotation.x += 0.01;
                    node.rotation.y += 0.01;
                    node.scale.setScalar(1 + Math.sin(animationTime * 2 + i) * 0.1);
                });
                
                // Animate particles
                particles.forEach(particle => {
                    particle.userData.progress += particle.userData.speed * 0.01;
                    
                    if(particle.userData.progress > 1) {
                        particle.userData.progress = 0;
                        particle.userData.stage = (particle.userData.stage + 1) % stageNodes.length;
                    }
                    
                    const currentStage = particle.userData.stage;
                    const nextStage = (currentStage + 1) % stageNodes.length;
                    
                    // Interpolate between stages
                    const startPos = stageNodes[currentStage].position.clone();
                    const endPos = stageNodes[nextStage].position.clone();
                    
                    particle.position.x = startPos.x + (endPos.x - startPos.x) * particle.userData.progress;
                    particle.position.y = startPos.y + 1 + Math.sin(particle.userData.progress * Math.PI) * 2;
                    particle.position.z = startPos.z + (endPos.z - startPos.z) * particle.userData.progress;
                });
                
                controls.update();
                renderer.render(scene, camera);
            }
            
            animateWorkflow();
            
            // Handle resize
            window.addEventListener('resize', () => {
                camera.aspect = canvas.clientWidth / canvas.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(canvas.clientWidth, canvas.clientHeight);
            });
        }
        
        // Initialize everything
        window.addEventListener('load', () => {
            initThreeJS();
            createParticles();
            
            // Add click handlers for workflow controls
            document.getElementById('playBtn').addEventListener('click', () => {
                if (!isTyping) {
                    typeCode();
                }
            });
            
            document.getElementById('resetBtn').addEventListener('click', () => {
                // Reset workflow
                codeDemo.style.display = 'block';
                vizDemo.style.display = 'none';
                compileBtn.innerHTML = '<i class="fas fa-play-circle"></i> Compile & Visualize';
                compileBtn.disabled = false;
                compileBtn.style.animation = '';
                compileBtn.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
                
                // Reset stages
                stageDescriptions.forEach((desc, i) => {
                    if(i === 0) desc.textContent = 'Typing...';
                    else desc.textContent = 'Waiting...';
                });
                
                stageIcons.forEach(icon => {
                    icon.style.background = 'var(--dark)';
                    icon.style.borderColor = 'var(--primary)';
                    icon.style.boxShadow = 'none';
                    icon.innerHTML = `<i class="${icon.querySelector('i').className}"></i>`;
                });
                
                // Start typing again
                setTimeout(typeCode, 500);
            });
            
            // Start workflow automatically
            setTimeout(() => {
                typeCode();
            }, 2000);
        });
        
        // Add hover effect to cards
        document.querySelectorAll('.language-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) rotateX(5deg)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) rotateX(0)';
            });
        });
        
        // Add keyboard shortcut for workflow reset
        document.addEventListener('keydown', (e) => {
            if(e.key === 'r' && e.ctrlKey) {
                document.getElementById('resetBtn').click();
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
