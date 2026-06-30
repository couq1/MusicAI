<?php
$page_title = 'Đăng ký - MusicAI';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isLoggedIn()) {
    redirect('index.php');
}
?>

<div class="auth-page-container">
    <!-- Khu vực thêm Reactbits animation -->
    <!-- Đây là nơi bạn có thể nhúng các thẻ div, canvas hoặc các class Reactbits tạo hiệu ứng chuyển động theo chuột -->
<div class="floating-lines-bg">
    <canvas id="floatingLinesCanvas"></canvas>
</div>
    <!-- Kết thúc Khu vực thêm Reactbits animation -->

    <div class="auth-box-wrapper" style="position: relative; z-index: 10;">
        <div class="glass-card auth-card">
            <h2 class="auth-title"><span class="highlight">ĐĂNG KÝ</span></h2>
            <p class="auth-subtitle">Bắt đầu trải nghiệm tạo nhạc trí tuệ nhân tạo</p>
            
            <form id="registerForm">
                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Nguyễn Văn A" required autocomplete="name">
                </div>
                
                <div class="form-group">
                    <label for="email">Địa chỉ Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="example@musicai.local" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="width: 100%; margin-top: 10px;">
                    Tạo tài khoản <i class="fa-solid fa-user-plus"></i>
                </button>
            </form>
            
            <div class="auth-footer">
                Đã có tài khoản? <a href="<?php echo url('login.php'); ?>" class="highlight">Đăng nhập tại đây</a>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page-container {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 40px 0;
    background: #030901;
}

.auth-box-wrapper {
    width: 90%;
    max-width: 420px;
    position: relative;
    z-index: 10;
}

.auth-card {
    padding: 40px 30px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4), 0 0 15px rgba(0, 255, 136, 0.05);
}

.auth-title {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 5px;
    letter-spacing: 1px;
}

.auth-subtitle {
    text-align: center;
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 30px;
}

.auth-footer {
    text-align: center;
    margin-top: 25px;
    font-size: 0.9rem;
    color: var(--text-muted);
}
.floating-lines-bg {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    overflow: hidden;
    background:
        radial-gradient(circle at 80% 20%, rgba(0, 255, 120, 0.08), transparent 35%),
        radial-gradient(circle at 20% 80%, rgba(80, 160, 255, 0.08), transparent 40%),
        #030901;
}

#floatingLinesCanvas {
    width: 100%;
    height: 100%;
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ==========================
       1. XỬ LÝ ĐĂNG KÝ
    ========================== */
    const registerForm = document.getElementById('registerForm');

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (password.length < 6) {
                showToast('Mật khẩu phải dài tối thiểu 6 ký tự!', 'error');
                return;
            }

            try {
                const response = await fetch(BASE_URL + 'api/auth/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });

                const res = await response.json();

                if (res.success) {
                    showToast('Đăng ký tài khoản thành công! Đang chuyển hướng đăng nhập...', 'success');

                    setTimeout(() => {
                        window.location.href = BASE_URL + 'login.php';
                    }, 1500);
                } else {
                    showToast(res.message, 'error');
                }

            } catch (err) {
                console.error('Register submit error:', err);
                showToast('Đã xảy ra lỗi kết nối đến máy chủ!', 'error');
            }
        });
    }

    /* ==========================
       2. FLOATING LINES
    ========================== */
    const canvas = document.getElementById('floatingLinesCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    let w = 0;
    let h = 0;
    let time = 0;

    let mouseX = 0;
    let mouseY = 0;
    let smoothMouseX = 0;
    let smoothMouseY = 0;

    function resizeCanvas() {
        const dpr = window.devicePixelRatio || 1;

        canvas.width = canvas.offsetWidth * dpr;
        canvas.height = canvas.offsetHeight * dpr;

        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        w = canvas.offsetWidth;
        h = canvas.offsetHeight;

        mouseX = w / 2;
        mouseY = h / 2;
        smoothMouseX = w / 2;
        smoothMouseY = h / 2;
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    document.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();

        mouseX = e.clientX - rect.left;
        mouseY = e.clientY - rect.top;
    });

    const colors = [
        'rgba(38, 99, 52, 0.65)',
        'rgba(9, 148, 76, 0.52)',
        'rgba(18, 77, 119, 0.38)',
        'rgba(98, 116, 134, 0.25)'
    ];

    function drawLine(index, color) {
        const baseY = h * 0.5 + index * 26;
        const amplitude = 85 + Math.abs(index) * 6;
        const phase = index * 0.75;

        ctx.beginPath();

        for (let x = -120; x <= w + 120; x += 10) {
            const progress = x / w;

            let y =
                baseY +
                Math.sin(progress * 8 + time * 0.0013 + phase) * amplitude +
                Math.sin(progress * 18 - time * 0.001 + phase) * 30 +
                Math.cos(progress * 6 + time * 0.0008 + phase) * 22;

            const dx = x - smoothMouseX;
            const distanceEffect = Math.exp(-(dx * dx) / 45000);

            y += (smoothMouseY - y) * distanceEffect * 0.45;

            if (x === -120) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }

        ctx.strokeStyle = color;
        ctx.lineWidth = 1.9;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.shadowColor = color;
        ctx.shadowBlur = 26;
        ctx.stroke();
    }

    function animate(t) {
        time = t;

        smoothMouseX += (mouseX - smoothMouseX) * 0.08;
        smoothMouseY += (mouseY - smoothMouseY) * 0.08;

        ctx.clearRect(0, 0, w, h);

        ctx.globalCompositeOperation = 'lighter';

        for (let i = 0; i < 20; i++) {
            drawLine(i - 10, colors[i % colors.length]);
        }

        ctx.globalCompositeOperation = 'source-over';

        requestAnimationFrame(animate);
    }

    requestAnimationFrame(animate);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
