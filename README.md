# Compiler3D-Visualizer

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![Three.js](https://img.shields.io/badge/Three.js-r128+-black.svg)](https://threejs.org)
[![GCC Required](https://img.shields.io/badge/Compiler-GCC%2FClang-green.svg)](https://gcc.gnu.org)

**Compiler3D-Viz** is an educational web application that visualizes the C compilation process in interactive 3D. Experience compilation like never before as abstract concepts transform into tangible, explorable visual structures.

##  Live Demo
*Check out the live version here: [[compiler3d-viz.demo.com](https://3d-compiler.gt.tc/)]*

##  Features

###  Core Visualization
- **3D Abstract Syntax Trees** - Explore hierarchical code structures in immersive 3D space
- **Interactive Compilation Pipeline** - Watch code flow through compilation stages like an assembly line
- **Control Flow Graphs** - Navigate execution paths as interconnected 3D networks
- **Memory Stack Visualization** - See stack frames and variables in spatial 3D representation

###  Technical Capabilities
- **Real-time Compilation** - Visualize GCC compilation output instantly
- **Multi-optimization Comparison** - Compare -O0, -O1, -O2, -O3 transformations
- **Step-by-Step Execution** - Control compilation flow with interactive timeline
- **VR Mode Ready** - Experience compilation in virtual reality (WebXR compatible)

###  Educational Tools
- **Hover Details** - Get instant information on any compilation element
- **Error Highlighting** - Visualize compilation errors in context
- **Export Capabilities** - Save visualizations as images or 3D models
- **Example Gallery** - Learn with curated C code examples

##  Quick Start

### Prerequisites
- PHP 7.4+ with `shell_exec` enabled
- GCC or Clang compiler
- Web server (Apache/Nginx)
- Modern browser with WebGL 2.0 support

### Installation
```bash
# Clone repository
git clone [https://github.com/Agabaofficial/Compiler3D-Viz.git](https://github.com/Agabaofficial/c-compiler-3d-visualizer)
cd Compiler3D-Viz

# Set permissions
chmod 777 tmp/

# Configure PHP (if needed)
# Ensure shell_exec is enabled in php.ini

# Start local server
php -S localhost:8000
