<?php
$page_title = 'Quản lý Bài hát';
require_once __DIR__ . '/admin_header.php';

$message = '';
$error = '';

// Xử lý POST request thêm/xóa bài hát
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        
        // 1. Xử lý Thêm bài hát
        if ($_POST['action'] === 'add') {
            $title = trim($_POST['title']);
            $artist = trim($_POST['artist']);
            $genre = trim($_POST['genre']);
            
            $audio_file = '';
            $thumbnail = '';
            
            // Tạo các thư mục lưu trữ nếu chưa tồn tại
            $audio_dir = dirname(__DIR__) . '/storage/audio';
            $thumb_dir = dirname(__DIR__) . '/storage/thumbnails';
            
            if (!is_dir($audio_dir)) {
                mkdir($audio_dir, 0777, true);
            }
            if (!is_dir($thumb_dir)) {
                mkdir($thumb_dir, 0777, true);
            }
            
            if (empty($title) || empty($artist) || empty($genre)) {
                $error = 'Vui lòng nhập đầy đủ tên bài hát, nghệ sĩ và thể loại!';
            } else {
                // Xử lý upload file âm thanh .mp3
                if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
                    $audio_ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
                    if (strtolower($audio_ext) !== 'mp3') {
                        $error = 'Hệ thống chỉ chấp nhận file định dạng .mp3!';
                    } else {
                        $audio_name = time() . '_' . uniqid() . '.mp3';
                        $target_audio = $audio_dir . '/' . $audio_name;
                        if (move_uploaded_file($_FILES['audio']['tmp_name'], $target_audio)) {
                            $audio_file = 'storage/audio/' . $audio_name;
                        } else {
                            $error = 'Lỗi lưu trữ file âm thanh!';
                        }
                    }
                } else {
                    $error = 'Vui lòng tải lên file âm thanh hợp lệ!';
                }
                
                // Xử lý upload ảnh bìa (thumbnail)
                if (empty($error) && isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                    $img_ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (!in_array($img_ext, $allowed_types)) {
                        $error = 'Ảnh bìa chỉ chấp nhận ảnh định dạng JPG, PNG, WEBP!';
                    } else {
                        $cover_name = time() . '_' . uniqid() . '.' . $img_ext;
                        $target_cover = $thumb_dir . '/' . $cover_name;
                        if (move_uploaded_file($_FILES['cover']['tmp_name'], $target_cover)) {
                            $thumbnail = 'storage/thumbnails/' . $cover_name;
                        }
                    }
                }
                
                // Thực thi chèn DB
                if (empty($error)) {
                    if ($db_connected && $conn) {
                        try {
                            $stmt = $conn->prepare("INSERT INTO songs (title, artist, genre, audio_file, thumbnail) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$title, $artist, $genre, $audio_file, $thumbnail]);
                            $message = 'Đã thêm bài hát mới thành công!';
                        } catch (PDOException $e) {
                            $error = 'Lỗi DB: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Không thể thêm bài hát ở chế độ Demo (chưa có kết nối Database)!';
                    }
                }
            }
        }
        
        // 2. Xử lý Xóa bài hát
        if ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            if ($db_connected && $conn) {
                try {
                    // Lấy thông tin bài hát để xóa file cứng
                    $stmt = $conn->prepare("SELECT audio_file, thumbnail FROM songs WHERE id = ?");
                    $stmt->execute([$id]);
                    $song = $stmt->fetch();
                    
                    if ($song) {
                        // Xóa bản ghi DB
                        $del = $conn->prepare("DELETE FROM songs WHERE id = ?");
                        $del->execute([$id]);
                        
                        // Xóa file âm thanh
                        $audio_path = dirname(__DIR__) . '/' . $song['audio_file'];
                        if (file_exists($audio_path) && is_file($audio_path)) {
                            unlink($audio_path);
                        }
                        
                        // Xóa ảnh bìa
                        if (!empty($song['thumbnail'])) {
                            $cover_path = dirname(__DIR__) . '/' . $song['thumbnail'];
                            if (file_exists($cover_path) && is_file($cover_path)) {
                                unlink($cover_path);
                            }
                        }
                        
                        $message = 'Đã xóa bài hát khỏi hệ thống thành công!';
                    }
                } catch (PDOException $e) {
                    $error = 'Lỗi DB: ' . $e->getMessage();
                }
            } else {
                $error = 'Không thể xóa bài hát ở chế độ Demo!';
            }
        }
    }
}

