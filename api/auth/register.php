<?php
// API xử lý Đăng ký tài khoản người dùng mới
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Phương thức gửi yêu cầu không hợp lệ!');
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($name) || empty($email) || empty($password)) {
    json_response(false, 'Vui lòng điền đầy đủ các thông tin đăng ký!');
}

if (strlen($password) < 6) {
    json_response(false, 'Mật khẩu bắt buộc phải dài tối thiểu 6 ký tự!');
}

// 1. Thực thi lưu trữ nếu kết nối database thành công
if ($db_connected && $conn) {
    try {
        // Kiểm tra email đã có người sử dụng chưa
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            json_response(false, 'Địa chỉ email này đã được sử dụng trên hệ thống!');
        }
        
        // Băm mật khẩu bảo mật
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Thêm tài khoản mặc định phân quyền 'user'
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $hash]);
        
        json_response(true, 'Đăng ký tài khoản thành công! Hãy chuyển sang Đăng nhập.');
    } catch (PDOException $e) {
        json_response(false, 'Có lỗi xảy ra khi ghi dữ liệu tài khoản vào hệ thống!');
    }
} else {
    // 2. Chế độ chạy thử nghiệm Demo (Mô phỏng thành công để chuyển hướng giao diện)
    json_response(true, 'Đăng ký Demo thành công! Bạn có thể sử dụng thông tin đăng ký này hoặc tài khoản có sẵn (user@musicai.local / user123) để đăng nhập.');
}
