<?php
// Cấu hình chung cho MusicAI
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Tải cấu hình động lưu trữ trong JSON
$settings_file = __DIR__ . '/settings.json';
$site_name = 'MusicAI';
if (file_exists($settings_file)) {
    $json = file_get_contents($settings_file);
    $saved = json_decode($json, true);
    if (is_array($saved) && !empty($saved['site_name'])) {
        $site_name = $saved['site_name'];
    }
}

define('SITE_NAME', $site_name);
// define('BASE_URL', '/MusicAI/'); // Đường dẫn cơ sở chạy trên XAMPP (mặc định thư mục htdocs/MusicAI)
define('BASE_URL', '/');
define('STORAGE_PATH', dirname(__DIR__) . '/storage/');
define('DEFAULT_AVATAR', 'assets/images/default-avatar.png');

// Định nghĩa các đường dẫn tài nguyên
define('STORAGE_SONGS', 'storage/songs/');
define('STORAGE_GENERATED', 'storage/generated/');
define('STORAGE_BEAT_MIXES', 'storage/beat_mixes/');
define('STORAGE_THUMBNAILS', 'storage/thumbnails/');
define('STORAGE_AVATARS', 'storage/avatars/');

/**
 * Trả về đường dẫn tuyệt đối của tài nguyên
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Làm sạch dữ liệu đầu vào chống XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Chuyển hướng trang nhanh
 */
function redirect($url) {
    header("Location: " . url($url));
    exit();
}

/**
 * Trả về response JSON đồng bộ cấu trúc cho API
 */
function json_response($success, $message = 'Thành công', $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
