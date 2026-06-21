<?php
// API xử lý Yêu cầu tạo nhạc AI (gửi tới Python Server hoặc chạy Mockup)
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/config/ai_server.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

if (!isLoggedIn()) {
    json_response(false, 'Bạn cần đăng nhập để sử dụng tính năng tạo nhạc AI!');
}

$prompt = isset($_POST['prompt']) ? trim($_POST['prompt']) : '';
$genre = isset($_POST['genre']) ? trim($_POST['genre']) : 'Lofi';
$mood = isset($_POST['mood']) ? trim($_POST['mood']) : 'Relax';
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 30;

if (empty($prompt)) {
    json_response(false, 'Ý tưởng bài nhạc (prompt) không được để trống!');
}

// Lấy cấu hình URL của AI Server từ cài đặt quản trị hoặc file config mặc định
$ai_url = AI_SERVER_URL;
$settings_file = dirname(dirname(__DIR__)) . '/config/settings.json';
if (file_exists($settings_file)) {
    $settings_data = json_decode(file_get_contents($settings_file), true);
    if (is_array($settings_data) && !empty($settings_data['ai_url'])) {
        $ai_url = $settings_data['ai_url'];
    }
}

// 1. Thử gửi POST Request JSON tới Python AI Server qua cURL
$ch = curl_init();
$payload = json_encode([
    'prompt' => $prompt,
    'genre' => $genre,
    'mood' => $mood,
    'duration' => $duration
]);

curl_setopt($ch, CURLOPT_URL, rtrim($ai_url, '/') . '/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Chờ phản hồi tối đa 3 giây trước khi fallback sang Mockup

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response && $http_code === 200) {
    $res_data = json_decode($response, true);
    if (is_array($res_data) && isset($res_data['task_id'])) {
        // Trả về task_id thành công từ Python Server
        json_response(true, 'Yêu cầu tạo nhạc AI đã được gửi đi!', $res_data);
    }
}

// 2. Dự phòng Mockup khi Python Server không hoạt động hoặc chưa bật
$mock_task_id = 'task_mock_' . uniqid();

// Danh sách file loop tương ứng
$mock_loops = [
    'Lofi' => 'storage/samples/lofi_loop.mp3',
    'EDM' => 'storage/samples/edm_loop.mp3',
    'Trap' => 'storage/samples/trap_loop.mp3',
    'Chill' => 'storage/samples/chill_loop.mp3',
    'Piano' => 'storage/samples/piano_loop.mp3',
    'Ambient' => 'storage/samples/ambient_loop.mp3',
    'Hip-hop' => 'storage/samples/hiphop_loop.mp3'
];

$audio_file = isset($mock_loops[$genre]) ? $mock_loops[$genre] : 'storage/samples/lofi_loop.mp3';

// Lưu trữ thông tin task ảo vào Session để api/ai/check_status.php truy cập
if (!isset($_SESSION['mock_tasks'])) {
    $_SESSION['mock_tasks'] = [];
}

$_SESSION['mock_tasks'][$mock_task_id] = [
    'task_id' => $mock_task_id,
    'status' => 'completed',
    'prompt' => $prompt,
    'genre' => $genre,
    'mood' => $mood,
    'duration' => $duration,
    'audio_file' => $audio_file
];

// Trả về task_id ảo để JS tiến hành polling
json_response(true, 'Gửi yêu cầu tạo nhạc thành công (Chế độ chạy thử nghiệm)!', [
    'task_id' => $mock_task_id,
    'prompt' => $prompt,
    'genre' => $genre,
    'mood' => $mood,
    'duration' => $duration,
    'audio_file' => $audio_file
]);
