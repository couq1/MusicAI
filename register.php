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
    <div id="reactbits-mouse-animation" style="position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:1;"></div>
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
}

.auth-box-wrapper {
    width: 90%;
    max-width: 420px;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
