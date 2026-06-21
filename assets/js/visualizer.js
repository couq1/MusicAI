// Hiệu ứng sóng nhạc bằng Canvas cho trang Beatmaker

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('beatmakerCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    let animationFrameId;
    let isActive = false;
    
    // Tự động thay đổi kích thước Canvas theo kích thước container
    function resizeCanvas() {
        if (!canvas.parentElement) return;
        canvas.width = canvas.parentElement.clientWidth;
        canvas.height = canvas.parentElement.clientHeight || 120;
    }
    
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
    
    // Khởi tạo các thanh phổ tần số sóng
    const barCount = 70;
    const bars = [];
    
    for (let i = 0; i < barCount; i++) {
        bars.push({
            targetHeight: 4,
            currentHeight: 4,
            speed: 0.1 + Math.random() * 0.15
        });
    }
    
    /**
     * Vòng lặp vẽ hiệu ứng
     */
    function renderVisualizer() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        const gap = 3;
        const totalGapWidth = gap * (barCount - 1);
        const barWidth = (canvas.width - totalGapWidth) / barCount;
        
        // Gradient màu xanh lá neon sang xanh lá đậm
        const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);
        gradient.addColorStop(0, '#00ff88');
        gradient.addColorStop(0.5, '#00dd77');
        gradient.addColorStop(1, '#00ff88');
        ctx.fillStyle = gradient;
        
        // Tạo glow nhẹ
        ctx.shadowBlur = 8;
        ctx.shadowColor = 'rgba(0, 255, 136, 0.5)';
        
        for (let i = 0; i < barCount; i++) {
            const bar = bars[i];
            
            if (isActive) {
                // Tạo dao động tần số giả lập nhấp nhô theo nhịp nhạc
                const timeFactor = Date.now() * 0.006;
                const wave = Math.sin(i * 0.15 - timeFactor) * Math.cos(i * 0.05 + timeFactor);
                const baseHeight = Math.abs(wave) * (canvas.height - 20);
                
                // Trộn lẫn một chút ngẫu nhiên để trông giống phổ thật hơn
                bar.targetHeight = Math.max(6, baseHeight * (0.4 + Math.random() * 0.6));
            } else {
                // Sóng tĩnh nhỏ khi nhạc đang dừng
                bar.targetHeight = 4 + Math.sin(i * 0.3 + Date.now() * 0.002) * 2;
            }
            
            // Nội suy chiều cao mượt mà
            bar.currentHeight += (bar.targetHeight - bar.currentHeight) * bar.speed;
            
            const x = i * (barWidth + gap);
            const y = (canvas.height - bar.currentHeight) / 2; // Căn giữa theo trục đứng
            const w = barWidth;
            const h = bar.currentHeight;
            
            // Vẽ bo góc nhẹ cho mỗi cột sóng
            ctx.beginPath();
            if (ctx.roundRect) {
                ctx.roundRect(x, y, w, h, 2);
            } else {
                ctx.rect(x, y, w, h);
            }
            ctx.fill();
        }
        
        animationFrameId = requestAnimationFrame(renderVisualizer);
    }
    
    // Đăng ký nhận sự kiện thay đổi trạng thái chơi nhạc từ beatmaker.js
    document.addEventListener('visualizer-state-change', (e) => {
        isActive = e.detail.active;
    });
    
    renderVisualizer();
});
