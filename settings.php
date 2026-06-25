<?php
$page_title = 'Cài đặt tài khoản - MusicAI';
$page_desc = 'Chỉnh sửa thông tin cá nhân, cập nhật ảnh đại diện và thay đổi mật khẩu của bạn.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Yêu cầu đăng nhập mới được truy cập
requireLogin();

$user_id = $_SESSION['user']['id'];
$user_name = $_SESSION['user']['name'];
$user_email = $_SESSION['user']['email'];
$user_avatar = $_SESSION['user']['avatar'];

// Lấy thông tin tươi nhất từ cơ sở dữ liệu nếu có kết nối
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("SELECT name, email, avatar FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $db_user = $stmt->fetch();
        if ($db_user) {
            $user_name = $db_user['name'];
            $user_email = $db_user['email'];
            if (!empty($db_user['avatar'])) {
                $user_avatar = $db_user['avatar'];
            }
        }
    } catch (PDOException $e) {
        // Fallback dùng Session
    }
}

// Xử lý đường dẫn avatar
$avatar_url = url(!empty($user_avatar) ? $user_avatar : DEFAULT_AVATAR);
?>

<div class="container settings-page-container">
    <div class="settings-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border-light); padding-bottom: 15px;">
        <h2 class="section-title" style="margin-bottom: 0;"><i class="fa-solid fa-user-gear"></i> MyInfo</h2>
        <a href="javascript:history.back()" class="btn btn-secondary btn-sm" style="border-radius: 20px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <div class="grid-3 settings-grid">
        <!-- Panel bên trái: Profile Preview & Avatar Upload -->
        <div class="glass-card settings-left-card">
            <div class="avatar-upload-wrapper">
                <div class="avatar-preview-container">
                    <img id="avatarPreview" src="<?php echo $avatar_url; ?>" alt="Avatar Preview" class="profile-avatar-large">
                    <div class="avatar-overlay" onclick="document.getElementById('avatarInput').click();">
                        <i class="fa-solid fa-camera"></i>
                        <span>Thay đổi ảnh</span>
                    </div>
                </div>
                <h3 class="user-display-name" id="userDisplayName"><?php echo sanitize($user_name); ?></h3>
                <p class="user-display-email"><?php echo sanitize($user_email); ?></p>
                <span class="user-role-badge">
                    <i class="fa-solid fa-shield-halved"></i> <?php echo $_SESSION['user']['role'] === 'admin' ? 'Quản trị viên' : 'Thành viên'; ?>
                </span>
            </div>
            
            <div class="settings-instructions">
                <h4><i class="fa-solid fa-circle-info"></i> Lưu ý</h4>
                <ul>
                    <li>Đảm bảo các thay đổi tên hoặc mật khẩu đã được lưu lại trước khi rời đi.</li>
                </ul>
            </div>
        </div>
        
        <!-- Panel bên phải: Form chỉnh sửa chi tiết -->
        <div class="glass-card settings-right-card col-span-2">
            <form id="settingsForm" enctype="multipart/form-data">
                <!-- File input ẩn dùng để upload avatar -->
                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;">
                
                <h3 class="settings-sub-title"><i class="fa-solid fa-address-card"></i> Thông tin cá nhân</h3>
                
                <div class="grid-2 form-row">
                    <div class="form-group">
                        <label for="name">Họ và tên hiển thị</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo sanitize($user_name); ?>" required placeholder="Nhập tên hiển thị của bạn">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Địa chỉ Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo sanitize($user_email); ?>" required placeholder="example@musicai.local">
                    </div>
                </div>
                
                <hr class="settings-divider">
                
                <h3 class="settings-sub-title"><i class="fa-solid fa-key"></i> Thay đổi mật khẩu</h3>
                <p class="settings-desc-text">Nếu không muốn thay đổi mật khẩu, vui lòng bỏ trống các ô dưới đây.</p>
                
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Nhập mật khẩu hiện tại nếu muốn đổi mật khẩu">
                </div>
                
                <div class="grid-2 form-row">
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Tối thiểu 6 ký tự">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới">
                    </div>
                </div>
                
                <div class="settings-actions">
                    <a href="<?php echo url('index.php'); ?>" class="btn btn-outline btn-sm" style="margin-right: auto; border-radius: 20px;">
                        <i class="fa-solid fa-house"></i> Về trang chủ
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="window.location.reload();">
                        <i class="fa-solid fa-arrows-rotate"></i> Hủy thay đổi
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.settings-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 0;
}

