class CFGVisualizer {
    constructor(container) {
        this.container = container;
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.nodes = new Map();
        this.edges = [];
        this.animationId = null;
        
        this.init();
        this.animate();
    }
    
    init() {
        // Create scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x0a0a0a);
        
        // Add lights
        const ambientLight = new THREE.AmbientLight(0x404040, 0.5);
        this.scene.add(ambientLight);
        
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(10, 20, 0);
        directionalLight.castShadow = true;
        this.scene.add(directionalLight);
        
        // Add point lights for depth
        const pointLight1 = new THREE.PointLight(0x3498db, 0.5, 50);
        pointLight1.position.set(-20, 10, -10);
        this.scene.add(pointLight1);
        
        const pointLight2 = new THREE.PointLight(0x2ecc71, 0.5, 50);
        pointLight2.position.set(20, 10, 10);
        this.scene.add(pointLight2);
        
        // Create camera
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        this.camera.position.set(0, 15, 30);
        
        // Create renderer
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.renderer.setSize(width, height);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.container.appendChild(this.renderer.domElement);
        
        // Create orbit controls
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        
        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    update(data) {
        this.clear();
        this.createCFG(data);
    }
    
    createCFG(data) {
        if (!data.nodes || !data.edges) {
            // Generate sample CFG if no data provided
            this.createSampleCFG();
            return;
        }
        
        // Create nodes in a grid layout
        const gridSize = Math.ceil(Math.sqrt(data.nodes.length));
        let index = 0;
        
        data.nodes.forEach(nodeData => {
            const row = Math.floor(index / gridSize);
            const col = index % gridSize;
            
            const x = (col - gridSize / 2) * 8;
            const z = (row - gridSize / 2) * 8;
            
            const node = this.createCFGNode(nodeData, x, 0, z);
            this.nodes.set(nodeData.id, node);
            this.scene.add(node);
            
            index++;
        });
        
        // Create edges
        data.edges.forEach(edgeData => {
            const edge = this.createCFGEdge(edgeData);
            if (edge) {
                this.edges.push(edge);
                this.scene.add(edge);
            }
        });
        
        // Add execution flow animation
        this.addExecutionFlow();
    }
    
    createSampleCFG() {
        // Create sample control flow graph
        const nodes = [
            { id: 'start', label: 'Start', type: 'entry' },
            { id: 'cond1', label: 'if (x > 0)', type: 'condition' },
            { id: 'block1', label: 'x = x * 2', type: 'statement' },
            { id: 'block2', label: 'x = x / 2', type: 'statement' },
            { id: 'cond2', label: 'while (x < 10)', type: 'loop' },
            { id: 'block3', label: 'x++', type: 'statement' },
            { id: 'end', label: 'Return x', type: 'exit' }
        ];
        
        const edges = [
            { from: 'start', to: 'cond1', label: '' },
            { from: 'cond1', to: 'block1', label: 'true' },
            { from: 'cond1', to: 'block2', label: 'false' },
            { from: 'block1', to: 'cond2', label: '' },
            { from: 'block2', to: 'cond2', label: '' },
            { from: 'cond2', to: 'block3', label: 'true' },
            { from: 'cond2', to: 'end', label: 'false' },
            { from: 'block3', to: 'cond2', label: 'loop' }
        ];
        
        // Position nodes
        const positions = {
            'start': [0, 0, 12],
            'cond1': [0, 0, 6],
            'block1': [-6, 0, 0],
            'block2': [6, 0, 0],
            'cond2': [0, 0, -6],
            'block3': [0, 0, -12],
            'end': [0, 0, -18]
        };
        
        // Create nodes
        nodes.forEach(node => {
            const pos = positions[node.id];
            const nodeObj = this.createCFGNode(node, pos[0], pos[1], pos[2]);
            this.nodes.set(node.id, nodeObj);
            this.scene.add(nodeObj);
        });
        
        // Create edges
        edges.forEach(edge => {
            const edgeObj = this.createCFGEdge(edge);
            if (edgeObj) {
                this.edges.push(edgeObj);
                this.scene.add(edgeObj);
            }
        });
        
        this.addExecutionFlow();
    }
    
    createCFGNode(nodeData, x, y, z) {
        const group = new THREE.Group();
        
        // Determine color based on node type
        const color = this.getNodeColor(nodeData.type);
        const isCondition = nodeData.type === 'condition' || nodeData.type === 'loop';
        
        // Create main shape
        let geometry;
        if (isCondition) {
            // Diamond shape for conditions
            geometry = new THREE.ConeGeometry(1.5, 3, 4);
            geometry.rotateX(Math.PI);
        } else if (nodeData.type === 'entry' || nodeData.type === 'exit') {
            // Circle for start/end
            geometry = new THREE.CylinderGeometry(1.5, 1.5, 1, 16);
        } else {
            // Rectangle for statements
            geometry = new THREE.BoxGeometry(4, 2, 2);
        }
        
        const material = new THREE.MeshPhongMaterial({
            color: color,
            emissive: color,
            emissiveIntensity: 0.1,
            shininess: 100,
            transparent: true,
            opacity: 0.9
        });
        
        const mesh = new THREE.Mesh(geometry, material);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        group.add(mesh);
        
        // Add inner glow for conditions
        if (isCondition) {
            const innerGeometry = isCondition ? 
                new THREE.ConeGeometry(1.2, 2.5, 4) : 
                new THREE.BoxGeometry(3.5, 1.5, 1.5);
            
            if (isCondition) innerGeometry.rotateX(Math.PI);
            
            const innerMaterial = new THREE.MeshBasicMaterial({
                color: 0xffffff,
                transparent: true,
                opacity: 0.3
            });
            
            const innerMesh = new THREE.Mesh(innerGeometry, innerMaterial);
            group.add(innerMesh);
        }
        
        // Position the node
        group.position.set(x, y, z);
        
        // Add label
        this.addCFGLabel(group, nodeData.label);
        
        // Add floating animation
        this.addFloatingAnimation(group);
        
        // Add pulse animation for entry/exit
        if (nodeData.type === 'entry' || nodeData.type === 'exit') {
            this.addPulseAnimation(mesh);
        }
        
        return group;
    }
    
    createCFGEdge(edgeData) {
        const fromNode = this.nodes.get(edgeData.from);
        const toNode = this.nodes.get(edgeData.to);
        
        if (!fromNode || !toNode) {
            console.warn(`Missing nodes for edge: ${edgeData.from} -> ${edgeData.to}`);
            return null;
        }
        
        const fromPos = fromNode.position;
        const toPos = toNode.position;
        
        // Create a curved line
        const curve = new THREE.CatmullRomCurve3([
            new THREE.Vector3(fromPos.x, fromPos.y, fromPos.z),
            new THREE.Vector3(
                (fromPos.x + toPos.x) / 2,
                Math.max(fromPos.y, toPos.y) + 3,
                (fromPos.z + toPos.z) / 2
            ),
            new THREE.Vector3(toPos.x, toPos.y, toPos.z)
        ]);
        
        const tubeGeometry = new THREE.TubeGeometry(curve, 20, 0.1, 8, false);
        const tubeMaterial = new THREE.MeshPhongMaterial({
            color: edgeData.label === 'true' ? 0x2ecc71 : 
                   edgeData.label === 'false' ? 0xe74c3c : 0x3498db,
            transparent: true,
            opacity: 0.6,
            emissive: edgeData.label === 'true' ? 0x2ecc71 : 
                     edgeData.label === 'false' ? 0xe74c3c : 0x3498db,
            emissiveIntensity: 0.1
        });
        
        const tube = new THREE.Mesh(tubeGeometry, tubeMaterial);
        
        // Add arrow head
        this.addArrowHead(curve, edgeData.label);
        
        // Add flowing particles
        this.addCFGEdgeParticles(curve, edgeData.label);
        
        return tube;
    }
    
    addArrowHead(curve, label) {
        const endPoint = curve.getPoint(0.95);
        const tangent = curve.getTangent(0.95).normalize();
        
        const arrowGeometry = new THREE.ConeGeometry(0.3, 1, 8);
        const arrowMaterial = new THREE.MeshPhongMaterial({
            color: label === 'true' ? 0x2ecc71 : 
                   label === 'false' ? 0xe74c3c : 0x3498db,
            emissive: label === 'true' ? 0x2ecc71 : 
                     label === 'false' ? 0xe74c3c : 0x3498db,
            emissiveIntensity: 0.2
        });
        
        const arrow = new THREE.Mesh(arrowGeometry, arrowMaterial);
        
        // Position and orient arrow
        arrow.position.copy(endPoint);
        arrow.lookAt(endPoint.clone().add(tangent));
        arrow.rotateX(Math.PI / 2);
        
        this.scene.add(arrow);
        this.edges.push(arrow);
        
        // Add label near arrow if edge has a label
        if (label && label !== '') {
            this.addEdgeLabel(endPoint, label);
        }
    }
    
    addEdgeLabel(position, text) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = 128;
        canvas.height = 64;
        
        context.fillStyle = 'rgba(0, 0, 0, 0)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        
        context.font = 'bold 16px Arial';
        context.fillStyle = text === 'true' ? '#2ecc71' : 
                           text === 'false' ? '#e74c3c' : '#3498db';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(text, canvas.width / 2, canvas.height / 2);
        
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({
            map: texture,
            transparent: true
        });
        
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(3, 1.5, 1);
        sprite.position.copy(position).add(new THREE.Vector3(0, 1, 0));
        
        this.scene.add(sprite);
        this.edges.push(sprite);
    }
    
