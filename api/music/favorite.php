<?php
// API xử lý Yêu thích / Bỏ yêu thích bài hát
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

if (!isLoggedIn()) {
    json_response(false, 'Vui lòng đăng nhập để thực hiện tính năng này!');
}

$user_id = $_SESSION['user']['id'];
$song_id = isset($_POST['song_id']) ? intval($_POST['song_id']) : 0;

if ($song_id <= 0) {
    json_response(false, 'Mã bài hát không hợp lệ!');
}

// 1. Nếu cơ sở dữ liệu hoạt động
if ($db_connected && $conn) {
    try {
        // Kiểm tra xem bài hát đã nằm trong mục yêu thích chưa
        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND song_id = ?");
        $stmt->execute([$user_id, $song_id]);
        $fav = $stmt->fetch();
        
        if ($fav) {
            // Đã yêu thích -> Bỏ yêu thích
            $del = $conn->prepare("DELETE FROM favorites WHERE id = ?");
            $del->execute([$fav['id']]);
            json_response(true, 'Đã xóa bài hát khỏi danh sách yêu thích!', ['is_favorite' => false]);
        } else {
            // Chưa yêu thích -> Thêm yêu thích
            $ins = $conn->prepare("INSERT INTO favorites (user_id, song_id) VALUES (?, ?)");
            $ins->execute([$user_id, $song_id]);
            json_response(true, 'Đã thêm bài hát vào mục yêu thích!', ['is_favorite' => true]);
        }
    } catch (PDOException $e) {
        json_response(false, 'Gặp lỗi trong quá trình xử lý yêu thích trên cơ sở dữ liệu!');
    }
} else {
    // 2. Chế độ chạy thử nghiệm Demo (Lưu trạng thái trong Session của User)
    if (!isset($_SESSION['demo_favorites'])) {
        $_SESSION['demo_favorites'] = [];
    }
    
    $key = array_search($song_id, $_SESSION['demo_favorites']);
    if ($key !== false) {
        unset($_SESSION['demo_favorites'][$key]);
        $_SESSION['demo_favorites'] = array_values($_SESSION['demo_favorites']);
        json_response(true, 'Đã bỏ thích ở chế độ Demo!', ['is_favorite' => false]);
    } else {
        $_SESSION['demo_favorites'][] = $song_id;
        json_response(true, 'Đã thích ở chế độ Demo!', ['is_favorite' => true]);
    }
}