.settings-grid {
    grid-template-columns: 1fr 2fr;
    gap: 30px;
    align-items: start;
}

.settings-left-card {
    text-align: center;
    padding: 40px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 25px;
}

.avatar-upload-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.avatar-preview-container {
    position: relative;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    margin-bottom: 20px;
    overflow: hidden;
    border: 3px solid var(--green-main);
    box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
}

.profile-avatar-large {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-normal);
}

.avatar-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(5, 8, 7, 0.75);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    opacity: 0;
    cursor: pointer;
    transition: opacity var(--transition-normal);
}

.avatar-preview-container:hover .avatar-overlay {
    opacity: 1;
}

.avatar-preview-container:hover .profile-avatar-large {
    transform: scale(1.1);
}

.avatar-overlay i {
    font-size: 1.5rem;
    color: var(--green-main);
}

.avatar-overlay span {
    font-size: 0.75rem;
    color: var(--text-main);
    font-weight: 500;
}

.user-display-name {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.user-display-email {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.user-role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 20px;
    background: rgba(0, 255, 136, 0.1);
    color: var(--green-main);
    border: 1px solid var(--border-green);
    font-size: 0.8rem;
    font-weight: 600;
}

.settings-instructions {
    text-align: left;
    width: 100%;
    border-top: 1px solid var(--border-light);
    padding-top: 20px;
}

.settings-instructions h4 {
    font-size: 0.95rem;
    margin-bottom: 12px;
    color: var(--text-main);
}

.settings-instructions ul {
    list-style: none;
    padding-left: 0;
}

.settings-instructions li {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 10px;
    position: relative;
    padding-left: 15px;
}

.settings-instructions li::before {
    content: "•";
    color: var(--green-main);
    position: absolute;
    left: 0;
    top: 0;
    font-weight: bold;
}

.settings-right-card {
    padding: 40px;
}

.settings-sub-title {
    font-size: 1.3rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.settings-sub-title i {
    color: var(--green-main);
}

.form-row {
    margin-bottom: 10px;
}

.settings-divider {
    border: none;
    border-top: 1px solid var(--border-light);
    margin: 30px 0;
}

.settings-desc-text {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.settings-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
    border-top: 1px solid var(--border-light);
    padding-top: 20px;
}

/* responsive */
@media (max-width: 992px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const settingsForm = document.getElementById('settingsForm');
    
    // Preview ảnh đại diện khi người dùng chọn file
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Kiểm tra định dạng ảnh
                const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showToast('Vui lòng chọn ảnh định dạng JPG, PNG hoặc WEBP!', 'error');
                    this.value = '';
                    return;
                }
                
                // Kiểm tra dung lượng (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showToast('Dung lượng ảnh vượt quá giới hạn 2MB!', 'error');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Gửi form AJAX để cập nhật
    if (settingsForm) {
        settingsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validate password nếu có thay đổi
            if (newPassword || confirmPassword) {
                if (!currentPassword) {
                    showToast('Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu mới!', 'error');
                    return;
                }
                if (newPassword.length < 6) {
                    showToast('Mật khẩu mới phải dài tối thiểu 6 ký tự!', 'error');
                    return;
                }
                if (newPassword !== confirmPassword) {
                    showToast('Mật khẩu mới và mật khẩu xác nhận không trùng khớp!', 'error');
                    return;
                }
            }
            
            // Chuẩn bị FormData để upload tệp
            const formData = new FormData(settingsForm);
            
            try {
                const response = await fetch(BASE_URL + 'api/user/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const res = await response.json();
                
                if (res.success) {
                    showToast(res.message, 'success');
                    
                    // Cập nhật giao diện real-time
                    if (res.data) {
                        const nameElements = document.querySelectorAll('.username-text, #userDisplayName');
                        nameElements.forEach(el => {
                            el.textContent = res.data.name;
                        });
                        
                        const emailEl = document.querySelector('.user-display-email');
                        if (emailEl) emailEl.textContent = res.data.email;
                        
                        const avatarElements = document.querySelectorAll('.avatar-small, #avatarPreview');
                        avatarElements.forEach(el => {
                            el.src = res.data.avatar;
                        });
                    }
                    
                    // Xóa các trường mật khẩu
                    document.getElementById('current_password').value = '';
                    document.getElementById('new_password').value = '';
                    document.getElementById('confirm_password').value = '';
                    
                } else {
                    showToast(res.message, 'error');
                }
            } catch (err) {
                console.error('Settings save error:', err);
                showToast('Đã xảy ra lỗi kết nối đến máy chủ!', 'error');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
