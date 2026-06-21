<?php
// API xử lý Đăng nhập người dùng
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Phương thức gửi yêu cầu không hợp lệ!');
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    json_response(false, 'Vui lòng nhập đầy đủ Email và Mật khẩu!');
}

// 1. Nếu kết nối cơ sở dữ liệu thành công
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Lưu thông tin người dùng vào Session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => !empty($user['avatar']) ? $user['avatar'] : DEFAULT_AVATAR
            ];
            
            // Xác định trang chuyển hướng dựa trên quyền
            $redirect = ($user['role'] === 'admin') ? 'admin/dashboard.php' : 'index.php';
            json_response(true, 'Đăng nhập thành công!', ['redirect_url' => url($redirect)]);
        } else {
            json_response(false, 'Tên đăng nhập (Email) hoặc Mật khẩu không khớp!');
        }
    } catch (PDOException $e) {
        json_response(false, 'Gặp lỗi khi xác thực trong cơ sở dữ liệu!');
    }
} else {
    // 2. Chế độ chạy thử nghiệm Demo (Khi chưa tạo bảng hoặc kết nối DB hỏng)
    if ($email === 'admin@musicai.local' && $password === 'admin123') {
        $_SESSION['user'] = [
            'id' => 1,
            'name' => 'Admin Demo',
            'email' => 'admin@musicai.local',
            'role' => 'admin',
            'avatar' => DEFAULT_AVATAR
        ];
        json_response(true, 'Đăng nhập thử nghiệm quyền Admin thành công!', ['redirect_url' => url('admin/dashboard.php')]);
    } elseif ($email === 'user@musicai.local' && $password === 'user123') {
        $_SESSION['user'] = [
            'id' => 2,
            'name' => 'Khách Demo',
            'email' => 'user@musicai.local',
            'role' => 'user',
            'avatar' => DEFAULT_AVATAR
        ];
        json_response(true, 'Đăng nhập thử nghiệm quyền User thành công!', ['redirect_url' => url('index.php')]);
    } else {
        json_response(false, 'Tài khoản không chính xác! Vui lòng sử dụng thông tin Demo: (admin@musicai.local / admin123) hoặc (user@musicai.local / user123).');
    }
}
