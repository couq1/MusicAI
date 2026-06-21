<?php
$page_title = 'Beatmaker - MusicAI';
$page_desc = 'Sáng tạo các bản beat nhạc độc đáo theo phong cách Incredibox. Kéo thả âm thanh, kích hoạt loop và lưu bản mix cá nhân.';
$extra_css = ['beatmaker.css'];
$extra_js = ['dragdrop.js', 'beatmaker.js', 'visualizer.js'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Lấy danh sách beat sound mẫu từ database hoặc mảng dự phòng
$sounds = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->query("SELECT * FROM beat_sounds WHERE status = 'active' ORDER BY category, name");
        $sounds = $stmt->fetchAll();
    } catch (PDOException $e) {
        $sounds = $fallback_sounds;
    }
} else {
    $sounds = $fallback_sounds;
}

// Xử lý nạp bản Mix cũ nếu truyền mix_id qua URL GET
$preloaded_mix_sounds = [];
$mix_id = isset($_GET['mix_id']) ? intval($_GET['mix_id']) : 0;
if ($mix_id > 0 && isLoggedIn()) {
    if ($db_connected && $conn) {
        try {
            $stmt = $conn->prepare("SELECT mix_data FROM beat_mixes WHERE id = ? AND user_id = ?");
            $stmt->execute([$mix_id, $_SESSION['user']['id']]);
            $mix = $stmt->fetch();
            if ($mix) {
                $sound_ids = json_decode($mix['mix_data'], true);
                if (is_array($sound_ids) && !empty($sound_ids)) {
                    // Lấy danh sách bài hát sử dụng
                    $placeholders = implode(',', array_fill(0, count($sound_ids), '?'));
                    $stmt_sounds = $conn->prepare("SELECT * FROM beat_sounds WHERE id IN ($placeholders)");
                    $stmt_sounds->execute($sound_ids);
                    $preloaded_mix_sounds = $stmt_sounds->fetchAll();
                }
            }
        } catch (PDOException $e) {}
    } else {
        // Lấy từ session giả lập nếu chưa có DB
        if (isset($_SESSION['demo_beat_mixes'])) {
            foreach ($_SESSION['demo_beat_mixes'] as $dm) {
                if ($dm['id'] == $mix_id && $dm['user_id'] == $_SESSION['user']['id']) {
                    $sound_ids = json_decode($dm['mix_data'], true);
                    if (is_array($sound_ids)) {
                        foreach ($sound_ids as $sid) {
                            foreach ($fallback_sounds as $fs) {
                                if ($fs['id'] == $sid) {
                                    $preloaded_mix_sounds[] = $fs;
                                }
                            }
                        }
                    }
                    break;
                }
            }
        }
    }
}
?>

<script>
    // Truyền dữ liệu mix nạp sẵn xuống client JS
    window.PRELOADED_MIX = <?php echo json_encode($preloaded_mix_sounds, JSON_UNESCAPED_UNICODE); ?>;
</script>

<div class="container" id="beatmakerContainer">
    <div class="beatmaker-layout">
        <!-- Tiêu đề & Thanh điều khiển toàn cục -->
        <div class="beatmaker-controls-bar glass-panel">
            <h2 class="section-title" style="margin-bottom: 0;"><i class="fa-solid fa-shapes"></i> Beatmaker Studio</h2>
            <div class="beatmaker-btn-group">
                <button class="btn btn-secondary" id="bmPlayAll"><i class="fa-solid fa-play"></i> Phát tất cả</button>
                <button class="btn btn-secondary" id="bmStopAll"><i class="fa-solid fa-stop"></i> Tạm dừng</button>
                <button class="btn btn-secondary" id="bmReset"><i class="fa-solid fa-rotate-left"></i> Reset Mix</button>
                <button class="btn btn-primary" id="bmSaveMix"><i class="fa-solid fa-cloud-arrow-up"></i> Lưu bản Mix</button>
            </div>
        </div>

        <!-- Khu vực hiển thị visualizer sóng nhạc toàn bản phối -->
        <div class="glass-card beatmaker-visualizer-card">
            <canvas id="beatmakerCanvas" class="beatmaker-canvas"></canvas>
        </div>

        <!-- Khu vực các Slots nhân vật kéo thả -->
        <div class="slots-container">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="beatmaker-slot" data-slot-id="<?php echo $i; ?>">
                    <span class="slot-index">Bot <?php echo $i; ?></span>
                    <button class="remove-sound-btn" title="Gỡ âm thanh này"><i class="fa-solid fa-xmark"></i></button>
                    
                    <!-- Avatar nhân vật sẽ chuyển động khi hoạt động -->
                    <div class="slot-avatar">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    
                    <span class="slot-sound-name">Kéo sound vào đây</span>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Bảng danh sách các sound mẫu chia theo tab -->
        <div class="glass-card sound-palette">
            <div class="palette-tabs">
                <button class="palette-tab active" data-tab="all">Tất cả</button>
                <button class="palette-tab" data-tab="drums" style="color: #ff4d4d;"><i class="fa-solid fa-drum"></i> Drums</button>
                <button class="palette-tab" data-tab="bass" style="color: #3399ff;"><i class="fa-solid fa-guitar"></i> Bass</button>
                <button class="palette-tab" data-tab="melody" style="color: #00ff88;"><i class="fa-solid fa-music"></i> Melody</button>
                <button class="palette-tab" data-tab="vocals" style="color: #ffcc00;"><i class="fa-solid fa-microphone-lines"></i> Vocals</button>
                <button class="palette-tab" data-tab="effects" style="color: #9933ff;"><i class="fa-solid fa-wand-magic-sparkles"></i> Effects</button>
            </div>

            <div class="sounds-grid" id="soundsGrid">
                <?php foreach ($sounds as $snd): ?>
                    <div class="sound-item" 
                         draggable="true" 
                         data-id="<?php echo $snd['id']; ?>" 
                         data-name="<?php echo sanitize($snd['name']); ?>" 
                         data-category="<?php echo sanitize($snd['category']); ?>" 
                         data-audio-file="<?php echo sanitize($snd['audio_file']); ?>">
                        
                        <div class="sound-item-name" title="Kéo thả sound này">
                            <?php echo sanitize($snd['name']); ?>
                        </div>
                        
                        <!-- Nút play preview nhỏ tại chỗ -->
                        <button class="sound-item-play-preview" title="Nghe thử nhanh">
                            <i class="fa-solid fa-play"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
