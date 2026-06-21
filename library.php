<?php
$page_title = 'Thư viện của tôi - MusicAI';
$page_desc = 'Quản lý toàn bộ danh sách nhạc AI bạn đã tạo và các bản Beat Mix cá nhân đã lưu trữ.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Trang này bắt buộc phải đăng nhập
requireLogin();

$user_id = $_SESSION['user']['id'];
$ai_songs = [];
$beat_mixes = [];

// Truy vấn cơ sở dữ liệu nếu có kết nối
if ($db_connected && $conn) {
    try {
        // Lấy danh sách nhạc AI của người dùng
        $stmt = $conn->prepare("SELECT * FROM ai_songs WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$user_id]);
        $ai_songs = $stmt->fetchAll();
        
        // Lấy danh sách beat mix của người dùng
        $stmt = $conn->prepare("SELECT * FROM beat_mixes WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$user_id]);
        $beat_mixes = $stmt->fetchAll();
    } catch (PDOException $e) {
        $ai_songs = $fallback_ai_songs;
        $beat_mixes = $fallback_beat_mixes;
    }
} else {
    // Dữ liệu dự phòng
    $ai_songs = $fallback_ai_songs;
    $beat_mixes = $fallback_beat_mixes;
    
    // Ghép thêm nhạc AI sinh ra trong session khi chạy thử nghiệm
    if (isset($_SESSION['demo_ai_songs'])) {
        $ai_songs = array_merge($_SESSION['demo_ai_songs'], $ai_songs);
    }
    
    // Ghép thêm bản beat mix lưu trong session khi chạy thử nghiệm
    if (isset($_SESSION['demo_beat_mixes'])) {
        $beat_mixes = array_merge($_SESSION['demo_beat_mixes'], $beat_mixes);
    }
}
?>

<div class="container library-page-container">
    <h2 class="section-title"><i class="fa-solid fa-compact-disc"></i> Thư Viện Cá Nhân</h2>
    
    <div class="library-tabs-nav">
        <button class="lib-tab-btn active" onclick="switchLibraryTab('ai-music-tab', this)">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Nhạc AI Đã Tạo (<?php echo count($ai_songs); ?>)
        </button>
        <button class="lib-tab-btn" onclick="switchLibraryTab('beat-mix-tab', this)">
            <i class="fa-solid fa-sliders"></i> Bản Beat Mix Đã Lưu (<?php echo count($beat_mixes); ?>)
        </button>
    </div>

    <!-- Nội dung Tab 1: Nhạc AI Đã Tạo -->
    <div class="library-tab-content active" id="ai-music-tab">
        <?php if (empty($ai_songs)): ?>
            <div class="glass-card empty-library">
                <i class="fa-solid fa-music"></i>
                <h3>Chưa tạo bài nhạc AI nào</h3>
                <p>Bắt đầu ngay để sở hữu những tác phẩm âm nhạc độc quyền của riêng bạn.</p>
                <a href="<?php echo url('generate.php'); ?>" class="btn btn-primary" style="margin-top: 15px;">Tạo nhạc AI ngay</a>
            </div>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach ($ai_songs as $song): ?>
                    <?php
                        $song_data = [
                            'id' => 'ai_' . $song['id'],
                            'title' => 'AI Song #' . $song['id'],
                            'artist' => 'AI Composer',
                            'audio_file' => $song['audio_file'],
                            'thumbnail' => 'assets/images/default_song.jpg',
                            'is_favorite' => false
                        ];
                        $song_json = json_encode($song_data, JSON_UNESCAPED_UNICODE);
                    ?>
                    <div class="glass-card lib-item-card">
                        <div class="lib-item-header">
                            <div class="lib-item-icon ai-icon">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </div>
                            <div class="lib-item-meta">
                                <h3>Bài nhạc AI #<?php echo $song['id']; ?></h3>
                                <p>Tạo ngày: <?php echo date('d/m/Y H:i', strtotime($song['created_at'])); ?></p>
                            </div>
                            <button class="btn btn-primary btn-sm play-lib-btn" onclick='playTrackOnPlayer(<?php echo htmlspecialchars($song_json, ENT_QUOTES, 'UTF-8'); ?>)'>
                                <i class="fa-solid fa-play"></i> Phát
                            </button>
                        </div>
                        <div class="lib-item-body">
                            <p class="prompt-text"><strong>Prompt:</strong> "<?php echo sanitize($song['prompt']); ?>"</p>
                            <div class="meta-tags">
                                <span class="tag">Thể loại: <?php echo sanitize($song['genre']); ?></span>
                                <span class="tag">Tâm trạng: <?php echo sanitize($song['mood']); ?></span>
                                <span class="tag">Thời lượng: <?php echo $song['duration']; ?>s</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Nội dung Tab 2: Bản Beat Mix Đã Lưu -->
    <div class="library-tab-content" id="beat-mix-tab">
        <?php if (empty($beat_mixes)): ?>
            <div class="glass-card empty-library">
                <i class="fa-solid fa-sliders"></i>
                <h3>Chưa lưu bản Beat Mix nào</h3>
                <p>Hãy truy cập Beatmaker Studio để kéo thả các loop và sáng tạo giai điệu của riêng bạn.</p>
                <a href="<?php echo url('beatmaker.php'); ?>" class="btn btn-primary" style="margin-top: 15px;">Vào Beatmaker Studio</a>
            </div>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach ($beat_mixes as $mix): ?>
                    <div class="glass-card lib-item-card">
                        <div class="lib-item-header">
                            <div class="lib-item-icon mix-icon">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <div class="lib-item-meta">
                                <h3><?php echo sanitize($mix['name']); ?></h3>
                                <p>Lưu ngày: <?php echo date('d/m/Y H:i', strtotime($mix['created_at'])); ?></p>
                            </div>
                            <a href="<?php echo url('beatmaker.php?mix_id=' . $mix['id']); ?>" class="btn btn-primary btn-sm play-lib-btn">
                                <i class="fa-solid fa-folder-open"></i> Nạp Mix
                            </a>
                        </div>
                        <div class="lib-item-body">
                            <?php 
                                $mix_sounds = json_decode($mix['mix_data'], true);
                                $sound_count = is_array($mix_sounds) ? count($mix_sounds) : 0;
                            ?>
                            <p class="mix-details">Bản mix gồm <strong><?php echo $sound_count; ?></strong> sound loop mẫu đang được sử dụng.</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.library-tabs-nav {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    border-bottom: 1px solid var(--border-light);
    padding-bottom: 15px;
}

.lib-tab-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    font-family: var(--font-heading);
    font-weight: 600;
    font-size: 1.05rem;
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 20px;
    transition: all var(--transition-fast);
}

.lib-tab-btn:hover {
    color: var(--text-main);
    background: rgba(255, 255, 255, 0.05);
}

.lib-tab-btn.active {
    background: var(--green-main);
    color: var(--bg-main);
    box-shadow: 0 0 10px var(--green-glow);
}

.library-tab-content {
    display: none;
    animation: fadeIn 0.3s ease-out;
}

.library-tab-content.active {
    display: block;
}

.empty-library {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.empty-library i {
    font-size: 3.5rem;
    color: var(--green-dark);
    margin-bottom: 20px;
}

.empty-library h3 {
    color: var(--text-main);
    margin-bottom: 8px;
}

.lib-item-card {
    padding: 24px;
}

.lib-item-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.lib-item-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.lib-item-icon.ai-icon {
    background: rgba(0, 255, 136, 0.1);
    color: var(--green-main);
    border: 1px solid var(--border-green);
}

.lib-item-icon.mix-icon {
    background: rgba(51, 153, 255, 0.1);
    color: #3399ff;
    border: 1px solid rgba(51, 153, 255, 0.2);
}

.lib-item-meta {
    flex-grow: 1;
}

.lib-item-meta h3 {
    font-size: 1.15rem;
    margin-bottom: 3px;
}

.lib-item-meta p {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.play-lib-btn {
    align-self: center;
}

.lib-item-body {
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    padding-top: 15px;
}

.prompt-text {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-style: italic;
    margin-bottom: 12px;
}

.meta-tags {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.meta-tags .tag {
    font-size: 0.8rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--border-light);
    padding: 3px 10px;
    border-radius: 12px;
    color: var(--text-muted);
}

.mix-details {
    font-size: 0.9rem;
    color: var(--text-muted);
}
</style>

<script>
/**
 * Chuyển đổi qua lại giữa các tab thư viện
 */
function switchLibraryTab(tabId, btn) {
    // Ẩn tất cả nội dung tab
    document.querySelectorAll('.library-tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Gỡ class active khỏi tất cả các nút điều hướng
    document.querySelectorAll('.lib-tab-btn').forEach(b => {
        b.classList.remove('active');
    });
    
    // Kích hoạt tab và nút được chọn
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
