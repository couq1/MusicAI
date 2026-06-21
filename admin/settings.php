<?php
$page_title = 'Cấu hình Website';
require_once __DIR__ . '/admin_header.php';

$settings_file = dirname(__DIR__) . '/config/settings.json';
$settings = [
    'site_name' => 'MusicAI',
    'logo_text' => 'MusicAI',
    'footer_text' => '&copy; 2026 MusicAI - Trải nghiệm âm nhạc đỉnh cao cùng AI.',
    'enable_ai' => '1',
    'ai_url' => 'http://127.0.0.1:8000'
];

// Nạp dữ liệu cũ nếu tồn tại
if (file_exists($settings_file)) {
    $json = file_get_contents($settings_file);
    $saved = json_decode($json, true);
    if (is_array($saved)) {
        $settings = array_merge($settings, $saved);
    }
}

$message = '';
$error = '';

// Lưu dữ liệu POST gửi lên
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['site_name'] = trim($_POST['site_name']);
    $settings['logo_text'] = trim($_POST['logo_text']);
    $settings['footer_text'] = trim($_POST['footer_text']);
    $settings['enable_ai'] = isset($_POST['enable_ai']) ? '1' : '0';
    $settings['ai_url'] = trim($_POST['ai_url']);
    
    if (empty($settings['site_name'])) {
        $error = 'Tên website không được phép để trống!';
    } else {
        if (file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $message = 'Đã lưu cấu hình website thành công!';
        } else {
            $error = 'Không có quyền ghi vào thư mục cấu hình config/!';
        }
    }
}
?>

<!-- Thông báo -->
<?php if (!empty($message)): ?>
    <div class="alert-box alert-success"><i class="fa-solid fa-check"></i> <?php echo $message; ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert-box alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-form-card" style="margin-top: 0;">
    <h3 style="margin-bottom: 25px;"><i class="fa-solid fa-sliders"></i> Cấu Hình Toàn Cục Hệ Thống</h3>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="site_name">Tên Website (Site Name)</label>
            <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo sanitize($settings['site_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="logo_text">Nội dung Logo Chữ (Logo Text)</label>
            <input type="text" id="logo_text" name="logo_text" class="form-control" value="<?php echo sanitize($settings['logo_text']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="footer_text">Thông tin bản quyền ở chân trang (Footer Copyright)</label>
            <textarea id="footer_text" name="footer_text" class="form-control" rows="3" required><?php echo sanitize($settings['footer_text']); ?></textarea>
        </div>
        
        <div class="form-group" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; margin-top: 20px;">
            <label for="ai_url">Endpoint Local AI Server (Python FastAPI)</label>
            <input type="url" id="ai_url" name="ai_url" class="form-control" value="<?php echo sanitize($settings['ai_url']); ?>" placeholder="http://127.0.0.1:8000">
        </div>
        
        <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top: 15px;">
            <input type="checkbox" id="enable_ai" name="enable_ai" <?php echo $settings['enable_ai'] === '1' ? 'checked' : ''; ?> style="width:18px; height:18px;">
            <label for="enable_ai" style="margin-bottom:0; cursor:pointer;">Cho phép người dùng tạo nhạc AI</label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 25px;">
            <i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình hệ thống
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
