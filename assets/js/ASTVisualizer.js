class ASTVisualizer {
    constructor(container) {
        this.container = container;
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.nodes = new Map();
        this.edges = [];
        this.animationId = null;
        this.rootNode = null;
        
        this.init();
        this.animate();
    }
    
    init() {
        // Create scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x0a0a0a);
        
        // Add lights
        const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
        this.scene.add(ambientLight);
        
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(10, 20, 15);
        directionalLight.castShadow = true;
        this.scene.add(directionalLight);
        
        // Add hemisphere light for softer illumination
        const hemisphereLight = new THREE.HemisphereLight(0x87CEEB, 0x228B22, 0.3);
        this.scene.add(hemisphereLight);
        
        // Create camera
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        this.camera.position.set(0, 20, 40);
        
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
        this.controls.minDistance = 10;
        this.controls.maxDistance = 200;
        
        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    update(data) {
        this.clear();
        this.createAST(data);
    }
    
    createAST(data) {
        if (!data.nodes || !data.edges) {
            console.error('Invalid AST data');
            return;
        }
        
        // Create nodes
        data.nodes.forEach(node => {
            const nodeObj = this.createASTNode(node);
            this.nodes.set(node.id, nodeObj);
            this.scene.add(nodeObj);
        });
        
        // Create edges
        data.edges.forEach(edge => {
            const edgeObj = this.createASTEdge(edge);
            if (edgeObj) {
                this.edges.push(edgeObj);
                this.scene.add(edgeObj);
            }
        });
        
        // Center the AST
        this.centerAST();
    }
    
    createASTNode(nodeData) {
        const group = new THREE.Group();
        
        // Determine color based on node type
        const color = this.getNodeColor(nodeData.type, nodeData.color);
        
        // Create main sphere
        const geometry = new THREE.SphereGeometry(1.5, 16, 16);
        const material = new THREE.MeshPhongMaterial({
            color: color,
            emissive: color,
            emissiveIntensity: 0.1,
            shininess: 100,
            transparent: true,
            opacity: 0.9
        });
        
        const sphere = new THREE.Mesh(geometry, material);
        sphere.castShadow = true;
        sphere.receiveShadow = true;
        group.add(sphere);
        
        // Add inner glow
        const innerGeometry = new THREE.SphereGeometry(1.3, 12, 12);
        const innerMaterial = new THREE.MeshBasicMaterial({
            color: 0xffffff,
            transparent: true,
            opacity: 0.3
        });
        const innerSphere = new THREE.Mesh(innerGeometry, innerMaterial);
        group.add(innerSphere);
        
        // Position the node
        group.position.set(
            nodeData.position.x || 0,
            nodeData.position.y || 0,
            nodeData.position.z || 0
        );
        
        // Add label
        this.addNodeLabel(group, nodeData);
        
        // Add pulsating animation
        this.addNodePulse(sphere);
        
        return group;
    }
    
    createASTEdge(edgeData) {
        const fromNode = this.nodes.get(edgeData.from);
        const toNode = this.nodes.get(edgeData.to);
        
        if (!fromNode || !toNode) {
            console.warn(`Missing nodes for edge: ${edgeData.from} -> ${edgeData.to}`);
            return null;
        }
        
        const fromPos = fromNode.position;
        const toPos = toNode.position;
        
        // Create a curve between nodes
        const curve = new THREE.CatmullRomCurve3([
            new THREE.Vector3(fromPos.x, fromPos.y, fromPos.z),
            new THREE.Vector3(
                (fromPos.x + toPos.x) / 2,
                (fromPos.y + toPos.y) / 2 + 2,
                (fromPos.z + toPos.z) / 2
            ),
            new THREE.Vector3(toPos.x, toPos.y, toPos.z)
        ]);
        
        const tubeGeometry = new THREE.TubeGeometry(curve, 20, 0.1, 8, false);
        const tubeMaterial = new THREE.MeshPhongMaterial({
            color: 0x3498db,
            transparent: true,
            opacity: 0.6,
            emissive: 0x3498db,
            emissiveIntensity: 0.1
        });
        
        const tube = new THREE.Mesh(tubeGeometry, tubeMaterial);
        
        // Add flowing particles along the edge
        this.addEdgeParticles(curve);
        
        return tube;
    }
    
    addNodeLabel(group, nodeData) {
        const text = nodeData.value || nodeData.name || nodeData.type;
        if (!text) return;
        
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = 256;
        canvas.height = 128;
        
        // Clear canvas
        context.fillStyle = 'rgba(0, 0, 0, 0)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        
        // Draw text
        context.font = 'bold 24px Arial';
        context.fillStyle = '#ffffff';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        
        // Wrap text if too long
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
        const lineHeight = 30;
        const startY = (canvas.height - (lines.length * lineHeight)) / 2;
        
        lines.forEach((line, index) => {
            context.fillText(line, canvas.width / 2, startY + index * lineHeight);
        });
        
        // Create texture
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({
            map: texture,
            transparent: true
        });
        
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(8, 4, 1);
        sprite.position.set(0, 2.5, 0);
        group.add(sprite);
    }
    
    addNodePulse(mesh) {
        const originalScale = mesh.scale.clone();
        const pulseSpeed = 0.002 + Math.random() * 0.001;
        
        const pulse = () => {
            if (!mesh.parent) return;
            
            const scaleFactor = 1 + Math.sin(Date.now() * pulseSpeed) * 0.05;
            mesh.scale.copy(originalScale).multiplyScalar(scaleFactor);
            
            // Pulsing emissive intensity
            const emissiveIntensity = 0.1 + Math.sin(Date.now() * pulseSpeed) * 0.05;
            mesh.material.emissiveIntensity = emissiveIntensity;
            
            requestAnimationFrame(pulse);
        };
        
        pulse();
    }
    
    addEdgeParticles(curve) {
        const particleCount = 10;
        const positions = new Float32Array(particleCount * 3);
        const colors = new Float32Array(particleCount * 3);
        
        for (let i = 0; i < particleCount; i++) {
            const t = i / particleCount;
            const point = curve.getPoint(t);
            
            positions[i * 3] = point.x;
            positions[i * 3 + 1] = point.y;
            positions[i * 3 + 2] = point.z;
            
            colors[i * 3] = 0.2;     // R
            colors[i * 3 + 1] = 0.6; // G
            colors[i * 3 + 2] = 0.9; // B
        }
        
        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
        
        const material = new THREE.PointsMaterial({
            size: 0.3,
            vertexColors: true,
            transparent: true,
            opacity: 0.7
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
                const t = (i / particleCount + time * 0.2) % 1;
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
    
    getNodeColor(nodeType, specifiedColor) {
        if (specifiedColor) {
            return new THREE.Color(specifiedColor);
        }
        
        const colorMap = {
            'Program': 0x2c3e50,
            'FunctionDeclaration': 0x3498db,
            'VariableDeclaration': 0x2ecc71,
            'IfStatement': 0xe74c3c,
            'ForStatement': 0x9b59b6,
            'WhileStatement': 0xf39c12,
            'CallExpression': 0x1abc9c,
            'Identifier': 0x34495e,
            'Literal': 0x7f8c8d,
            'BinaryExpression': 0xd35400,
            'ReturnStatement': 0x8e44ad,
            'ast_node': 0x3498db,
            'token': 0xf39c12,
            'symbol': 0x2ecc71
        };
        
        return new THREE.Color(colorMap[nodeType] || 0x3498db);
    }
    
    centerAST() {
        if (this.nodes.size === 0) return;
        
        // Calculate bounding box
        let minX = Infinity, maxX = -Infinity;
        let minY = Infinity, maxY = -Infinity;
        let minZ = Infinity, maxZ = -Infinity;
        
        this.nodes.forEach(node => {
            const pos = node.position;
            minX = Math.min(minX, pos.x);
            maxX = Math.max(maxX, pos.x);
            minY = Math.min(minY, pos.y);
            maxY = Math.max(maxY, pos.y);
            minZ = Math.min(minZ, pos.z);
            maxZ = Math.max(maxZ, pos.z);
        });
        
        // Calculate center
        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;
        const centerZ = (minZ + maxZ) / 2;
        
        // Move all nodes to center around origin
        this.nodes.forEach(node => {
            node.position.x -= centerX;
            node.position.y -= centerY;
            node.position.z -= centerZ;
        });
        
        // Update edges
        this.edges.forEach(edge => {
            this.scene.remove(edge);
        });
        this.edges = [];
        
        // Recreate edges with new positions
        // (In a real implementation, you'd store edge data and recreate them)
    }
    
    animate(animation) {
        if (animation.type === 'build_ast') {
            this.buildASTAnimation(animation.duration, animation.direction);
        }
    }
    
    buildASTAnimation(duration = 3000, direction = 'top_down') {
        const nodes = Array.from(this.nodes.values());
        
        if (direction === 'top_down') {
            // Sort by Y position (top to bottom)
            nodes.sort((a, b) => b.position.y - a.position.y);
        } else {
            // Random order
            nodes.sort(() => Math.random() - 0.5);
        }
        
        const startTime = Date.now();
        
        const animateBuild = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Calculate how many nodes should be visible
            const visibleCount = Math.floor(progress * nodes.length);
            
            // Show nodes progressively
            nodes.forEach((node, index) => {
                const visible = index < visibleCount;
                node.visible = visible;
                
                if (visible) {
                    // Scale up animation for newly visible nodes
                    if (index === visibleCount - 1) {
                        const scale = 1 + (1 - (elapsed % 1000) / 1000) * 0.5;
                        node.scale.setScalar(scale);
                    }
                }
            });
            
            if (progress < 1) {
                requestAnimationFrame(animateBuild);
            } else {
                // Reset scales
                nodes.forEach(node => {
                    node.scale.setScalar(1);
                });
            }
        };
        
        animateBuild();
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
                // Store original emissive
                if (!child.originalEmissive) {
                    child.originalEmissive = child.material.emissive.clone();
                    child.originalEmissiveIntensity = child.material.emissiveIntensity;
                }
                
                // Highlight
                child.material.emissive.set(0xffff00);
                child.material.emissiveIntensity = 0.5;
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
        this.camera.position.set(0, 20, 40);
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
        link.download = `ast_visualization_${Date.now()}.png`;
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