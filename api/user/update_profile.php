<?php
// API xử lý cập nhật thông tin cá nhân người dùng
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

// Chỉ cho phép POST request và phải đăng nhập
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Phương thức gửi yêu cầu không hợp lệ!');
}

if (!isLoggedIn()) {
    json_response(false, 'Bạn chưa đăng nhập hoặc phiên làm việc đã hết hạn!');
}

$user_id = $_SESSION['user']['id'];
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if (empty($name) || empty($email)) {
    json_response(false, 'Họ tên và email không được để trống!');
}

// 1. Nếu kết nối cơ sở dữ liệu thành công
if ($db_connected && $conn) {
    try {
        // Lấy thông tin tài khoản hiện tại từ database
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $db_user = $stmt->fetch();
        
        if (!$db_user) {
            json_response(false, 'Không tìm thấy tài khoản người dùng!');
        }

        // Kiểm tra mật khẩu hiện tại nếu đổi mật khẩu hoặc đổi thông tin quan trọng
        $password_changed = false;
        if (!empty($new_password)) {
            if (empty($current_password)) {
                json_response(false, 'Vui lòng nhập mật khẩu hiện tại để thay đổi mật khẩu!');
            }
            if (!password_verify($current_password, $db_user['password'])) {
                json_response(false, 'Mật khẩu hiện tại không chính xác!');
            }
            if (strlen($new_password) < 6) {
                json_response(false, 'Mật khẩu mới bắt buộc phải dài tối thiểu 6 ký tự!');
            }
            if ($new_password !== $confirm_password) {
                json_response(false, 'Xác nhận mật khẩu mới không khớp!');
            }
            $password_changed = true;
        }

        // Kiểm tra email trùng lặp nếu người dùng muốn thay đổi email
        if ($email !== $db_user['email']) {
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->execute([$email, $user_id]);
            if ($check_email->fetch()) {
                json_response(false, 'Địa chỉ email này đã được sử dụng bởi tài khoản khác!');
            }
        }

        // Xử lý upload avatar nếu có
        $avatar_path = $db_user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar_dir = dirname(dirname(__DIR__)) . '/storage/avatars';
            if (!is_dir($avatar_dir)) {
                mkdir($avatar_dir, 0777, true);
            }

            $img_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($img_ext, $allowed_types)) {
                json_response(false, 'Ảnh đại diện chỉ chấp nhận ảnh định dạng JPG, PNG, WEBP!');
            }

            // Kích thước tối đa 2MB
            if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                json_response(false, 'Ảnh đại diện có dung lượng không vượt quá 2MB!');
            }

            $avatar_name = 'user_' . $user_id . '_' . time() . '.' . $img_ext;
            $target_path = $avatar_dir . '/' . $avatar_name;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                // Xóa ảnh cũ nếu khác mặc định
                if (!empty($db_user['avatar']) && $db_user['avatar'] !== DEFAULT_AVATAR) {
                    $old_path = dirname(dirname(__DIR__)) . '/' . $db_user['avatar'];
                    if (file_exists($old_path) && is_file($old_path)) {
                        unlink($old_path);
                    }
                }
                $avatar_path = 'storage/avatars/' . $avatar_name;
            } else {
                json_response(false, 'Lỗi lưu trữ ảnh đại diện!');
            }
        }

        // Cập nhật thông tin vào DB
        if ($password_changed) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, avatar = ? WHERE id = ?");
            $update->execute([$name, $email, $new_hash, $avatar_path, $user_id]);
        } else {
            $update = $conn->prepare("UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?");
            $update->execute([$name, $email, $avatar_path, $user_id]);
        }

        // Cập nhật lại Session người dùng
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['avatar'] = $avatar_path;

        json_response(true, 'Cập nhật thông tin tài khoản thành công!', [
            'name' => $name,
            'email' => $email,
            'avatar' => url($avatar_path)
        ]);

    } catch (PDOException $e) {
        json_response(false, 'Lỗi cập nhật cơ sở dữ liệu: ' . $e->getMessage());
    }
} else {
    // 2. Chế độ chạy thử nghiệm Demo (Nếu không kết nối DB)
    // Cập nhật lại Session để giao diện thay đổi theo
    $avatar_path = $_SESSION['user']['avatar'];
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $avatar_dir = dirname(dirname(__DIR__)) . '/storage/avatars';
        if (!is_dir($avatar_dir)) {
            mkdir($avatar_dir, 0777, true);
        }
        $img_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $avatar_name = 'demo_user_' . time() . '.' . $img_ext;
        $target_path = $avatar_dir . '/' . $avatar_name;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
            $avatar_path = 'storage/avatars/' . $avatar_name;
        }
    }

    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['avatar'] = $avatar_path;

    json_response(true, 'Cập nhật tài khoản Demo thành công!', [
        'name' => $name,
        'email' => $email,
        'avatar' => url($avatar_path)
    ]);
}
