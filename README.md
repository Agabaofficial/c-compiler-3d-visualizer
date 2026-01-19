# CompilerHub - Advanced Compiler Visualization Platform

[![License: MIT](https://img.shields.io/badge/License-MIT-blue. svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![Three.js](https://img.shields.io/badge/Three. js-r128+-black.svg)](https://threejs.org)
[![Languages](https://img.shields.io/badge/Languages-6+-orange.svg)](#languages)
[![Live Demo](https://img.shields.io/badge/Demo-Online-brightgreen.svg)](https://compilerhub.dev)

**CompilerHub** is an advanced educational web platform that visualizes compiler internals across multiple programming languages in interactive 3D. Experience compilation pipelines like never before as abstract concepts transform into tangible, explorable visual structures across Java, C++, C, Swift, Brainfuck, and Go.

## <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/chrome/chrome-original.svg" width="20"/> Live Demo
*Experience the live platform: [compilerhub.dev](https://compilerhub.dev)*

## <img src="https://img.icons8.com/fluency/48/star--v1.png" width="20"/> Key Features

### <img src="https://img.icons8.com/color/48/code. png" width="18"/> Multi-Language Support
- **Java**: JVM bytecode generation, class loading, garbage collection visualization
- **C++**: Template instantiation, header processing, linker visualization
- **C**:  Direct compilation to assembly, memory layout visualization
- **Swift**: SIL visualization, ARC optimization, protocol witness tables
- **Brainfuck**: Minimalist language with tape memory visualization
- **Go**: Goroutine scheduling, channel operations, interface tables

### <img src="https://img.icons8.com/color/48/3d-model.png" width="18"/> Advanced Visualization
- **Interactive 3D Pipelines**: Rotate, zoom, and explore compiler internals in three dimensions
- **Real-time Workflow**: Watch code progress through lexical, syntax, and semantic analysis
- **Memory Models**: JVM heap, C stack frames, Swift ARC, Brainfuck tape visualization
- **AST Explorer**: Interactive Abstract Syntax Trees with node highlighting
- **Pipeline View**: Step through compilation stages with detailed explanations

### <img src="https://img.icons8.com/color/48/light. png" width="18"/> Educational Tools
- **Live Code Editor**: Type code and watch it compile in real-time
- **Export System**: Download ASTs, intermediate code, and 3D visualizations
- **Error Highlighting**: Visualize compilation errors in context
- **Example Gallery**:  Curated examples for each language
- **Step-by-Step Execution**: Control compilation flow with interactive timeline

### <img src="https://img.icons8.com/color/48/settings.png" width="18"/> Technical Features
- **Modern Web Stack**: Built with Three. js, PHP, and modern CSS3
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Cross-Browser**: Compatible with all modern browsers
- **Performance Optimized**: Efficient rendering for complex visualizations
- **Extensible Architecture**: Easy to add new languages and features

## <img src="https://img.icons8.com/color/48/bar-chart.png" width="20"/> Supported Languages

| Language | Paradigm | Key Visualizations | Status |
|----------|----------|-------------------|--------|
| **Java** | OOP, JVM | Bytecode, GC, Class Loading | ✅ Production |
| **C++** | Systems, OOP | Templates, Linker, Assembly | ✅ Production |
| **C** | Procedural | Memory Layout, Pointers | ✅ Production |
| **Swift** | Modern, Protocol | SIL, ARC, Generics | ✅ Production |
| **Brainfuck** | Esoteric | Tape Memory, Loops | ✅ Production |
| **Go** | Concurrent | Goroutines, Channels | ✅ Production |

## <img src="https://img.icons8.com/color/48/wrench.png" width="20"/> Quick Start

### Prerequisites
- PHP 7.4+ with `shell_exec` enabled
- GCC/Clang for C/C++ compilation
- Java Runtime for Java visualizations
- Swift compiler (optional, for Swift visualizations)
- Go compiler (optional, for Go visualizations)
- Modern browser with WebGL 2.0 support

### Installation
```bash
# Clone repository
git clone https://github.com/Agabaofficial/compiler-visualizer-hub.git
cd compiler-visualizer-hub

# Set permissions for temporary files
chmod 777 tmp/

# Configure PHP (if needed)
# Ensure shell_exec is enabled in php.ini

# Start local server
php -S localhost:8000

# Or use Apache/Nginx
# Copy files to your web server directory
```

### Docker Installation
```bash
# Build and run with Docker
docker build -t compilerhub .
docker run -p 8000:80 compilerhub

# Or use Docker Compose
docker-compose up
```

## <img src="https://img.icons8.com/color/48/folder-tree.png" width="20"/> Project Structure
```
compiler-visualizer-hub/
├── index. php              # Main hub page
├── java.php              # Java visualizer
├── c-plus. php            # C++ visualizer
├── c.php                 # C visualizer
├── swift. php             # Swift visualizer
├── brain-fuck.php        # Brainfuck visualizer
├── go.php                # Go visualizer
├── api/                  # Backend API endpoints
│   ├── compile.php       # Compilation API
│   ├── analyze.php       # Code analysis
│   └── visualize.php     # Visualization data
├��─ assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript modules
│   └── images/          # Images and icons
├── tmp/                  # Temporary files
└── docs/                 # Documentation
```

## <img src="https://img.icons8.com/color/48/search.png" width="20"/> How It Works

### 1. **Code Input**
Users write or upload code in any supported language through the web interface.

### 2. **Compilation Pipeline**
The code undergoes the complete compilation process:
- Lexical Analysis (Tokenization)
- Syntax Analysis (AST Generation)
- Semantic Analysis (Type Checking)
- Intermediate Code Generation
- Optimization
- Target Code Generation

### 3. **3D Visualization**
Each stage is visualized in interactive 3D:
- **Nodes**: Represent tokens, AST nodes, instructions
- **Edges**:  Show relationships and data flow
- **Colors**: Differentiate between stages and types
- **Animations**: Show progression through pipeline

### 4. **Interactive Exploration**
Users can:
- Rotate and zoom 3D visualizations
- Click nodes for detailed information
- Step through compilation stages
- Compare different optimization levels
- Export visualizations for study

## <img src="https://img.icons8.com/color/48/puzzle. png" width="20"/> Adding New Languages

CompilerHub is designed to be extensible. To add a new language:

1. Create a new visualizer file: `newlang.php`
2. Implement the compilation pipeline stages
3. Add 3D visualization components
4. Update the main hub with language card
5. Add language-specific API endpoints

Example structure for a new language: 
```php
// newlang.php
class NewLanguageVisualizer {
    public function tokenize($code) { /* ... */ }
    public function parse($tokens) { /* ... */ }
    public function generateAST($parseTree) { /* ... */ }
    public function visualize($ast) { /* ... */ }
}
```

## <img src="https://img.icons8.com/color/48/book.png" width="20"/> Educational Use

CompilerHub is ideal for:
- **Computer Science Students**:  Learn compiler design principles
- **University Courses**: Visualization aid for compiler design classes
- **Self-Learners**: Understand language implementation details
- **Researchers**: Study compilation techniques across languages
- **Developers**: Optimize code by understanding compilation

## <img src="https://img.icons8.com/color/48/api-settings.png" width="20"/> API Reference

### Compilation Endpoint
```http
POST /api/compile.php
Content-Type: application/json

{
    "language": "java",
    "code": "public class Main {}",
    "options": {
        "optimization": "O2",
        "debug": true
    }
}
```

### Analysis Endpoint
```http
POST /api/analyze.php
Content-Type: application/json

{
    "language": "c",
    "code": "int main() { return 0; }",
    "analysis": ["ast", "cfg", "symbol-table"]
}
```

## <img src="https://img.icons8.com/color/48/rocket.png" width="20"/> Performance

- **Frontend**: 60 FPS 3D rendering with WebGL
- **Backend**: Async compilation with worker processes
- **Caching**: Compiled results cached for performance
- **Scalability**:  Horizontal scaling ready

## <img src="https://img.icons8.com/color/48/handshake.png" width="20"/> Contributing

We welcome contributions! Here's how:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** changes (`git commit -m 'Add AmazingFeature'`)
4. **Push** to branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

### Development Setup
```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Run tests
npm test
```

### Areas for Contribution
- Add new programming languages
- Improve 3D visualizations
- Add more compilation stages
- Create educational content
- Optimize performance
- Fix bugs and issues

## <img src="https://img.icons8.com/color/48/document.png" width="20"/> License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## <img src="https://img.icons8.com/color/48/praying. png" width="20"/> Acknowledgments

- **Three.js** for 3D visualization capabilities
- **GCC/LLVM** for compilation infrastructure
- **Contributors** who have helped improve the platform
- **Universities** using this for computer science education

## <img src="https://img.icons8.com/color/48/user-group-man-man. png" width="20"/> Authors

- **Agaba Olivier** - Lead Developer & Architect
- **Iradi Arinda** - UI/UX Design & Frontend Development

## <img src="https://img.icons8.com/color/48/phone.png" width="20"/> Contact

- **GitHub**: [@Agabaofficial](https://github.com/Agabaofficial)
- **Email**: contact@compilerhub.dev
- **Website**:  [compilerhub.dev](https://compilerhub.dev)

---

<div align="center">

**Star us on GitHub** — if you find this project helpful!

**Share with educators** — help students learn compiler design!

**Report issues** — help us improve the platform! 

</div>
