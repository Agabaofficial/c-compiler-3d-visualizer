class CompilationPipeline3D {
    constructor(container) {
        this.container = container;
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.stageObjects = new Map();
        this.connectionObjects = [];
        this.animationId = null;
        this.autoRotate = true;
        this.showLabels = true;
        
        this.init();
        this.animate();
    }
    
    init() {
        // Create scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x0a0a0a);
        
        // Add ambient light
        const ambientLight = new THREE.AmbientLight(0x404040, 0.8);
        this.scene.add(ambientLight);
        
        // Add directional light
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.6);
        directionalLight.position.set(10, 20, 15);
        this.scene.add(directionalLight);
        
        // Add point lights
        const pointLight1 = new THREE.PointLight(0x3498db, 0.5, 100);
        pointLight1.position.set(-20, 10, -10);
        this.scene.add(pointLight1);
        
        const pointLight2 = new THREE.PointLight(0x2ecc71, 0.5, 100);
        pointLight2.position.set(20, 10, 10);
        this.scene.add(pointLight2);
        
        // Create camera
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        this.camera.position.set(0, 10, 30);
        
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
        this.controls.maxPolarAngle = Math.PI * 0.8;
        this.controls.minDistance = 10;
        this.controls.maxDistance = 100;
        
        // Add grid helper
        const gridHelper = new THREE.GridHelper(100, 20, 0x333333, 0x222222);
        this.scene.add(gridHelper);
        
        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }
    
    update(data) {
        this.clear();
        this.createPipeline(data);
    }
    
    createPipeline(data) {
        const stages = [
            { name: 'Lexical Analysis', color: 0x3498db },
            { name: 'Syntax Analysis', color: 0x2ecc71 },
            { name: 'Semantic Analysis', color: 0xe74c3c },
            { name: 'IR Generation', color: 0x9b59b6 },
            { name: 'Optimization', color: 0xf39c12 },
            { name: 'Code Generation', color: 0x1abc9c }
        ];
        
        // Create stage nodes
        stages.forEach((stage, index) => {
            const x = index * 12 - 30;
            const node = this.createStageNode(stage.name, stage.color, x, 0, 0);
            this.stageObjects.set(`stage_${index}`, node);
            
            // Add connection to next stage
            if (index > 0) {
                this.createConnection(index - 1, index);
            }
        });
        
        // Add data flow particles
        this.createDataFlow();
    }
    
    createStageNode(name, color, x, y, z) {
        const group = new THREE.Group();
        
        // Main cylinder
        const geometry = new THREE.CylinderGeometry(3, 3, 4, 16);
        const material = new THREE.MeshPhongMaterial({ 
            color: color,
            emissive: color,
            emissiveIntensity: 0.1,
            shininess: 100,
            transparent: true,
            opacity: 0.9
        });
        const cylinder = new THREE.Mesh(geometry, material);
        cylinder.position.set(0, 2, 0);
        cylinder.castShadow = true;
        cylinder.receiveShadow = true;
        group.add(cylinder);
        
        // Top cap
        const topGeometry = new THREE.CircleGeometry(3, 16);
        const topMaterial = new THREE.MeshPhongMaterial({ 
            color: color,
            emissive: color,
            emissiveIntensity: 0.2
        });
        const top = new THREE.Mesh(topGeometry, topMaterial);
        top.position.set(0, 4, 0);
        top.rotation.x = -Math.PI / 2;
        group.add(top);
        
        // Bottom cap
        const bottom = top.clone();
        bottom.position.set(0, 0, 0);
        bottom.rotation.x = Math.PI / 2;
        group.add(bottom);
        
        // Inner glow
        const innerGeometry = new THREE.CylinderGeometry(2.5, 2.5, 3.8, 16);
        const innerMaterial = new THREE.MeshBasicMaterial({ 
            color: 0xffffff,
            transparent: true,
            opacity: 0.3
        });
        const inner = new THREE.Mesh(innerGeometry, innerMaterial);
        inner.position.set(0, 2, 0);
        group.add(inner);
        
        // Label
        if (this.showLabels) {
            const label = this.createTextLabel(name, 0, 5, 0);
            group.add(label);
        }
        
        // Pulsing animation
        this.addPulseAnimation(cylinder);
        
        group.position.set(x, y, z);
        this.scene.add(group);
        
        return group;
    }
    
    createConnection(fromIndex, toIndex) {
        const fromPos = this.stageObjects.get(`stage_${fromIndex}`).position;
        const toPos = this.stageObjects.get(`stage_${toIndex}`).position;
        
        // Create tube for connection
        const curve = new THREE.CatmullRomCurve3([
            new THREE.Vector3(fromPos.x + 3, fromPos.y + 2, fromPos.z),
            new THREE.Vector3((fromPos.x + toPos.x) / 2, fromPos.y + 3, fromPos.z),
            new THREE.Vector3(toPos.x - 3, toPos.y + 2, toPos.z)
        ]);
        
        const tubeGeometry = new THREE.TubeGeometry(curve, 20, 0.3, 8, false);
        const tubeMaterial = new THREE.MeshPhongMaterial({ 
            color: 0xffffff,
            transparent: true,
            opacity: 0.6,
            emissive: 0xffffff,
            emissiveIntensity: 0.2
        });
        const tube = new THREE.Mesh(tubeGeometry, tubeMaterial);
        this.scene.add(tube);
        this.connectionObjects.push(tube);
        
        // Add flowing particles
        this.addFlowParticles(curve);
    }
    
    createTextLabel(text, x, y, z) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = 512;
        canvas.height = 128;
        
        context.fillStyle = 'rgba(0, 0, 0, 0)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        
        context.font = 'bold 40px Arial';
        context.fillStyle = '#ffffff';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(text, canvas.width / 2, canvas.height / 2);
        
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({ 
            map: texture,
            transparent: true
        });
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(15, 4, 1);
        sprite.position.set(x, y, z);
        
        return sprite;
    }
    
    addPulseAnimation(mesh) {
        const originalScale = mesh.scale.clone();
        let pulseDirection = 1;
        
        const pulse = () => {
            if (!mesh.parent) return;
            
            const scaleFactor = 1 + Math.sin(Date.now() * 0.002) * 0.05;
            mesh.scale.copy(originalScale).multiplyScalar(scaleFactor);
            
            requestAnimationFrame(pulse);
        };
        
        pulse();
    }
    
    addFlowParticles(curve) {
        const particleCount = 20;
        const positions = new Float32Array(particleCount * 3);
        const colors = new Float32Array(particleCount * 3);
        
        for (let i = 0; i < particleCount; i++) {
            const t = i / particleCount;
            const point = curve.getPoint(t);
            
            positions[i * 3] = point.x;
            positions[i * 3 + 1] = point.y;
            positions[i * 3 + 2] = point.z;
            
            // Random color between blue and green
            colors[i * 3] = 0.2 + Math.random() * 0.3;     // R
            colors[i * 3 + 1] = 0.6 + Math.random() * 0.4; // G
            colors[i * 3 + 2] = 0.8 + Math.random() * 0.2; // B
        }
        
        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
        
        const material = new THREE.PointsMaterial({
            size: 0.5,
            vertexColors: true,
            transparent: true,
            opacity: 0.8
        });
        
        const particles = new THREE.Points(geometry, material);
        this.scene.add(particles);
        this.connectionObjects.push(particles);
        
        // Animation
        const animateParticles = () => {
            if (!particles.parent) return;
            
            const positions = geometry.attributes.position.array;
            const time = Date.now() * 0.001;
            
            for (let i = 0; i < particleCount; i++) {
                const t = (i / particleCount + time * 0.1) % 1;
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
    
    createDataFlow() {
        // Create floating data points between stages
        for (let i = 0; i < 50; i++) {
            const geometry = new THREE.SphereGeometry(0.2, 8, 8);
            const material = new THREE.MeshPhongMaterial({
                color: Math.random() > 0.5 ? 0x3498db : 0x2ecc71,
                emissive: Math.random() > 0.5 ? 0x3498db : 0x2ecc71,
                emissiveIntensity: 0.3,
                transparent: true,
                opacity: 0.7
            });
            
            const sphere = new THREE.Mesh(geometry, material);
            
            // Random position along pipeline
            const stageIndex = Math.floor(Math.random() * 6);
            const x = stageIndex * 12 - 30 + (Math.random() - 0.5) * 8;
            const y = Math.random() * 8;
            const z = (Math.random() - 0.5) * 10;
            
            sphere.position.set(x, y, z);
            sphere.castShadow = true;
            
            this.scene.add(sphere);
            this.connectionObjects.push(sphere);
            
            // Add floating animation
            this.addFloatingAnimation(sphere);
        }
    }
    
    addFloatingAnimation(mesh) {
        const startY = mesh.position.y;
        const speed = 0.5 + Math.random() * 0.5;
        const amplitude = 0.5 + Math.random() * 0.5;
        
        const float = () => {
            if (!mesh.parent) return;
            
            const time = Date.now() * 0.001;
            mesh.position.y = startY + Math.sin(time * speed) * amplitude;
            
            requestAnimationFrame(float);
        };
        
        float();
    }
    
    animate(animation) {
        if (animation.type === 'highlight_tokens') {
            this.highlightElements(animation.elements, animation.color);
        }
    }
    
    highlightElements(elementPattern, color) {
        // Convert hex color to THREE.Color
        const threeColor = new THREE.Color(color);
        
        // Find and highlight matching elements
        this.stageObjects.forEach((object, key) => {
            if (key.match(new RegExp(elementPattern.replace('*', '.*')))) {
                this.highlightObject(object, threeColor);
            }
        });
    }
    
    highlightObject(object, color) {
        object.traverse((child) => {
            if (child.material) {
                // Store original color
                if (!child.originalColor) {
                    child.originalColor = child.material.color.clone();
                }
                
                // Animate to highlight color
                const startColor = child.material.color.clone();
                const endColor = color.clone();
                const startTime = Date.now();
                const duration = 1000;
                
                const animateColor = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    child.material.color.lerpColors(startColor, endColor, progress);
                    child.material.needsUpdate = true;
                    
                    if (progress < 1) {
                        requestAnimationFrame(animateColor);
                    } else {
                        // Return to original color after delay
                        setTimeout(() => {
                            const returnStart = child.material.color.clone();
                            const returnDuration = 1000;
                            const returnStartTime = Date.now();
                            
                            const returnColor = () => {
                                const returnElapsed = Date.now() - returnStartTime;
                                const returnProgress = Math.min(returnElapsed / returnDuration, 1);
                                
                                child.material.color.lerpColors(returnStart, child.originalColor, returnProgress);
                                child.material.needsUpdate = true;
                                
                                if (returnProgress < 1) {
                                    requestAnimationFrame(returnColor);
                                }
                            };
                            
                            returnColor();
                        }, 500);
                    }
                };
                
                animateColor();
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
        // Remove stage objects
        this.stageObjects.forEach(object => {
            this.scene.remove(object);
        });
        this.stageObjects.clear();
        
        // Remove connection objects
        this.connectionObjects.forEach(object => {
            this.scene.remove(object);
        });
        this.connectionObjects = [];
    }
    
    reset() {
        this.clear();
    }
    
    resetCamera() {
        this.camera.position.set(0, 10, 30);
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
        link.download = `pipeline_visualization_${Date.now()}.png`;
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
        
        if (this.autoRotate) {
            this.scene.rotation.y += 0.001;
        }
        
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