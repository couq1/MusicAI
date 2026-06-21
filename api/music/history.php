<?php
// API xử lý Ghi nhận lịch sử nghe nhạc và tăng lượt phát (plays)
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$song_id = isset($_POST['song_id']) ? intval($_POST['song_id']) : 0;

if ($song_id <= 0) {
    json_response(false, 'Mã bài hát không hợp lệ!');
}

// 1. Nếu có kết nối cơ sở dữ liệu
if ($db_connected && $conn) {
    try {
        // Tăng số lượt nghe của bài hát gốc lên 1 đơn vị
        $stmt = $conn->prepare("UPDATE songs SET plays = plays + 1 WHERE id = ?");
        $stmt->execute([$song_id]);
        
        // Nếu người dùng đã đăng nhập, ghi lại thông tin vào bảng listening_history
        if (isLoggedIn()) {
            $user_id = $_SESSION['user']['id'];
            
            $ins = $conn->prepare("INSERT INTO listening_history (user_id, song_id) VALUES (?, ?)");
            $ins->execute([$user_id, $song_id]);
        }
        
        json_response(true, 'Đã ghi nhận lượt phát nhạc!');
    } catch (PDOException $e) {
        json_response(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage());
    }
} else {
    // 2. Chế độ chạy thử nghiệm Demo (Thêm lịch sử vào mảng Session)
    if (isLoggedIn()) {
        if (!isset($_SESSION['demo_history'])) {
            $_SESSION['demo_history'] = [];
        }
        array_unshift($_SESSION['demo_history'], [
            'song_id' => $song_id,
            'listened_at' => date('Y-m-d H:i:s')
        ]);
    }
    json_response(true, 'Đã ghi nhận lượt phát ở chế độ Demo!');
}
