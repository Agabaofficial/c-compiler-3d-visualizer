class MemoryVisualizer {
    constructor(container) {
        this.container = container;
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.memoryBlocks = new Map();
        this.stackFrames = [];
        this.heapBlocks = [];
        this.animationId = null;
        
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
        directionalLight.position.set(10, 20, 10);
        directionalLight.castShadow = true;
        this.scene.add(directionalLight);
        
        // Create camera
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        this.camera.position.set(20, 15, 20);
        
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
        this.controls.maxDistance = 100;
        
        // Create coordinate axes
        this.createAxes();
        
        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    update(data) {
        this.clear();
        this.createMemoryLayout(data);
    }
    
    createMemoryLayout(data) {
        // Create stack memory
        this.createStackMemory();
        
        // Create heap memory
        this.createHeapMemory();
        
        // Create static/global memory
        this.createStaticMemory();
        
        // Create registers
        this.createRegisters();
        
        // Create memory access animation
        this.createMemoryAccessAnimation();
    }
    
    createStackMemory() {
        const stackGroup = new THREE.Group();
        stackGroup.position.set(-15, 0, 0);
        
        // Stack grows downward
        const stackFrames = [
            { name: 'main()', variables: ['a=5', 'b=10', 'sum=15', 'i=0'], color: 0x3498db },
            { name: 'printf()', variables: ['format', 'value'], color: 0x2ecc71 },
            { name: 'for loop', variables: ['i', 'limit=3'], color: 0xe74c3c }
        ];
        
        let yPosition = 0;
        stackFrames.forEach((frame, index) => {
            const frameHeight = 3 + frame.variables.length * 0.5;
            const frameObj = this.createStackFrame(frame, frameHeight, yPosition);
            stackGroup.add(frameObj);
            
            yPosition -= frameHeight + 0.5;
        });
        
        // Add stack pointer
        this.createStackPointer(stackGroup, yPosition);
        
        this.scene.add(stackGroup);
        this.stackFrames.push(stackGroup);
    }
    
    createStackFrame(frame, height, y) {
        const group = new THREE.Group();
        
        // Frame outline
        const frameGeometry = new THREE.BoxGeometry(8, height, 2);
        const frameMaterial = new THREE.MeshPhongMaterial({
            color: frame.color,
            transparent: true,
            opacity: 0.3,
            side: THREE.DoubleSide,
            wireframe: true
        });
        
        const frameMesh = new THREE.Mesh(frameGeometry, frameMaterial);
        frameMesh.position.set(0, y + height/2, 0);
        group.add(frameMesh);
        
        // Frame name plate
        this.createMemoryLabel(`${frame.name}`, 0, y + height - 0.5, 1.1, 0xffffff, group);
        
        // Variables
        frame.variables.forEach((variable, index) => {
            const varY = y + height - 1.5 - index * 0.7;
            this.createMemoryCell(variable, 0, varY, 0, 0.5, 0.5, 0.2, frame.color, group);
        });
        
        return group;
    }
    
    createHeapMemory() {
        const heapGroup = new THREE.Group();
        heapGroup.position.set(5, 0, 0);
        
        // Heap blocks (dynamically allocated)
        const heapBlocks = [
            { address: '0x1000', size: 16, content: 'array[10]', color: 0x9b59b6 },
            { address: '0x1100', size: 8, content: 'struct Node', color: 0xf39c12 },
            { address: '0x1200', size: 4, content: 'int*', color: 0x1abc9c },
            { address: '0x1300', size: 32, content: 'buffer[32]', color: 0x34495e }
        ];
        
        let xOffset = 0;
        heapBlocks.forEach((block, index) => {
            const blockObj = this.createHeapBlock(block, xOffset, 0);
            heapGroup.add(blockObj);
            this.heapBlocks.push(blockObj);
            
            xOffset += block.size / 4 + 2;
        });
        
        // Add heap manager
        this.createHeapManager(heapGroup, xOffset + 5);
        
        this.scene.add(heapGroup);
    }
    
    createHeapBlock(block, x, y) {
        const group = new THREE.Group();
        
        // Block body
        const blockGeometry = new THREE.BoxGeometry(block.size / 4, 2, 2);
        const blockMaterial = new THREE.MeshPhongMaterial({
            color: block.color,
            emissive: block.color,
            emissiveIntensity: 0.1,
            transparent: true,
            opacity: 0.7
        });
        
        const blockMesh = new THREE.Mesh(blockGeometry, blockMaterial);
        blockMesh.castShadow = true;
        blockMesh.receiveShadow = true;
        blockMesh.position.set(x, y + 1, 0);
        group.add(blockMesh);
        
        // Address label
        this.createMemoryLabel(block.address, x, y + 2.5, 1.1, 0xffffff, group);
        
        // Content label
        this.createMemoryLabel(block.content, x, y, 1.1, 0xffffff, group);
        
        // Size indicator
        this.createMemoryLabel(`${block.size} bytes`, x, y - 1, 1.1, 0xcccccc, group);
        
        group.position.set(x, y, 0);
        
        // Add allocation/deallocation animation
        this.addHeapBlockAnimation(blockMesh);
        
        return group;
    }
    
    createStaticMemory() {
        const staticGroup = new THREE.Group();
        staticGroup.position.set(-5, 10, 0);
        
        // Static/global variables
        const staticVars = [
            { name: 'global_counter', value: '42', type: 'int', color: 0x8e44ad },
            { name: 'PI', value: '3.14159', type: 'const double', color: 0x16a085 },
            { name: 'errors', value: '0', type: 'static int', color: 0xc0392b }
        ];
        
        staticVars.forEach((variable, index) => {
            const varObj = this.createStaticVariable(variable, index * 4);
            staticGroup.add(varObj);
        });
        
        this.scene.add(staticGroup);
    }
    
    createStaticVariable(variable, x) {
        const group = new THREE.Group();
        
        // Variable container
        const varGeometry = new THREE.BoxGeometry(3, 2, 2);
        const varMaterial = new THREE.MeshPhongMaterial({
            color: variable.color,
            transparent: true,
            opacity: 0.8,
            emissive: variable.color,
            emissiveIntensity: 0.2
        });
        
        const varMesh = new THREE.Mesh(varGeometry, varMaterial);
        varMesh.castShadow = true;
        group.add(varMesh);
        
        // Variable name
        this.createMemoryLabel(variable.name, 0, 0.5, 1.1, 0xffffff, group, 0.3);
        
        // Variable type
        this.createMemoryLabel(variable.type, 0, -0.2, 1.1, 0xcccccc, group, 0.2);
        
        // Variable value
        this.createMemoryLabel(`= ${variable.value}`, 0, -0.9, 1.1, 0xffff00, group, 0.25);
        
        group.position.set(x, 0, 0);
        
        // Add persistent glow
        this.addStaticVariableGlow(varMesh);
        
        return group;
    }
    
    createRegisters() {
        const registerGroup = new THREE.Group();
        registerGroup.position.set(15, 5, 0);
        
        // CPU Registers
        const registers = [
            { name: 'EAX', value: '0x0', color: 0xe74c3c },
            { name: 'EBX', value: '0x1000', color: 0x3498db },
            { name: 'ECX', value: '0x3', color: 0x2ecc71 },
            { name: 'EDX', value: '0x0', color: 0xf39c12 },
            { name: 'ESI', value: '0x1100', color: 0x9b59b6 },
            { name: 'EDI', value: '0x1200', color: 0x1abc9c },
            { name: 'EBP', value: '0xFFF0', color: 0x34495e },
            { name: 'ESP', value: '0xFFD0', color: 0x7f8c8d }
        ];
        
        // Arrange in a circle
        const radius = 6;
        registers.forEach((register, index) => {
            const angle = (index / registers.length) * Math.PI * 2;
            const x = Math.cos(angle) * radius;
            const z = Math.sin(angle) * radius;
            
            const registerObj = this.createRegister(register, x, 0, z);
            registerGroup.add(registerObj);
        });
        
        // Add CPU core in center
        this.createCPUCore(registerGroup);
        
        this.scene.add(registerGroup);
    }
    
    createRegister(register, x, y, z) {
        const group = new THREE.Group();
        
        // Register hexagon
        const hexGeometry = new THREE.CylinderGeometry(0.8, 0.8, 0.5, 6);
        const hexMaterial = new THREE.MeshPhongMaterial({
            color: register.color,
            emissive: register.color,
            emissiveIntensity: 0.3,
            shininess: 100
        });
        
        const hex = new THREE.Mesh(hexGeometry, hexMaterial);
        hex.castShadow = true;
        group.add(hex);
        
        // Register name
        this.createMemoryLabel(register.name, 0, 0.4, 0.6, 0xffffff, group, 0.25);
        
        // Register value
        this.createMemoryLabel(register.value, 0, -0.4, 0.6, 0xffff00, group, 0.2);
        
        group.position.set(x, y, z);
        
        // Add rotation animation
        this.addRegisterRotation(group);
        
        return group;
    }
    
    createCPUCore(group) {
        // CPU core sphere
        const coreGeometry = new THREE.SphereGeometry(1.5, 32, 32);
        const coreMaterial = new THREE.MeshPhongMaterial({
            color: 0xff0000,
            emissive: 0xff0000,
            emissiveIntensity: 0.5,
            transparent: true,
            opacity: 0.8
        });
        
        const core = new THREE.Mesh(coreGeometry, coreMaterial);
        group.add(core);
        
        // Add pulsating animation
        this.addCPUPulse(core);
        
        // Add processing rays
        this.addProcessingRays(group);
    }
    
    createAxes() {
        // X axis (red)
        const xAxis = new THREE.ArrowHelper(
            new THREE.Vector3(1, 0, 0),
            new THREE.Vector3(-20, -5, -20),
            10,
            0xff0000,
            1,
            0.5
        );
        this.scene.add(xAxis);
        
        // Y axis (green)
        const yAxis = new THREE.ArrowHelper(
            new THREE.Vector3(0, 1, 0),
            new THREE.Vector3(-20, -5, -20),
            10,
            0x00ff00,
            1,
            0.5
        );
        this.scene.add(yAxis);
        
        // Z axis (blue)
        const zAxis = new THREE.ArrowHelper(
            new THREE.Vector3(0, 0, 1),
            new THREE.Vector3(-20, -5, -20),
            10,
            0x0000ff,
            1,
            0.5
        );
        this.scene.add(zAxis);
        
        // Axis labels
        this.createMemoryLabel('Stack', -25, -5, -20, 0xff0000, this.scene, 0.5);
        this.createMemoryLabel('Heap', 25, -5, -20, 0x00ff00, this.scene, 0.5);
        this.createMemoryLabel('Height', -20, 15, -20, 0x0000ff, this.scene, 0.5);
    }
    
    createMemoryLabel(text, x, y, z, color, parent, scale = 0.5) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = 256;
        canvas.height = 128;
        
        context.fillStyle = 'rgba(0, 0, 0, 0)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        
        context.font = 'bold 24px Arial';
        context.fillStyle = `#${color.toString(16).padStart(6, '0')}`;
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(text, canvas.width / 2, canvas.height / 2);
        
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({
            map: texture,
            transparent: true
        });
        
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(scale * 5, scale * 2.5, 1);
        sprite.position.set(x, y, z);
        parent.add(sprite);
        
        return sprite;
    }
    
    createMemoryCell(text, x, y, z, width, height, depth, color, parent) {
        const group = new THREE.Group();
        
        // Cell container
        const cellGeometry = new THREE.BoxGeometry(width, height, depth);
        const cellMaterial = new THREE.MeshPhongMaterial({
            color: color,
            transparent: true,
            opacity: 0.5
        });
        
        const cell = new THREE.Mesh(cellGeometry, cellMaterial);
        group.add(cell);
        
        // Cell label
        const label = this.createMemoryLabel(text, 0, 0, depth/2 + 0.1, 0xffffff, group, 0.15);
        
        group.position.set(x, y, z);
        parent.add(group);
        
        return group;
    }
    
    createStackPointer(parent, yPosition) {
        // Stack pointer arrow
        const pointerGeometry = new THREE.ConeGeometry(0.3, 1, 8);
        const pointerMaterial = new THREE.MeshPhongMaterial({
            color: 0xff0000,
            emissive: 0xff0000,
            emissiveIntensity: 0.5
        });
        
        const pointer = new THREE.Mesh(pointerGeometry, pointerMaterial);
        pointer.position.set(5, yPosition - 1, 0);
        pointer.rotation.z = Math.PI / 2;
        parent.add(pointer);
        
        // Add bouncing animation
        this.addBouncingAnimation(pointer);
        
        return pointer;
    }
    
    createHeapManager(parent, x) {
        const group = new THREE.Group();
        
        // Heap manager structure
        const managerGeometry = new THREE.BoxGeometry(4, 6, 2);
        const managerMaterial = new THREE.MeshPhongMaterial({
            color: 0x2c3e50,
            wireframe: true,
            transparent: true,
            opacity: 0.5
        });
        
        const manager = new THREE.Mesh(managerGeometry, managerMaterial);
        group.add(manager);
        
        // Manager label
        this.createMemoryLabel('Heap Manager', 0, 3, 1.1, 0xffffff, group);
        
        // Free list
        this.createMemoryLabel('Free List:', 0, 1, 1.1, 0x2ecc71, group, 0.2);
        this.createMemoryLabel('Allocated:', 0, -1, 1.1, 0xe74c3c, group, 0.2);
        this.createMemoryLabel('Fragmentation:', 0, -3, 1.1, 0xf39c12, group, 0.2);
        
        group.position.set(x, 3, 0);
        parent.add(group);
        
        return group;
    }
    
    addHeapBlockAnimation(mesh) {
        const originalScale = mesh.scale.clone();
        const originalPosition = mesh.position.clone();
        
        // Simulate allocation/deallocation
        setInterval(() => {
            if (Math.random() > 0.7) { // Random allocation/deallocation
                // Bounce animation
                const startTime = Date.now();
                const bounce = () => {
                    const elapsed = Date.now() - startTime;
                    if (elapsed > 1000) return;
                    
                    const progress = elapsed / 1000;
                    const bounceHeight = Math.sin(progress * Math.PI) * 0.5;
                    
                    mesh.position.y = originalPosition.y + bounceHeight;
                    mesh.scale.copy(originalScale).multiplyScalar(1 + bounceHeight * 0.1);
                    
                    requestAnimationFrame(bounce);
                };
                
                bounce();
            }
        }, 3000);
    }
    
    addStaticVariableGlow(mesh) {
        const pulse = () => {
            if (!mesh.parent) return;
            
            const intensity = 0.2 + Math.sin(Date.now() * 0.001) * 0.1;
            mesh.material.emissiveIntensity = intensity;
            mesh.material.needsUpdate = true;
            
            requestAnimationFrame(pulse);
        };
        
        pulse();
    }
    
    addRegisterRotation(group) {
        const rotate = () => {
            if (!group.parent) return;
            
            group.rotation.y += 0.01;
            requestAnimationFrame(rotate);
        };
        
        rotate();
    }
    
    addCPUPulse(mesh) {
        const originalScale = mesh.scale.clone();
        
        const pulse = () => {
            if (!mesh.parent) return;
            
            const scale = 1 + Math.sin(Date.now() * 0.003) * 0.1;
            mesh.scale.copy(originalScale).multiplyScalar(scale);
            
            const intensity = 0.3 + Math.sin(Date.now() * 0.002) * 0.2;
            mesh.material.emissiveIntensity = intensity;
            mesh.material.needsUpdate = true;
            
            requestAnimationFrame(pulse);
        };
        
        pulse();
    }
    
    addProcessingRays(group) {
        const rayCount = 8;
        const rays = [];
        
        for (let i = 0; i < rayCount; i++) {
            const rayGeometry = new THREE.ConeGeometry(0.1, 3, 8);
            const rayMaterial = new THREE.MeshBasicMaterial({
                color: 0xffff00,
                transparent: true,
                opacity: 0.6
            });
            
            const ray = new THREE.Mesh(rayGeometry, rayMaterial);
            ray.rotation.x = Math.PI / 2;
            
            const angle = (i / rayCount) * Math.PI * 2;
            ray.position.set(
                Math.cos(angle) * 2,
                Math.sin(angle) * 2,
                0
            );
            
            group.add(ray);
            rays.push(ray);
        }
        
        // Animate rays
        const animateRays = () => {
            if (!group.parent) return;
            
            const time = Date.now() * 0.001;
            rays.forEach((ray, i) => {
                const angle = (i / rayCount) * Math.PI * 2 + time;
                const distance = 2 + Math.sin(time * 2 + i) * 0.5;
                
                ray.position.set(
                    Math.cos(angle) * distance,
                    Math.sin(angle) * distance,
                    0
                );
                
                ray.material.opacity = 0.3 + Math.sin(time * 3 + i) * 0.3;
                ray.material.needsUpdate = true;
            });
            
            requestAnimationFrame(animateRays);
        };
        
        animateRays();
    }
    
    addBouncingAnimation(mesh) {
        const originalY = mesh.position.y;
        const bounce = () => {
            if (!mesh.parent) return;
            
            const bounceHeight = Math.sin(Date.now() * 0.003) * 0.3;
            mesh.position.y = originalY + bounceHeight;
            
            requestAnimationFrame(bounce);
        };
        
        bounce();
    }
    
    createMemoryAccessAnimation() {
        // Create data flow between memory areas
        const paths = [
            { from: [-15, 2, 0], to: [15, 5, 0], color: 0x3498db }, // Stack to Registers
            { from: [5, 2, 0], to: [15, 5, 0], color: 0x2ecc71 },   // Heap to Registers
            { from: [-5, 10, 0], to: [15, 5, 0], color: 0x9b59b6 }, // Static to Registers
            { from: [15, 5, 0], to: [5, 2, 0], color: 0xe74c3c }    // Registers to Heap
        ];
        
        paths.forEach(path => {
            this.createDataFlowPath(path.from, path.to, path.color);
        });
    }
    
    createDataFlowPath(from, to, color) {
        const curve = new THREE.CatmullRomCurve3([
            new THREE.Vector3(from[0], from[1], from[2]),
            new THREE.Vector3(
                (from[0] + to[0]) / 2,
                Math.max(from[1], to[1]) + 5,
                (from[2] + to[2]) / 2
            ),
            new THREE.Vector3(to[0], to[1], to[2])
        ]);
        
        // Create flowing particles
        this.createFlowParticles(curve, color);
    }
    
    createFlowParticles(curve, color) {
        const particleCount = 20;
        const positions = new Float32Array(particleCount * 3);
        const colorsArray = new Float32Array(particleCount * 3);
        
        const rgb = new THREE.Color(color);
        
        for (let i = 0; i < particleCount; i++) {
            const t = i / particleCount;
            const point = curve.getPoint(t);
            
            positions[i * 3] = point.x;
            positions[i * 3 + 1] = point.y;
            positions[i * 3 + 2] = point.z;
            
            colorsArray[i * 3] = rgb.r;
            colorsArray[i * 3 + 1] = rgb.g;
            colorsArray[i * 3 + 2] = rgb.b;
        }
        
        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colorsArray, 3));
        
        const material = new THREE.PointsMaterial({
            size: 0.3,
            vertexColors: true,
            transparent: true,
            opacity: 0.7
        });
        
        const particles = new THREE.Points(geometry, material);
        this.scene.add(particles);
        
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
    
    animate(animation) {
        // Handle memory-specific animations
        if (animation.type === 'memory_access') {
            this.simulateMemoryAccess(animation.address);
        }
    }
    
    simulateMemoryAccess(address) {
        // Highlight the accessed memory location
        this.heapBlocks.forEach(block => {
            // Simple highlight animation
            block.traverse(child => {
                if (child.material) {
                    const originalEmissive = child.material.emissive.clone();
                    const originalIntensity = child.material.emissiveIntensity;
                    
                    child.material.emissive.set(0xffff00);
                    child.material.emissiveIntensity = 0.8;
                    child.material.needsUpdate = true;
                    
                    setTimeout(() => {
                        child.material.emissive.copy(originalEmissive);
                        child.material.emissiveIntensity = originalIntensity;
                        child.material.needsUpdate = true;
                    }, 500);
                }
            });
        });
    }
    
    highlight(elementId) {
        // Find and highlight memory element
        // Implementation depends on how elements are identified
    }
    
    show() {
        this.renderer.domElement.style.display = 'block';
    }
    
    hide() {
        this.renderer.domElement.style.display = 'none';
    }
    
    clear() {
        // Clear all memory visualizations
        this.stackFrames.forEach(frame => {
            this.scene.remove(frame);
        });
        this.stackFrames = [];
        
        this.heapBlocks.forEach(block => {
            this.scene.remove(block);
        });
        this.heapBlocks = [];
        
        // Remove other memory groups
        const memoryGroups = this.scene.children.filter(child => 
            child instanceof THREE.Group && 
            (child.position.x !== undefined)
        );
        
        memoryGroups.forEach(group => {
            this.scene.remove(group);
        });
    }
    
    reset() {
        this.clear();
    }
    
    resetCamera() {
        this.camera.position.set(20, 15, 20);
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
        link.download = `memory_visualization_${Date.now()}.png`;
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
        this.scene.rotation.y += 0.0003;
        
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