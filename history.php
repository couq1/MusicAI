<?php
$page_title = 'Lịch sử nghe nhạc - MusicAI';
$page_desc = 'Xem lại danh sách các bài hát bạn đã thưởng thức gần đây trên MusicAI.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Chỉ người dùng đã đăng nhập mới vào được trang này
requireLogin();

$user_id = $_SESSION['user']['id'];
$history_songs = [];

if ($db_connected && $conn) {
    try {
        // Lấy lịch sử nghe nhạc tham chiếu đến bảng songs và sắp xếp theo thời gian nghe gần nhất
        $stmt = $conn->prepare("SELECT s.*, h.listened_at 
                                FROM songs s 
                                JOIN listening_history h ON s.id = h.song_id 
                                WHERE h.user_id = ? 
                                ORDER BY h.listened_at DESC 
                                LIMIT 50");
        $stmt->execute([$user_id]);
        $history_songs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $history_songs = $fallback_songs;
    }
} else {
    // Dự phòng
    $history_songs = $fallback_songs;
}
?>

<div class="container history-page-container">
    <h2 class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Nghe Nhạc</h2>
    
    <?php if (empty($history_songs)): ?>
        <div class="glass-card empty-history">
            <i class="fa-solid fa-headphones-simple"></i>
            <h3>Lịch sử nghe trống</h3>
            <p>Hãy bắt đầu trải nghiệm nghe nhạc để lưu giữ lại những giai điệu bạn đã đi qua.</p>
            <a href="<?php echo url('music.php'); ?>" class="btn btn-primary" style="margin-top: 15px;">Nghe nhạc ngay</a>
        </div>
    <?php else: ?>
        <div class="glass-card history-list-card">
            <div class="history-list-header">
                <span class="col-title col-track">Bài hát</span>
                <span class="col-title col-genre">Thể loại</span>
                <span class="col-title col-time">Nghe lúc</span>
                <span class="col-title col-actions"></span>
            </div>
            
            <div class="history-items">
                <?php 
                    // Tạo playlist danh sách lịch sử phát liên tiếp
                    $playlist_data = [];
                    foreach ($history_songs as $s) {
                        $cover_src = 'assets/images/default_song.jpg';
                        if (!empty($s['thumbnail'])) {
                            $cover_src = (strpos($s['thumbnail'], 'assets/') === 0 || strpos($s['thumbnail'], 'storage/') === 0) 
                                ? $s['thumbnail'] 
                                : 'storage/thumbnails/' . $s['thumbnail'];
                        }
                        $playlist_data[] = [
                            'id' => $s['id'],
                            'title' => $s['title'],
                            'artist' => $s['artist'],
                            'audio_file' => $s['audio_file'],
                            'thumbnail' => $cover_src
                        ];
                    }
                    $playlist_json = json_encode($playlist_data, JSON_UNESCAPED_UNICODE);
                ?>
                
                <?php foreach ($history_songs as $song): ?>
                    <?php 
                        $cover_src = 'assets/images/default_song.jpg';
                        if (!empty($song['thumbnail'])) {
                            $cover_src = (strpos($song['thumbnail'], 'assets/') === 0 || strpos($song['thumbnail'], 'storage/') === 0) 
                                ? $song['thumbnail'] 
                                : 'storage/thumbnails/' . $song['thumbnail'];
                        }
                        
                        $song_data = [
                            'id' => $song['id'],
                            'title' => $song['title'],
                            'artist' => $song['artist'],
                            'audio_file' => $song['audio_file'],
                            'thumbnail' => $cover_src,
                            'is_favorite' => false
                        ];
                        
                        // Kiểm tra yêu thích
                        if ($db_connected) {
                            try {
                                $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND song_id = ?");
                                $stmt->execute([$user_id, $song['id']]);
                                if ($stmt->fetch()) {
                                    $song_data['is_favorite'] = true;
                                }
                            } catch (PDOException $e) {}
                        }
                        $song_json = json_encode($song_data, JSON_UNESCAPED_UNICODE);
                        
                        // Định dạng thời gian
                        $listened_time = isset($song['listened_at']) ? date('d/m/Y H:i', strtotime($song['listened_at'])) : 'Gần đây';
                    ?>
                    <div class="history-item">
                        <div class="col-track item-track-info">
                            <img src="<?php echo url($cover_src); ?>" alt="Cover" class="history-track-cover">
                            <div class="history-track-meta">
                                <span class="track-title"><?php echo sanitize($song['title']); ?></span>
                                <span class="track-artist"><?php echo sanitize($song['artist']); ?></span>
                            </div>
                        </div>
                        
                        <div class="col-genre">
                            <span class="genre-tag"><?php echo sanitize($song['genre']); ?></span>
                        </div>
                        
                        <div class="col-time">
                            <span class="time-text"><?php echo $listened_time; ?></span>
                        </div>
                        
                        <div class="col-actions">
                            <button class="btn btn-primary btn-sm play-history-btn" onclick='playTrackOnPlayer(<?php echo htmlspecialchars($song_json, ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($playlist_json, ENT_QUOTES, 'UTF-8'); ?>)'>
                                <i class="fa-solid fa-play"></i> Phát
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.empty-history {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.empty-history i {
    font-size: 3.5rem;
    color: var(--green-dark);
    margin-bottom: 20px;
}

.empty-history h3 {
    color: var(--text-main);
    margin-bottom: 8px;
}

.history-list-card {
    padding: 20px;
}

.history-list-header {
    display: flex;
    padding: 10px 15px;
    border-bottom: 1px solid var(--border-light);
    font-family: var(--font-heading);
    font-weight: 600;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.col-track { flex: 2; display: flex; align-items: center; }
.col-genre { flex: 1; }
.col-time { flex: 1.2; }
.col-actions { flex: 0.8; text-align: right; display: flex; justify-content: flex-end; }

.history-items {
    display: flex;
    flex-direction: column;
}

.history-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    transition: background-color var(--transition-fast);
    border-radius: 8px;
}

.history-item:hover {
    background-color: rgba(255, 255, 255, 0.02);
}

.history-track-cover {
    width: 44px;
    height: 44px;
    border-radius: 6px;
    object-fit: cover;
    margin-right: 15px;
}

.history-track-meta {
    display: flex;
    flex-direction: column;
}

.history-track-meta .track-title {
    font-weight: 600;
    color: var(--text-main);
    font-size: 0.95rem;
}

.history-track-meta .track-artist {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.time-text {
    font-size: 0.9rem;
    color: var(--text-muted);
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
