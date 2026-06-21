<?php
// API lưu bài hát AI đã tạo vào cơ sở dữ liệu
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

if (!isLoggedIn()) {
    json_response(false, 'Vui lòng đăng nhập để lưu tác phẩm!');
}

$user_id = $_SESSION['user']['id'];
$prompt = isset($_POST['prompt']) ? trim($_POST['prompt']) : '';
$genre = isset($_POST['genre']) ? trim($_POST['genre']) : '';
$mood = isset($_POST['mood']) ? trim($_POST['mood']) : '';
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 30;
$audio_file = isset($_POST['audio_file']) ? trim($_POST['audio_file']) : '';

if (empty($prompt) || empty($audio_file)) {
    json_response(false, 'Dữ liệu đầu vào để lưu bài hát không hợp lệ!');
}

// 1. Thực thi chèn DB
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("INSERT INTO ai_songs (user_id, prompt, genre, mood, duration, audio_file) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $prompt, $genre, $mood, $duration, $audio_file]);
        json_response(true, 'Đã lưu bài hát thành công vào thư viện của bạn!');
    } catch (PDOException $e) {
        json_response(false, 'Lỗi cơ sở dữ liệu khi lưu bài hát: ' . $e->getMessage());
    }
} else {
    // 2. Chế độ chạy thử nghiệm Demo (Lưu trữ trong mảng Session của User)
    if (!isset($_SESSION['demo_ai_songs'])) {
        $_SESSION['demo_ai_songs'] = [];
    }
    
    $new_id = count($_SESSION['demo_ai_songs']) + 1;
    $new_song = [
        'id' => $new_id,
        'user_id' => $user_id,
        'prompt' => $prompt,
        'genre' => $genre,
        'mood' => $mood,
        'duration' => $duration,
        'audio_file' => $audio_file,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['demo_ai_songs'][] = $new_song;
    
    json_response(true, 'Lưu bài hát thành công ở chế độ Demo!', $new_song);
}
