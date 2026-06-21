<?php
$page_title = 'Quản lý Thành viên';
require_once __DIR__ . '/admin_header.php';

$message = '';
$error = '';

// Xử lý các yêu cầu thay đổi qua POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // 1. Thêm thành viên mới
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ thông tin!';
            } elseif ($db_connected && $conn) {
                try {
                    // Kiểm tra email trùng
                    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $check->execute([$email]);
                    if ($check->fetch()) {
                        $error = 'Địa chỉ email này đã được sử dụng!';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$name, $email, $hash, $role]);
                        $message = 'Đã thêm thành viên mới thành công!';
                    }
                } catch (PDOException $e) {
                    $error = 'Lỗi DB: ' . $e->getMessage();
                }
            } else {
                $error = 'Không thể thêm thành viên ở chế độ Demo (chưa kết nối Database)!';
            }
        }
        
        // 2. Xóa thành viên
        if ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            if ($id === $_SESSION['user']['id']) {
                $error = 'Bạn không thể tự xóa chính mình!';
            } elseif ($db_connected && $conn) {
                try {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Đã xóa thành viên thành công!';
                } catch (PDOException $e) {
                    $error = 'Lỗi DB: ' . $e->getMessage();
                }
            } else {
                $error = 'Không thể xóa thành viên ở chế độ Demo!';
            }
        }
        
        // 3. Thay đổi vai trò (role toggle)
        if ($_POST['action'] === 'toggle_role') {
            $id = intval($_POST['id']);
            $role = $_POST['role'];
            if ($id === $_SESSION['user']['id']) {
                $error = 'Bạn không thể tự đổi quyền của mình!';
            } elseif ($db_connected && $conn) {
                try {
                    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$role, $id]);
                    $message = 'Cập nhật vai trò người dùng thành công!';
                } catch (PDOException $e) {
                    $error = 'Lỗi DB: ' . $e->getMessage();
                }
            } else {
                $error = 'Không thể cập nhật quyền ở chế độ Demo!';
            }
        }
    }
}

// Lấy danh sách tài khoản
$users = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Không thể truy vấn danh sách người dùng.';
    }
} else {
    // Demo Mock Array
    $users = [
        ['id' => 1, 'name' => 'Administrator', 'email' => 'admin@musicai.local', 'role' => 'admin', 'created_at' => '2026-06-20 12:00:00'],
        ['id' => 2, 'name' => 'Demo User', 'email' => 'user@musicai.local', 'role' => 'user', 'created_at' => '2026-06-20 12:30:00']
    ];
}
?>

<!-- Thông báo -->
<?php if (!empty($message)): ?>
    <div class="alert-box alert-success"><i class="fa-solid fa-check"></i> <?php echo $message; ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert-box alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="grid-3" style="grid-template-columns: 1fr 2fr; gap:30px; align-items: start;">
    <!-- Form thêm thành viên -->
    <div class="glass-card">
        <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-user-plus"></i> Thêm thành viên</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="name">Họ và tên</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@musicai.local" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
            </div>
            
            <div class="form-group">
                <label for="role">Vai trò</label>
                <select id="role" name="role" class="form-control">
                    <option value="user">Người dùng (User)</option>
                    <option value="admin">Quản trị viên (Admin)</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">
                <i class="fa-solid fa-check"></i> Thêm thành viên
            </button>
        </form>
    </div>

    <!-- Bảng danh sách thành viên -->
    <div class="admin-table-card" style="margin-top: 0;">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-list"></i> Danh sách thành viên</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Ngày tạo</th>
                    <th style="text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td><strong><?php echo sanitize($user['name']); ?></strong></td>
                        <td><?php echo sanitize($user['email']); ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge badge-success">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-warning">User</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                            <!-- Đổi vai trò -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_role">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <input type="hidden" name="role" value="user">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Hạ cấp xuống User" <?php echo $user['id'] === $_SESSION['user']['id'] ? 'disabled' : ''; ?>>
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="role" value="admin">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Thăng cấp lên Admin">
                                        <i class="fa-solid fa-angle-up"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                            
                            <!-- Xóa -->
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa thành viên này?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" title="Xóa tài khoản" <?php echo $user['id'] === $_SESSION['user']['id'] ? 'disabled' : ''; ?> style="border-color:#ff4d4d; color:#ff4d4d;">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