// Lấy danh sách bài hát hiện tại
$songs = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->query("SELECT * FROM songs ORDER BY id DESC");
        $songs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $songs = $fallback_songs;
    }
} else {
    $songs = $fallback_songs;
}
?>

<!-- Thông báo kết quả -->
<?php if (!empty($message)): ?>
    <div class="alert-box alert-success"><i class="fa-solid fa-check"></i> <?php echo $message; ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert-box alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="grid-3" style="grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
    <!-- Form upload bài hát mới -->
    <div class="glass-card">
        <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-cloud-arrow-up"></i> Đăng Tải Bài Hát</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="title">Tên bài hát</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="Ví dụ: Chill Vibe" required>
            </div>
            
            <div class="form-group">
                <label for="artist">Nghệ sĩ / Ca sĩ</label>
                <input type="text" id="artist" name="artist" class="form-control" placeholder="Ví dụ: Lofi Chill" required>
            </div>
            
            <div class="form-group">
                <label for="genre">Thể loại</label>
                <select id="genre" name="genre" class="form-control">
                    <option value="Lofi">Lofi</option>
                    <option value="EDM">EDM</option>
                    <option value="Trap">Trap</option>
                    <option value="Chill">Chill</option>
                    <option value="Pop">Pop</option>
                    <option value="Jazz">Jazz</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="audio">File âm thanh (Định dạng .mp3)</label>
                <input type="file" id="audio" name="audio" accept=".mp3" required style="border: 1px dashed rgba(0, 255, 136, 0.3); padding: 10px; border-radius: 8px; width: 100%;">
            </div>
            
            <div class="form-group">
                <label for="cover">Ảnh bìa bài hát (Hình vuông JPG, PNG, WEBP)</label>
                <input type="file" id="cover" name="cover" accept="image/*" style="border: 1px dashed rgba(0, 255, 136, 0.3); padding: 10px; border-radius: 8px; width: 100%;">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">
                <i class="fa-solid fa-check"></i> Đăng tải ngay
            </button>
        </form>
    </div>

    <!-- Danh sách bài hát -->
    <div class="admin-table-card" style="margin-top: 0;">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-list"></i> Danh sách bài hát gốc</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Bìa</th>
                    <th>Tên bài hát</th>
                    <th>Nghệ sĩ</th>
                    <th>Thể loại</th>
                    <th>Lượt nghe</th>
                    <th style="text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($songs as $song): ?>
                    <?php 
                        $cover_src = 'assets/images/default_song.jpg';
                        if (!empty($song['thumbnail'])) {
                            $cover_src = (strpos($song['thumbnail'], 'assets/') === 0 || strpos($song['thumbnail'], 'storage/') === 0) 
                                ? $song['thumbnail'] 
                                : 'storage/thumbnails/' . $song['thumbnail'];
                        }
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo url($cover_src); ?>" alt="Bìa" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;">
                        </td>
                        <td><strong><?php echo sanitize($song['title']); ?></strong></td>
                        <td><?php echo sanitize($song['artist']); ?></td>
                        <td><span class="genre-tag" style="background: rgba(0, 255, 136, 0.1); color: var(--green-main);"><?php echo sanitize($song['genre']); ?></span></td>
                        <td><i class="fa-solid fa-headphones"></i> <?php echo number_format($song['plays']); ?></td>
                        <td style="text-align: right;">
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa bài hát này khỏi hệ thống?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $song['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" title="Xóa bài hát" style="border-color:#ff4d4d; color:#ff4d4d;">
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
