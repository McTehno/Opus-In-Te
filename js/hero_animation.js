/**
 * Golden Flowers Hero Animation
 * Renders realistic vector-style golden roses and tulips on an HTML5 Canvas.
 * Features:
 * - Blooming animation on load.
 * - Interactive blooming effect on scroll.
 */

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width, height;
    let flowers = [];
    let scrollY = 0;

    // Configuration
    const config = {
        flowerCount: 20,
        color: '#C5A76A', // --soft-gold
        growthSpeed: 0.015,
        maxStemLength: 350,
        flowerSize: 35
    };

    // Resize handling
    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        initFlowers();
    }

    window.addEventListener('resize', resize);
    window.addEventListener('scroll', () => {
        scrollY = window.scrollY;
    });

    // Flower Class
    class Flower {
        constructor(x) {
            this.initialX = x;
            this.init();
        }

        init() {
            this.x = this.initialX;
            this.y = height + Math.random() * 100; // Start below screen
            
            // Indent logic: Shorter in the middle to reveal text
            const centerX = width / 2;
            const distFromCenter = Math.abs(this.x - centerX);
            const normalizedDist = distFromCenter / (width / 2); // 0 (center) to 1 (edge)
            
            // Create a U-shape valley. 
            // Flowers at center will be ~25% height, at edges ~85% height
            const valleyFactor = 0.25 + 0.75 * Math.pow(normalizedDist, 1.5); 
            const randomHeight = height * 0.75 * valleyFactor * (0.8 + Math.random() * 0.4);
            
            this.targetY = height - randomHeight;
            
            // Control points for the stem curve
            // Reduced randomness in X control points to prevent wild crossing
            this.controlX1 = this.x + (Math.random() - 0.5) * 100;
            this.controlY1 = height - (height - this.targetY) * 0.3;
            this.controlX2 = this.x + (Math.random() - 0.5) * 100;
            this.controlY2 = height - (height - this.targetY) * 0.6;
            
            this.progress = 0;
            this.speed = config.growthSpeed * (0.5 + Math.random() * 0.5);
            
            // Swaying properties
            this.angle = 0;
            this.swaySpeed = 0.001 + Math.random() * 0.002;
            this.swayOffset = Math.random() * Math.PI * 2;
            
            this.size = config.flowerSize * (0.8 + Math.random() * 0.6);
            this.type = Math.random() > 0.5 ? 'rose' : 'tulip';
            this.alpha = 0.2 + Math.random() * 0.6; // Random opacity for depth
            
            // Random rotation for the flower head
            this.headRotation = (Math.random() - 0.5) * 0.3;
        }

        update() {
            // Growth
            if (this.progress < 1) {
                this.progress += this.speed;
                if (this.progress > 1) this.progress = 1;
            }

            // Swaying (wind effect)
            this.angle = Math.sin(Date.now() * this.swaySpeed + this.swayOffset) * 0.05;
        }

        draw(ctx, scrollY) {
            // Parallax effect: move flowers slightly based on scroll
            // Also, use scrollY to influence the "bloom" state
            const parallaxY = scrollY * 0.3 * (this.alpha); 
            const scrollBloom = Math.min(1, scrollY / 500); // 0 to 1 based on scroll

            ctx.save();
            ctx.translate(0, parallaxY);
            ctx.strokeStyle = config.color;
            ctx.fillStyle = config.color;
            ctx.globalAlpha = this.alpha;
            ctx.lineWidth = 1.5;

            // --- Draw Stem ---
            const t = this.progress;
            const p0 = {x: this.x, y: this.y};
            const p1 = {x: this.controlX1, y: this.controlY1};
            const p2 = {x: this.controlX2, y: this.controlY2};
            // Tip of the stem sways
            const tipX = this.x + Math.sin(this.angle) * 30 + (this.controlX2 - this.x)*0.2; 
            const p3 = {x: tipX, y: this.targetY}; 

            // Calculate current tip position using Bezier interpolation
            const q0 = lerpPoint(p0, p1, t);
            const q1 = lerpPoint(p1, p2, t);
            const q2 = lerpPoint(p2, p3, t);
            const r0 = lerpPoint(q0, q1, t);
            const r1 = lerpPoint(q1, q2, t);
            const s0 = lerpPoint(r0, r1, t); // Current tip position

            ctx.beginPath();
            ctx.moveTo(this.x, this.y);
            ctx.quadraticCurveTo(r0.x, r0.y, s0.x, s0.y);
            ctx.stroke();

            // --- Draw Leaves (if grown enough) ---
            if (this.progress > 0.4) {
                this.drawLeaf(ctx, r0, 1, this.progress);
            }
            if (this.progress > 0.7) {
                this.drawLeaf(ctx, lerpPoint(p0, p1, 0.5), -1, this.progress);
            }

            // --- Draw Flower Head ---
            if (this.progress > 0.2) {
                // Bloom factor combines initial growth and scroll
                // Initial growth gives 0->1. Scroll adds extra "opening".
                const growthBloom = Math.max(0, (this.progress - 0.2) / 0.8);
                const totalBloom = Math.min(1, growthBloom + scrollBloom * 0.5);

                ctx.translate(s0.x, s0.y);
                
                // Calculate angle of the stem tip to align flower
                const stemAngle = Math.atan2(s0.y - r0.y, s0.x - r0.x);
                // Rotate so -Y (up) aligns with stem direction
                ctx.rotate(stemAngle + Math.PI / 2);
                
                // Add slight random rotation
                ctx.rotate(this.headRotation);

                ctx.scale(growthBloom, growthBloom); // Scale up as it grows
                
                if (this.type === 'rose') {
                    this.drawRose(ctx, totalBloom);
                } else {
                    this.drawTulip(ctx, totalBloom);
                }
            }

            ctx.restore();
        }

        drawLeaf(ctx, pos, dir, progress) {
            const size = 15 * progress;
            ctx.save();
            ctx.translate(pos.x, pos.y);
            ctx.rotate(dir * 0.5 + this.angle);
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.quadraticCurveTo(dir * size, -size/2, dir * size * 2, -size);
            ctx.quadraticCurveTo(dir * size, size/2, 0, 0);
            ctx.stroke();
            // ctx.fill();
            ctx.restore();
        }

        drawRose(ctx, bloom) {
            const s = this.size;
            
            // Draw Sepal/Base to connect to stem
            ctx.beginPath();
            ctx.moveTo(0, 0);
            // Small cup shape at base
            ctx.quadraticCurveTo(s*0.15, -s*0.1, 0, -s*0.2);
            ctx.quadraticCurveTo(-s*0.15, -s*0.1, 0, 0);
            ctx.stroke();

            // Move up for the flower head
            ctx.translate(0, -s * 0.15);

            // Inner bud (spiral)
            ctx.beginPath();
            const spiralRevs = 3;
            for (let i = 0; i < 50; i++) {
                const angle = i * 0.2;
                const r = (i / 50) * (s * 0.4) * (1 - bloom * 0.3); // Unwinds slightly
                const x = Math.cos(angle) * r;
                const y = Math.sin(angle) * r - s*0.2;
                if (i===0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }
            ctx.stroke();

            // Outer petals
            const petalCount = 5;
            const layerCount = 2;
            
            for (let l = 0; l < layerCount; l++) {
                const layerScale = 0.6 + l * 0.4;
                for (let i = 0; i < petalCount; i++) {
                    ctx.save();
                    // Rotate for petal position
                    ctx.rotate((i / petalCount) * Math.PI * 2 + (l * Math.PI / petalCount));
                    
                    // Bloom effect: petals rotate outward
                    const openAngle = bloom * 0.5; 
                    ctx.rotate(openAngle);

                    ctx.beginPath();
                    ctx.moveTo(0, -s * 0.2);
                    
                    // Petal shape
                    ctx.bezierCurveTo(
                        s * 0.5 * layerScale, -s * 0.8 * layerScale, 
                        s * 0.5 * layerScale, -s * 1.2 * layerScale, 
                        0, -s * 1.5 * layerScale
                    );
                    ctx.bezierCurveTo(
                        -s * 0.5 * layerScale, -s * 1.2 * layerScale, 
                        -s * 0.5 * layerScale, -s * 0.8 * layerScale, 
                        0, -s * 0.2
                    );
                    ctx.stroke();
                    ctx.restore();
                }
            }
        }

        drawTulip(ctx, bloom) {
            const s = this.size;
            
            // Base connection
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.lineTo(0, -s*0.1);
            ctx.stroke();
            
            // Center petal (stays mostly upright)
            ctx.save();
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.quadraticCurveTo(s*0.4, -s*0.8, 0, -s*1.2);
            ctx.quadraticCurveTo(-s*0.4, -s*0.8, 0, 0);
            ctx.stroke();
            ctx.restore();

            // Side petals (open up with bloom)
            const sidePetals = [-1, 1];
            sidePetals.forEach(dir => {
                ctx.save();
                // Bloom rotation
                const rotation = dir * bloom * 0.4;
                ctx.rotate(rotation);

                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.quadraticCurveTo(dir * s * 0.6, -s * 0.6, dir * s * 0.3, -s * 1.1);
                ctx.quadraticCurveTo(0, -s * 0.8, 0, 0);
                ctx.stroke();
                ctx.restore();
            });
        }
    }

    function lerpPoint(p1, p2, t) {
        return {
            x: p1.x + (p2.x - p1.x) * t,
            y: p1.y + (p2.y - p1.y) * t
        };
    }

    function initFlowers() {
        flowers = [];
        const segmentWidth = width / config.flowerCount;
        for (let i = 0; i < config.flowerCount; i++) {
            // Distribute flowers evenly across the width with some random jitter
            const x = i * segmentWidth + segmentWidth * 0.1 + Math.random() * (segmentWidth * 0.8);
            flowers.push(new Flower(x));
        }
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        flowers.forEach(flower => {
            flower.update();
            flower.draw(ctx, scrollY);
        });

        requestAnimationFrame(animate);
    }

    // Start
    resize();
    animate();
});
