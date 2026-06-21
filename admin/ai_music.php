<?php
$page_title = 'Quản lý Nhạc AI tự tạo';
require_once __DIR__ . '/admin_header.php';

$message = '';
$error = '';

// Xử lý hành động xóa nhạc AI từ Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        
        if ($db_connected && $conn) {
            try {
                // Lấy thông tin đường dẫn file âm thanh để dọn dẹp ổ cứng
                $stmt = $conn->prepare("SELECT audio_file FROM ai_songs WHERE id = ?");
                $stmt->execute([$id]);
                $song = $stmt->fetch();
                
                if ($song) {
                    // Xóa bản ghi
                    $del = $conn->prepare("DELETE FROM ai_songs WHERE id = ?");
                    $del->execute([$id]);
                    
                    // Xóa file âm thanh AI sinh ra
                    $file_path = dirname(__DIR__) . '/' . $song['audio_file'];
                    if (file_exists($file_path) && is_file($file_path)) {
                        unlink($file_path);
                    }
                    
                    $message = 'Đã xóa bài nhạc AI khỏi hệ thống!';
                }
            } catch (PDOException $e) {
                $error = 'Lỗi DB: ' . $e->getMessage();
            }
        } else {
            $error = 'Không thể xóa bài hát ở chế độ Demo!';
        }
    }
}

// Lấy danh sách nhạc AI cùng thông tin người tạo
$ai_songs = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->query("SELECT a.*, u.name as user_name, u.email as user_email 
                                FROM ai_songs a 
                                LEFT JOIN users u ON a.user_id = u.id 
                                ORDER BY a.id DESC");
        $ai_songs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $ai_songs = $fallback_ai_songs;
    }
} else {
    // Dự phòng
    $ai_songs = $fallback_ai_songs;
}
?>

<!-- Thông báo -->
<?php if (!empty($message)): ?>
    <div class="alert-box alert-success"><i class="fa-solid fa-check"></i> <?php echo $message; ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert-box alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-table-card" style="margin-top: 0;">
    <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-list-check"></i> Danh sách nhạc AI được sinh bởi thành viên</h3>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Người tạo</th>
                <th>Prompt mô tả</th>
                <th>Thông số AI</th>
                <th>Audio phát thử</th>
                <th>Thời gian tạo</th>
                <th style="text-align: right;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ai_songs as $song): ?>
                <?php 
                    $creator = isset($song['user_name']) ? sanitize($song['user_name']) : 'User Demo';
                    $creator_email = isset($song['user_email']) ? ' (' . sanitize($song['user_email']) . ')' : '';
                    
                    // Chuẩn bị link phát thử
                    $audio_url = url($song['audio_file']);
                ?>
                <tr>
                    <td>#<?php echo $song['id']; ?></td>
                    <td>
                        <strong><?php echo $creator; ?></strong>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $creator_email; ?></div>
                    </td>
                    <td style="max-width: 250px; font-style: italic; font-size: 0.85rem;" title="<?php echo sanitize($song['prompt']); ?>">
                        "<?php echo sanitize($song['prompt']); ?>"
                    </td>
                    <td>
                        <div style="font-size: 0.8rem;">Thể loại: <strong><?php echo sanitize($song['genre']); ?></strong></div>
                        <div style="font-size: 0.8rem;">Mood: <strong><?php echo sanitize($song['mood']); ?></strong></div>
                        <div style="font-size: 0.8rem;">Thời lượng: <strong><?php echo $song['duration']; ?>s</strong></div>
                    </td>
                    <td>
                        <audio src="<?php echo $audio_url; ?>" controls style="height:32px; width:180px; max-width:100%; border-radius:4px; outline:none; background:#07130f;"></audio>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($song['created_at'])); ?></td>
                    <td style="text-align: right;">
                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa tác phẩm AI này?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $song['id']; ?>">
                            <button type="submit" class="btn btn-outline btn-sm" title="Xóa nhạc AI" style="border-color:#ff4d4d; color:#ff4d4d;">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
