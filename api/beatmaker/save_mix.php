<?php
// API lưu bản Beat Mix (Incredibox style)
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

if (!isLoggedIn()) {
    json_response(false, 'Vui lòng đăng nhập để lưu bản Mix!');
}

$user_id = $_SESSION['user']['id'];
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$mix_data = isset($_POST['mix_data']) ? trim($_POST['mix_data']) : '';

if (empty($name) || empty($mix_data)) {
    json_response(false, 'Dữ liệu bản Mix không hợp lệ hoặc thiếu!');
}

// Kiểm tra tính hợp lệ của JSON mix_data
$decoded = json_decode($mix_data, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
    json_response(false, 'Định dạng danh sách sound trong bản Mix không hợp lệ!');
}

// 1. Thực thi chèn DB
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("INSERT INTO beat_mixes (user_id, name, mix_data) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $name, $mix_data]);
        json_response(true, 'Đã lưu bản phối Beat Mix thành công vào thư viện!');
    } catch (PDOException $e) {
        json_response(false, 'Lỗi cơ sở dữ liệu khi lưu bản Mix: ' . $e->getMessage());
    }
} else {
    // 2. Chế độ chạy thử nghiệm Demo (Lưu vào mảng Session)
    if (!isset($_SESSION['demo_beat_mixes'])) {
        $_SESSION['demo_beat_mixes'] = [];
    }
    
    $new_id = count($_SESSION['demo_beat_mixes']) + 1;
    $new_mix = [
        'id' => $new_id,
        'user_id' => $user_id,
        'name' => $name,
        'mix_data' => $mix_data,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['demo_beat_mixes'][] = $new_mix;
    
    json_response(true, 'Đã lưu bản Beat Mix thành công ở chế độ Demo!', $new_mix);
}
