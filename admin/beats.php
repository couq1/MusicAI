<?php
$page_title = 'Quản lý Beatmaker Sounds';
require_once __DIR__ . '/admin_header.php';

$message = '';
$error = '';

// Xử lý các POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        
        // 1. Thêm Beat sound loop mới
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $category = trim($_POST['category']);
            $status = isset($_POST['status']) ? 'active' : 'inactive';
            
            $audio_file = '';
            $beats_dir = dirname(__DIR__) . '/storage/beats';
            
            if (!is_dir($beats_dir)) {
                mkdir($beats_dir, 0777, true);
            }
            
            if (empty($name) || empty($category)) {
                $error = 'Vui lòng điền đầy đủ tên và chọn thể loại âm thanh!';
            } else {
                // Xử lý upload loop file
                if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
                    $audio_ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
                    $allowed_formats = ['mp3', 'wav', 'ogg'];
                    
                    if (!in_array($audio_ext, $allowed_formats)) {
                        $error = 'Chấp nhận các định dạng file âm thanh: .mp3, .wav, .ogg!';
                    } else {
                        $file_name = time() . '_' . uniqid() . '.' . $audio_ext;
                        $target_path = $beats_dir . '/' . $file_name;
                        
                        if (move_uploaded_file($_FILES['audio']['tmp_name'], $target_path)) {
                            $audio_file = 'storage/beats/' . $file_name;
                        } else {
                            $error = 'Gặp lỗi khi di chuyển file loop vào thư mục lưu trữ!';
                        }
                    }
                } else {
                    $error = 'Vui lòng tải lên file âm thanh loop của sound!';
                }
                
                // Lưu vào DB
                if (empty($error)) {
                    if ($db_connected && $conn) {
                        try {
                            $stmt = $conn->prepare("INSERT INTO beat_sounds (name, category, audio_file, status) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$name, $category, $audio_file, $status]);
                            $message = 'Đã thêm beat sound loop mới thành công!';
                        } catch (PDOException $e) {
                            $error = 'Lỗi DB: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Không thể thêm sound ở chế độ Demo (chưa kết nối Database)!';
                    }
                }
            }
        }
        
        // 2. Xóa Beat sound loop
        if ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            
            if ($db_connected && $conn) {
                try {
                    // Lấy thông tin bài hát để xóa file
                    $stmt = $conn->prepare("SELECT audio_file FROM beat_sounds WHERE id = ?");
                    $stmt->execute([$id]);
                    $snd = $stmt->fetch();
                    
                    if ($snd) {
                        $del = $conn->prepare("DELETE FROM beat_sounds WHERE id = ?");
                        $del->execute([$id]);
                        
                        // Xóa file cứng
                        $file_path = dirname(__DIR__) . '/' . $snd['audio_file'];
                        if (file_exists($file_path) && is_file($file_path)) {
                            unlink($file_path);
                        }
                        
                        $message = 'Đã xóa beat sound thành công!';
                    }
                } catch (PDOException $e) {
                    $error = 'Lỗi DB: ' . $e->getMessage();
                }
            } else {
                $error = 'Không thể xóa sound ở chế độ Demo!';
            }
        }
    }
}

// Lấy danh sách sound loop hiện có
$sounds = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->query("SELECT * FROM beat_sounds ORDER BY category, name");
        $sounds = $stmt->fetchAll();
    } catch (PDOException $e) {
        $sounds = $fallback_sounds;
    }
} else {
    $sounds = $fallback_sounds;
}
?>

<!-- Thông báo -->
<?php if (!empty($message)): ?>
    <div class="alert-box alert-success"><i class="fa-solid fa-check"></i> <?php echo $message; ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert-box alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="grid-3" style="grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
    <!-- Form thêm loop mới -->
    <div class="glass-card">
        <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-plus"></i> Đăng Ký Beat Sound</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="name">Tên Sound Loop</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Ví dụ: Lofi Kick 01" required>
            </div>
            
            <div class="form-group">
                <label for="category">Danh mục Sound</label>
                <select id="category" name="category" class="form-control">
                    <option value="drums">Drums (Trống)</option>
                    <option value="bass">Bass (Âm trầm)</option>
                    <option value="melody">Melody (Giai điệu)</option>
                    <option value="vocals">Vocals (Giọng hát)</option>
                    <option value="effects">Effects (Hiệu ứng âm thanh)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="audio">File âm thanh Loop (.mp3, .wav, .ogg)</label>
                <input type="file" id="audio" name="audio" accept=".mp3,.wav,.ogg" required style="border: 1px dashed rgba(0, 255, 136, 0.3); padding: 10px; border-radius: 8px; width: 100%;">
            </div>
            
            <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top: 15px;">
                <input type="checkbox" id="status" name="status" checked style="width:18px; height:18px;">
                <label for="status" style="margin-bottom:0; cursor:pointer;">Kích hoạt sử dụng ngay</label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">
                <i class="fa-solid fa-check"></i> Đăng ký sound
            </button>
        </form>
    </div>

    <!-- Bảng danh sách sound loops -->
    <div class="admin-table-card" style="margin-top: 0;">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-shapes"></i> Danh sách beat sound</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tên sound</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Nghe thử</th>
                    <th style="text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sounds as $snd): ?>
                    <tr>
                        <td><strong><?php echo sanitize($snd['name']); ?></strong></td>
                        <td>
                            <span class="genre-tag" style="background: rgba(255, 255, 255, 0.05); color: var(--text-main);">
                                <?php echo strtoupper($snd['category']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($snd['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <audio src="<?php echo url($snd['audio_file']); ?>" controls style="height:32px; width:150px; outline:none; background:#07130f; border-radius:4px;"></audio>
                        </td>
                        <td style="text-align: right;">
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa sound loop này?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $snd['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" title="Xóa sound" style="border-color:#ff4d4d; color:#ff4d4d;">
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
