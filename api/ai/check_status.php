<?php
// API kiểm tra trạng thái tiến trình tạo nhạc (Polling) từ Python Server hoặc Session Mockup
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/config/ai_server.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

if (!isLoggedIn()) {
    json_response(false, 'Yêu cầu đăng nhập tài khoản!');
}

$task_id = isset($_GET['task_id']) ? trim($_GET['task_id']) : '';

if (empty($task_id)) {
    json_response(false, 'Thiếu thông số mã tiến trình task_id!');
}

// 1. Kiểm tra xem có thuộc các task chạy mockup lưu trong Session không
if (isset($_SESSION['mock_tasks'][$task_id])) {
    json_response(true, 'Lấy trạng thái mock thành công!', $_SESSION['mock_tasks'][$task_id]);
}

// 2. Thử truy vấn trực tiếp Python AI Server qua cURL
$ai_url = AI_SERVER_URL;
$settings_file = dirname(dirname(__DIR__)) . '/config/settings.json';
if (file_exists($settings_file)) {
    $settings_data = json_decode(file_get_contents($settings_file), true);
    if (is_array($settings_data) && !empty($settings_data['ai_url'])) {
        $ai_url = $settings_data['ai_url'];
    }
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, rtrim($ai_url, '/') . '/status/' . $task_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response && $http_code === 200) {
    $res_data = json_decode($response, true);
    if (is_array($res_data)) {
        json_response(true, 'Lấy thông tin từ AI Server thành công!', $res_data);
    }
}

json_response(false, 'Không tìm thấy tiến trình nào khớp với mã task_id được gửi!');
