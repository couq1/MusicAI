<?php
$page_title = 'Dashboard Thống Kê';
require_once __DIR__ . '/admin_header.php';

// Khởi tạo các số liệu mặc định dự phòng
$total_users = 0;
$total_songs = 0;
$total_ai_songs = 0;
$total_beat_mixes = 0;
$total_plays = 0;
$top_song = null;

if ($db_connected && $conn) {
    try {
        // Đếm tổng số người dùng
        $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        // Đếm tổng số bài hát
        $total_songs = $conn->query("SELECT COUNT(*) FROM songs")->fetchColumn();
        
        // Đếm tổng số nhạc AI
        $total_ai_songs = $conn->query("SELECT COUNT(*) FROM ai_songs")->fetchColumn();
        
        // Đếm tổng số beat mix
        $total_beat_mixes = $conn->query("SELECT COUNT(*) FROM beat_mixes")->fetchColumn();
        
        // Cộng tổng lượt nghe
        $total_plays = $conn->query("SELECT SUM(plays) FROM songs")->fetchColumn() ?: 0;
        
        // Truy vấn bài hát nghe nhiều nhất
        $stmt = $conn->query("SELECT * FROM songs ORDER BY plays DESC LIMIT 1");
        $top_song = $stmt->fetch();
    } catch (PDOException $e) {
        $total_users = 25;
        $total_songs = count($fallback_songs);
        $total_ai_songs = count($fallback_ai_songs);
        $total_beat_mixes = count($fallback_beat_mixes);
        $total_plays = 5320;
        $top_song = $fallback_songs[3]; // Hard rap Anthem (2450 plays)
    }
} else {
    // Dữ liệu dự phòng khi chưa cài DB
    $total_users = 25;
    $total_songs = count($fallback_songs);
    $total_ai_songs = count($fallback_ai_songs);
    $total_beat_mixes = count($fallback_beat_mixes);
    $total_plays = 5320;
    $top_song = $fallback_songs[3]; // Hard rap Anthem
}
?>

<!-- Thống kê dạng Card -->
<div class="admin-stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h4>Tổng người dùng</h4>
            <div class="stat-value"><?php echo number_format($total_users); ?></div>
        </div>
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Tổng bài hát gốc</h4>
            <div class="stat-value"><?php echo number_format($total_songs); ?></div>
        </div>
        <div class="stat-icon"><i class="fa-solid fa-music"></i></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Nhạc AI đã tạo</h4>
            <div class="stat-value"><?php echo number_format($total_ai_songs); ?></div>
        </div>
        <div class="stat-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Bản Beat Mix</h4>
            <div class="stat-value"><?php echo number_format($total_beat_mixes); ?></div>
        </div>
        <div class="stat-icon"><i class="fa-solid fa-sliders"></i></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Tổng lượt nghe</h4>
            <div class="stat-value"><?php echo number_format($total_plays); ?></div>
        </div>
        <div class="stat-icon"><i class="fa-solid fa-headphones"></i></div>
    </div>
</div>

<div class="grid-2">
    <!-- Bài hát nghe nhiều nhất -->
    <div class="glass-card">
        <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-crown" style="color: #ffcc00;"></i> Bài Hát Được Nghe Nhiều Nhất</h3>
        <?php if ($top_song): ?>
            <?php 
                $cover_src = 'assets/images/default_song.jpg';
                if (!empty($top_song['thumbnail'])) {
                    $cover_src = (strpos($top_song['thumbnail'], 'assets/') === 0 || strpos($top_song['thumbnail'], 'storage/') === 0) 
                        ? $top_song['thumbnail'] 
                        : 'storage/thumbnails/' . $top_song['thumbnail'];
                }
            ?>
            <div style="display: flex; align-items: center; gap: 20px; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(0, 255, 136, 0.1);">
                <img src="<?php echo url($cover_src); ?>" alt="Cover" style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-light);">
                <div style="flex-grow: 1;">
                    <h4 style="font-size: 1.2rem; margin-bottom: 5px;"><?php echo sanitize($top_song['title']); ?></h4>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 8px;">Nghệ sĩ: <?php echo sanitize($top_song['artist']); ?></p>
                    <span class="genre-tag"><?php echo sanitize($top_song['genre']); ?></span>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--green-main);"><i class="fa-solid fa-play"></i> <?php echo number_format($top_song['plays']); ?></div>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">lượt nghe</span>
                </div>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted);">Chưa có dữ liệu bài hát.</p>
        <?php endif; ?>
    </div>

    <!-- Hướng dẫn quản trị nhanh -->
    <div class="glass-card">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-circle-info"></i> Hệ Thống Quản Trị</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 15px; line-height: 1.6;">
            Chào mừng bạn đến với trang quản trị dự án MusicAI. Tại đây bạn có thể thêm mới bài hát của hệ thống, quản lý quyền hạn của người dùng, xem xét dữ liệu nhạc do AI của thành viên tạo ra, cấu hình các sound beat mẫu cho Beatmaker Studio và điều chỉnh hoạt động của website.
        </p>
        <div style="display: flex; gap: 10px;">
            <a href="<?php echo url('admin/songs.php'); ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-music"></i> Quản lý nhạc</a>
            <a href="<?php echo url('admin/users.php'); ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-users"></i> Quản lý user</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