    addCFGLabel(group, text) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = 256;
        canvas.height = 128;
        
        context.fillStyle = 'rgba(0, 0, 0, 0)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        
        context.font = 'bold 20px Arial';
        context.fillStyle = '#ffffff';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        
        // Wrap text
        const words = text.split(' ');
        let lines = [];
        let currentLine = words[0];
        
        for (let i = 1; i < words.length; i++) {
            const word = words[i];
            const width = context.measureText(currentLine + ' ' + word).width;
            if (width < canvas.width - 20) {
                currentLine += ' ' + word;
            } else {
                lines.push(currentLine);
                currentLine = word;
            }
        }
        lines.push(currentLine);
        
        // Draw lines
        const lineHeight = 25;
        const startY = (canvas.height - (lines.length * lineHeight)) / 2;
        
        lines.forEach((line, index) => {
            context.fillText(line, canvas.width / 2, startY + index * lineHeight);
        });
        
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({
            map: texture,
            transparent: true
        });
        
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(6, 3, 1);
        sprite.position.set(0, 2, 0);
        group.add(sprite);
    }
    
    addFloatingAnimation(group) {
        const startY = group.position.y;
        const speed = 0.3 + Math.random() * 0.2;
        const amplitude = 0.3 + Math.random() * 0.2;
        
        const float = () => {
            if (!group.parent) return;
            
            const time = Date.now() * 0.001;
            group.position.y = startY + Math.sin(time * speed) * amplitude;
            
            // Slight rotation
            group.rotation.y += 0.001;
            
            requestAnimationFrame(float);
        };
        
        float();
    }
    
    addPulseAnimation(mesh) {
        const originalScale = mesh.scale.clone();
        const pulseSpeed = 0.003;
        
        const pulse = () => {
            if (!mesh.parent) return;
            
            const scaleFactor = 1 + Math.sin(Date.now() * pulseSpeed) * 0.1;
            mesh.scale.copy(originalScale).multiplyScalar(scaleFactor);
            
            // Pulsing emissive
            mesh.material.emissiveIntensity = 0.1 + Math.sin(Date.now() * pulseSpeed) * 0.1;
            mesh.material.needsUpdate = true;
            
            requestAnimationFrame(pulse);
        };
        
        pulse();
    }
    
    addCFGEdgeParticles(curve, label) {
        const particleCount = 15;
        const positions = new Float32Array(particleCount * 3);
        const colors = new Float32Array(particleCount * 3);
        
        const color = label === 'true' ? [0.2, 0.8, 0.2] :
                     label === 'false' ? [0.8, 0.2, 0.2] : [0.2, 0.6, 0.9];
        
        for (let i = 0; i < particleCount; i++) {
            const t = i / particleCount;
            const point = curve.getPoint(t);
            
            positions[i * 3] = point.x;
            positions[i * 3 + 1] = point.y;
            positions[i * 3 + 2] = point.z;
            
            colors[i * 3] = color[0];
            colors[i * 3 + 1] = color[1];
            colors[i * 3 + 2] = color[2];
        }
        
        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
        
        const material = new THREE.PointsMaterial({
            size: 0.25,
            vertexColors: true,
            transparent: true,
            opacity: 0.8
        });
        
        const particles = new THREE.Points(geometry, material);
        this.scene.add(particles);
        this.edges.push(particles);
        
        // Animate particles
        const animateParticles = () => {
            if (!particles.parent) return;
            
            const positions = geometry.attributes.position.array;
            const time = Date.now() * 0.001;
            
            for (let i = 0; i < particleCount; i++) {
                const t = (i / particleCount + time * 0.3) % 1;
                const point = curve.getPoint(t);
                
                positions[i * 3] = point.x;
                positions[i * 3 + 1] = point.y;
                positions[i * 3 + 2] = point.z;
            }
            
            geometry.attributes.position.needsUpdate = true;
            requestAnimationFrame(animateParticles);
        };
        
        animateParticles();
    }
    
    addExecutionFlow() {
        // Create a flowing execution path through the CFG
        const path = ['start', 'cond1', 'block1', 'cond2', 'block3', 'cond2', 'end'];
        
        const flowPoints = [];
        path.forEach(nodeId => {
            const node = this.nodes.get(nodeId);
            if (node) {
                flowPoints.push(node.position.clone().add(new THREE.Vector3(0, 1, 0)));
            }
        });
        
        if (flowPoints.length < 2) return;
        
        // Create a smooth curve through the points
        const curve = new THREE.CatmullRomCurve3(flowPoints);
        
        // Create flowing particles along execution path
        this.createExecutionParticles(curve);
    }
    
    createExecutionParticles(curve) {
        const particleCount = 30;
        const positions = new Float32Array(particleCount * 3);
        const colors = new Float32Array(particleCount * 3);
        
        for (let i = 0; i < particleCount; i++) {
            const t = i / particleCount;
            const point = curve.getPoint(t);
            
            positions[i * 3] = point.x;
            positions[i * 3 + 1] = point.y;
            positions[i * 3 + 2] = point.z;
            
            // Gold color for execution flow
            colors[i * 3] = 1.0; // R
            colors[i * 3 + 1] = 0.84; // G
            colors[i * 3 + 2] = 0.0; // B
        }
        
        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
        
        const material = new THREE.PointsMaterial({
            size: 0.4,
            vertexColors: true,
            transparent: true,
            opacity: 0.9
        });
        
        const particles = new THREE.Points(geometry, material);
        this.scene.add(particles);
        this.edges.push(particles);
        
        // Animate execution flow
        const animateExecution = () => {
            if (!particles.parent) return;
            
            const positions = geometry.attributes.position.array;
            const time = Date.now() * 0.001;
            
            for (let i = 0; i < particleCount; i++) {
                const t = (i / particleCount + time * 0.2) % 1;
                const point = curve.getPoint(t);
                
                positions[i * 3] = point.x;
                positions[i * 3 + 1] = point.y;
                positions[i * 3 + 2] = point.z;
            }
            
            geometry.attributes.position.needsUpdate = true;
            requestAnimationFrame(animateExecution);
        };
        
        animateExecution();
    }
    
    getNodeColor(nodeType) {
        const colorMap = {
            'entry': 0x2ecc71,    // Green
            'exit': 0xe74c3c,     // Red
            'condition': 0xf39c12, // Orange
            'loop': 0x9b59b6,     // Purple
            'statement': 0x3498db, // Blue
            'default': 0x95a5a6   // Gray
        };
        
        return colorMap[nodeType] || colorMap.default;
    }
    
    animate(animation) {
        if (animation.type === 'flow_animation') {
            this.flowAnimation(animation.duration, animation.speed);
        }
    }
    
    flowAnimation(duration = 2500, speed = 'medium') {
        const speedMap = {
            'slow': 0.1,
            'medium': 0.3,
            'fast': 0.5
        };
        
        const flowSpeed = speedMap[speed] || speedMap.medium;
        
        // Highlight edges in sequence
        const edges = this.edges.filter(e => e instanceof THREE.Mesh && e.geometry instanceof THREE.TubeGeometry);
        
        const startTime = Date.now();
        
        const animateFlow = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Highlight edges based on progress
            edges.forEach((edge, index) => {
                const edgeProgress = (progress * edges.length - index) * 2;
                const intensity = Math.max(0, Math.min(1, edgeProgress));
                
                edge.material.emissiveIntensity = 0.1 + intensity * 0.5;
                edge.material.opacity = 0.6 + intensity * 0.4;
                edge.material.needsUpdate = true;
                
                // Reset after highlight passes
                if (edgeProgress > 1) {
                    setTimeout(() => {
                        edge.material.emissiveIntensity = 0.1;
                        edge.material.opacity = 0.6;
                        edge.material.needsUpdate = true;
                    }, 500);
                }
            });
            
            if (progress < 1) {
                requestAnimationFrame(animateFlow);
            }
        };
        
        animateFlow();
    }
    
    highlight(elementId) {
        const node = this.nodes.get(elementId);
        if (node) {
            this.highlightNode(node);
        }
    }
    
    highlightNode(node) {
        node.traverse((child) => {
            if (child.material) {
                // Store original values
                if (!child.originalEmissive) {
                    child.originalEmissive = child.material.emissive.clone();
                    child.originalEmissiveIntensity = child.material.emissiveIntensity;
                }
                
                // Highlight
                child.material.emissive.set(0xffff00);
                child.material.emissiveIntensity = 0.8;
                child.material.needsUpdate = true;
                
                // Return to normal after delay
                setTimeout(() => {
                    if (child.originalEmissive) {
                        child.material.emissive.copy(child.originalEmissive);
                        child.material.emissiveIntensity = child.originalEmissiveIntensity;
                        child.material.needsUpdate = true;
                    }
                }, 1000);
            }
        });
    }
    
    show() {
        this.renderer.domElement.style.display = 'block';
    }
    
    hide() {
        this.renderer.domElement.style.display = 'none';
    }
    
    clear() {
        // Remove all nodes
        this.nodes.forEach(node => {
            this.scene.remove(node);
        });
        this.nodes.clear();
        
        // Remove all edges
        this.edges.forEach(edge => {
            this.scene.remove(edge);
        });
        this.edges = [];
    }
    
    reset() {
        this.clear();
    }
    
    resetCamera() {
        this.camera.position.set(0, 15, 30);
        this.controls.reset();
    }
    
    zoom(factor) {
        this.camera.position.multiplyScalar(factor);
        this.controls.update();
    }
    
    takeScreenshot() {
        this.renderer.render(this.scene, this.camera);
        const dataURL = this.renderer.domElement.toDataURL('image/png');
        
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = `cfg_visualization_${Date.now()}.png`;
        link.click();
    }
    
    onWindowResize() {
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }
    
    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        
        // Slow rotation for better viewing
        this.scene.rotation.y += 0.0005;
        
        this.controls.update();
        this.renderer.render(this.scene, this.camera);
    }
    
    dispose() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        
        if (this.controls) {
            this.controls.dispose();
        }
        
        if (this.renderer) {
            this.renderer.dispose();
        }
        
        this.clear();
    }
}