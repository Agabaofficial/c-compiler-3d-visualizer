class CompilerVisualizer {
    constructor() {
        this.currentSession = null;
        this.currentStep = 0;
        this.isAnimating = false;
        this.visualizers = {};
        this.currentView = 'pipeline';
        
        this.initializeElements();
        this.bindEvents();
        this.loadExamples();
        this.initializeVisualizers();
    }
    
    initializeElements() {
        // DOM Elements
        this.elements = {
            sourceCode: document.getElementById('source-code'),
            compileBtn: document.getElementById('compile-btn'),
            stepBtn: document.getElementById('step-btn'),
            resetBtn: document.getElementById('reset-btn'),
            stageSelect: document.getElementById('stage-select'),
            viewMode: document.getElementById('view-mode'),
            speedSlider: document.getElementById('speed-slider'),
            autoRotate: document.getElementById('auto-rotate'),
            showLabels: document.getElementById('show-labels'),
            exampleSelect: document.getElementById('example-select'),
            
            // Visualization
            visualizationCanvas: document.getElementById('visualization-canvas'),
            
            // Status
            statusMessage: document.getElementById('status-message'),
            progressBar: document.querySelector('.progress-fill'),
            
            // Info panels
            stageInfo: document.getElementById('stage-info'),
            tokensContent: document.getElementById('tokens-content'),
            astContent: document.getElementById('ast-content'),
            irContent: document.getElementById('ir-content'),
            asmContent: document.getElementById('asm-content'),
            
            // Download buttons
            downloadAST: document.getElementById('download-ast'),
            downloadCFG: document.getElementById('download-cfg'),
            downloadASM: document.getElementById('download-asm'),
            
            // Controls
            zoomIn: document.getElementById('zoom-in'),
            zoomOut: document.getElementById('zoom-out'),
            resetView: document.getElementById('reset-view'),
            screenshot: document.getElementById('screenshot')
        };
    }
    
    bindEvents() {
        // Compile button
        this.elements.compileBtn.addEventListener('click', () => this.compile());
        
        // Step through button
        this.elements.stepBtn.addEventListener('click', () => this.nextStep());
        
        // Reset button
        this.elements.resetBtn.addEventListener('click', () => this.reset());
        
        // Stage selection
        this.elements.stageSelect.addEventListener('change', (e) => {
            this.changeStage(e.target.value);
        });
        
        // View mode
        this.elements.viewMode.addEventListener('change', (e) => {
            this.changeView(e.target.value);
        });
        
        // Example selection
        this.elements.exampleSelect.addEventListener('change', (e) => {
            if (e.target.value) this.loadExample(e.target.value);
        });
        
        // Download buttons
        this.elements.downloadAST.addEventListener('click', () => this.download('ast'));
        this.elements.downloadCFG.addEventListener('click', () => this.download('cfg'));
        this.elements.downloadASM.addEventListener('click', () => this.download('asm'));
        
        // Visualization controls
        this.elements.zoomIn.addEventListener('click', () => this.zoom(1.2));
        this.elements.zoomOut.addEventListener('click', () => this.zoom(0.8));
        this.elements.resetView.addEventListener('click', () => this.resetView());
        this.elements.screenshot.addEventListener('click', () => this.takeScreenshot());
        
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.switchTab(e.target));
        });
    }
    
    async compile() {
        const sourceCode = this.elements.sourceCode.value.trim();
        
        if (!sourceCode) {
            this.showStatus('Please enter some C code', 'error');
            return;
        }
        
        this.showStatus('Compiling...', 'info');
        this.elements.compileBtn.disabled = true;
        this.elements.compileBtn.textContent = 'Compiling...';
        this.updateProgress(10);
        
        try {
            const response = await fetch('api/compile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ source_code: sourceCode })
            });
            
            if (!response.ok) {
                throw new Error('Compilation failed');
            }
            
            const result = await response.json();
            
            if (result.error) {
                throw new Error(result.error);
            }
            
            this.currentSession = result.session_id;
            this.currentStep = 0;
            
            // Update UI with results
            this.updateOutputDisplays(result.outputs);
            this.updateStageInfo(result.stages[0]);
            
            // Visualize
            await this.visualize('all');
            
            // Enable step through
            this.elements.stepBtn.disabled = false;
            
            this.showStatus('Compilation successful!', 'success');
            this.updateProgress(100);
            
        } catch (error) {
            console.error('Compilation error:', error);
            this.showStatus(`Error: ${error.message}`, 'error');
            this.updateProgress(0);
        } finally {
            this.elements.compileBtn.disabled = false;
            this.elements.compileBtn.textContent = 'Compile & Visualize';
        }
    }
    
    async nextStep() {
        if (!this.currentSession || this.isAnimating) return;
        
        this.isAnimating = true;
        this.elements.stepBtn.disabled = true;
        
        try {
            const response = await fetch(`api/step.php?session_id=${this.currentSession}&step=${this.currentStep}&action=next`);
            const stepData = await response.json();
            
            if (stepData.error) {
                throw new Error(stepData.error);
            }
            
            this.currentStep = stepData.current_step;
            
            // Update stage info
            this.updateStageInfo(stepData.stage);
            
            // Highlight current stage
            this.highlightStage(this.currentStep);
            
            // Apply animations
            await this.applyAnimations(stepData.animations);
            
            // Show explanation
            this.showExplanation(stepData.explanations);
            
            this.showStatus(`Step ${this.currentStep + 1}/${stepData.total_steps}: ${stepData.explanations.title}`, 'info');
            
        } catch (error) {
            console.error('Step error:', error);
            this.showStatus(`Error: ${error.message}`, 'error');
        } finally {
            this.isAnimating = false;
            this.elements.stepBtn.disabled = false;
        }
    }
    
    async changeStage(stage) {
        if (!this.currentSession) return;
        
        this.showStatus(`Loading ${stage} visualization...`, 'info');
        
        try {
            const response = await fetch(`api/visualize.php?session_id=${this.currentSession}&stage=${stage}`);
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Switch to appropriate visualizer
            let visualizerType = 'pipeline';
            if (stage === 'syntax') visualizerType = 'ast';
            else if (stage === 'ir' || stage === 'optimization' || stage === 'codegen') visualizerType = 'cfg';
            
            this.changeView(visualizerType);
            
            // Update visualization
            if (this.visualizers[this.currentView]) {
                this.visualizers[this.currentView].update(data);
            }
            
            this.showStatus(`${stage} visualization loaded`, 'success');
            
        } catch (error) {
            console.error('Stage change error:', error);
            this.showStatus(`Error: ${error.message}`, 'error');
        }
    }
    
    async changeView(view) {
        this.currentView = view;
        
        // Hide all visualizers
        Object.values(this.visualizers).forEach(v => v.hide());
        
        // Show selected visualizer
        if (this.visualizers[view]) {
            this.visualizers[view].show();
        }
    }
    
    async visualize(stage = 'all') {
        if (!this.currentSession) return;
        
        try {
            const response = await fetch(`api/visualize.php?session_id=${this.currentSession}&stage=${stage}`);
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Update current visualizer
            if (this.visualizers[this.currentView]) {
                this.visualizers[this.currentView].update(data);
            }
            
        } catch (error) {
            console.error('Visualization error:', error);
            this.showStatus(`Error: ${error.message}`, 'error');
        }
    }
    
    async applyAnimations(animations) {
        for (const animation of animations) {
            await this.playAnimation(animation);
        }
    }
    
    playAnimation(animation) {
        return new Promise(resolve => {
            // Apply animation to visualization
            if (this.visualizers[this.currentView]) {
                this.visualizers[this.currentView].animate(animation);
            }
            
            setTimeout(resolve, animation.duration || 1000);
        });
    }
    
    highlightStage(stepIndex) {
        // Update stage select
        this.elements.stageSelect.selectedIndex = stepIndex;
        
        // Highlight in visualization
        if (this.visualizers[this.currentView]) {
            this.visualizers[this.currentView].highlight(`stage_${stepIndex}`);
        }
    }
    
    updateOutputDisplays(outputs) {
        // Tokens
        if (outputs.tokens) {
            this.elements.tokensContent.textContent = JSON.stringify(outputs.tokens, null, 2);
        }
        
        // AST
        if (outputs.ast) {
            this.elements.astContent.textContent = JSON.stringify(outputs.ast, null, 2);
        }
        
        // IR
        if (outputs.ir) {
            this.elements.irContent.textContent = outputs.ir.map(instr => {
                if (instr.label) return instr.label + ':';
                return '    ' + Object.values(instr).join(' ');
            }).join('\n');
        }
        
        // Assembly
        if (outputs.asm) {
            this.elements.asmContent.textContent = outputs.asm.join('\n');
        }
    }
    
    updateStageInfo(stage) {
        const info = `
            <h3>${stage.name}</h3>
            <p><strong>Status:</strong> <span class="status-${stage.status}">${stage.status}</span></p>
            <p><strong>Duration:</strong> ${stage.duration}ms</p>
            ${stage.metrics ? `<p><strong>Complexity:</strong> ${stage.metrics.complexity}/10</p>` : ''}
        `;
        
        this.elements.stageInfo.innerHTML = info;
    }
    
    showExplanation(explanation) {
        const info = `
            <h3>${explanation.title}</h3>
            <p><strong>${explanation.description}</strong></p>
            <p>${explanation.details}</p>
        `;
        
        this.elements.stageInfo.innerHTML = info;
    }
    
    showStatus(message, type = 'info') {
        this.elements.statusMessage.textContent = message;
        this.elements.statusMessage.className = '';
        this.elements.statusMessage.classList.add(`status-${type}`);
        
        // Auto-clear success messages
        if (type === 'success') {
            setTimeout(() => {
                if (this.elements.statusMessage.textContent === message) {
                    this.showStatus('Ready', 'info');
                }
            }, 3000);
        }
    }
    
    updateProgress(percent) {
        this.elements.progressBar.style.width = `${percent}%`;
    }
    
    reset() {
        this.currentSession = null;
        this.currentStep = 0;
        
        // Reset UI
        this.elements.sourceCode.value = `#include <stdio.h>

int main() {
    int a = 5;
    int b = 10;
    int sum = a + b;
    
    if (sum > 10) {
        printf("Sum is greater than 10: %d\\n", sum);
    } else {
        printf("Sum is 10 or less: %d\\n", sum);
    }
    
    for(int i = 0; i < 3; i++) {
        printf("Iteration %d\\n", i);
    }
    
    return 0;
}`;
        
        this.elements.stepBtn.disabled = true;
        this.elements.stageSelect.selectedIndex = 0;
        this.updateProgress(0);
        
        // Clear outputs
        this.elements.tokensContent.textContent = '';
        this.elements.astContent.textContent = '';
        this.elements.irContent.textContent = '';
        this.elements.asmContent.textContent = '';
        
        this.elements.stageInfo.innerHTML = '<p>Select a compilation stage to view details</p>';
        
        // Reset visualization
        Object.values(this.visualizers).forEach(v => v.reset());
        
        this.showStatus('Reset complete', 'info');
    }
    
    resetView() {
        if (this.visualizers[this.currentView]) {
            this.visualizers[this.currentView].resetCamera();
        }
    }
    
    zoom(factor) {
        if (this.visualizers[this.currentView]) {
            this.visualizers[this.currentView].zoom(factor);
        }
    }
    
    takeScreenshot() {
        if (this.visualizers[this.currentView]) {
            this.visualizers[this.currentView].takeScreenshot();
        }
    }
    
    switchTab(button) {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        // Activate clicked tab
        button.classList.add('active');
        const tabId = button.dataset.tab;
        document.getElementById(`${tabId}-content`).classList.add('active');
    }
    
    async download(type) {
        if (!this.currentSession) {
            this.showStatus('No compilation session found', 'error');
            return;
        }
        
        this.showStatus(`Downloading ${type.toUpperCase()}...`, 'info');
        
        try {
            const response = await fetch(`api/download.php?session_id=${this.currentSession}&type=${type}&format=json`);
            
            if (!response.ok) {
                throw new Error('Download failed');
            }
            
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${type}_${this.currentSession}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            this.showStatus(`${type.toUpperCase()} downloaded successfully`, 'success');
            
        } catch (error) {
            console.error('Download error:', error);
            this.showStatus(`Error: ${error.message}`, 'error');
        }
    }
    
    async loadExamples() {
        try {
            const response = await fetch('assets/examples/examples.json');
            const examples = await response.json();
            
            const select = this.elements.exampleSelect;
            examples.forEach(example => {
                const option = document.createElement('option');
                option.value = example.id;
                option.textContent = example.name;
                select.appendChild(option);
            });
            
        } catch (error) {
            console.error('Failed to load examples:', error);
        }
    }
    
    async loadExample(exampleId) {
        try {
            const response = await fetch('assets/examples/examples.json');
            const examples = await response.json();
            
            const example = examples.find(e => e.id === exampleId);
            if (example) {
                this.elements.sourceCode.value = example.code;
                this.showStatus(`Loaded example: ${example.name}`, 'success');
            }
            
        } catch (error) {
            console.error('Failed to load example:', error);
            this.showStatus('Error loading example', 'error');
        }
    }
    
    initializeVisualizers() {
        const canvas = this.elements.visualizationCanvas;
        
        this.visualizers = {
            pipeline: new CompilationPipeline3D(canvas),
            ast: new ASTVisualizer(canvas),
            cfg: new CFGVisualizer(canvas),
            memory: new MemoryVisualizer(canvas)
        };
        
        // Start with pipeline view
        this.changeView('pipeline');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.visualizer = new CompilerVisualizer();
});

// Utility functions
function formatDuration(ms) {
    if (ms < 1000) return `${ms}ms`;
    return `${(ms / 1000).toFixed(2)}s`;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}