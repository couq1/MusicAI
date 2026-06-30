<?php
$page_title = 'Trang chủ - MusicAI';
$page_desc = 'Trải nghiệm nghe nhạc chất lượng cao, tạo nhạc AI độc đáo và chơi beat mixer cực đỉnh.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Lấy danh sách bài hát nổi bật
$featured_songs = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->query("SELECT * FROM songs ORDER BY plays DESC LIMIT 4");
        $featured_songs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $featured_songs = array_slice($fallback_songs, 0, 4);
    }
} else {
    $featured_songs = array_slice($fallback_songs, 0, 4);
}
?>

<!-- Hero Section -->
<section class="hero-section liquid-ether-hero">
    <canvas id="liquidEtherCanvas"></canvas>
    <div class="liquid-ether-overlay"></div>
    <div class="hero-glow"></div>

    <div class="container hero-content">
        <h1 class="hero-title">Make Music<span class="highlight">Feel The Beat</span></h1>
        <p class="hero-subtitle">Nghe nhạc chất lượng cao, tạo các bài hát độc quyền từ văn bản prompt và khám phá thế giới beatbox tương tác sinh động.</p>
        <div class="hero-actions">
            <a href="<?php echo url('music.php'); ?>" class="btn btn-primary btn-lg"><i class="fa-solid fa-play"></i> Nghe nhạc ngay</a>
            <a href="<?php echo url('generate.php'); ?>" class="btn btn-outline btn-lg"><i class="fa-solid fa-wand-magic-sparkles"></i> Tạo nhạc AI</a>
            <a href="<?php echo url('beatmaker.php'); ?>" class="btn btn-secondary btn-lg"><i class="fa-solid fa-shapes"></i> Chơi Beatmaker</a>
        </div>
    </div>
</section>

<!-- Features Info Section -->
<section class="container features-section">
    <h2 class="section-title"><i class="fa-solid fa-cubes"></i> 3 Core Experiences</h2>
    <div class="grid-3">
        <div class="glass-card feature-card">
            <div class="feature-icon"><i class="fa-solid fa-music"></i></div>
            <h3>Advanced Music Player</h3>
            <p>Trải nghiệm thư viện âm nhạc đa dạng các thể loại, chất lượng âm thanh HD cùng trình phát nhạc cố định mượt mà ở chân trang.</p>
            <a href="<?php echo url('music.php'); ?>" class="feature-link">Khám phá ngay <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        
        <div class="glass-card feature-card">
            <div class="feature-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <h3>AI Music Generator</h3>
            <p>Chỉ cần nhập mô tả (prompt), chọn thể loại, tâm trạng, AI Generator của chúng tôi sẽ tự động sáng tạo bài hát của riêng bạn sau vài giây.</p>
            <a href="<?php echo url('generate.php'); ?>" class="feature-link">Sáng tạo ngay <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        
        <div class="glass-card feature-card">
            <div class="feature-icon"><i class="fa-solid fa-shapes"></i></div>
            <h3>Incredibox-Style Beat Mixer</h3>
            <p>Tự tay làm producer bằng cách kéo thả các vòng lặp drums, bass, vocals, effects vào các slot để tạo nên bản phối tuyệt đỉnh.</p>
            <a href="<?php echo url('beatmaker.php'); ?>" class="feature-link">Chơi beat ngay <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Featured Tracks Section -->
<section class="container featured-tracks-section">
    <div class="section-header-row">
        <h2 class="section-title"><i class="fa-solid fa-star"></i> Featured Tracks</h2>
        <a href="<?php echo url('music.php'); ?>" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="grid-4">
        <?php foreach ($featured_songs as $song): ?>
            <?php 
                // Xử lý đường dẫn ảnh cover
                $cover_src = 'assets/images/default_song.jpg';
                if (!empty($song['thumbnail'])) {
                    $cover_src = (strpos($song['thumbnail'], 'assets/') === 0 || strpos($song['thumbnail'], 'storage/') === 0) 
                        ? $song['thumbnail'] 
                        : 'storage/thumbnails/' . $song['thumbnail'];
                }
                
                // Chuẩn bị dữ liệu bài hát để chuyển cho JS phát nhạc
                $song_data = [
                    'id' => $song['id'],
                    'title' => $song['title'],
                    'artist' => $song['artist'],
                    'audio_file' => $song['audio_file'],
                    'thumbnail' => $cover_src,
                    'is_favorite' => false
                ];
                
                // Kiểm tra xem bài hát này có được yêu thích bởi user không
                if (isLoggedIn() && $db_connected) {
                    try {
                        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND song_id = ?");
                        $stmt->execute([$_SESSION['user']['id'], $song['id']]);
                        if ($stmt->fetch()) {
                            $song_data['is_favorite'] = true;
                        }
                    } catch (PDOException $e) {}
                }
                
                $song_json = json_encode($song_data, JSON_UNESCAPED_UNICODE);
                // Tạo playlist từ danh sách nổi bật này
                $playlist_data = [];
                foreach ($featured_songs as $fs) {
                    $p_cover = 'assets/images/default_song.jpg';
                    if (!empty($fs['thumbnail'])) {
                        $p_cover = (strpos($fs['thumbnail'], 'assets/') === 0 || strpos($fs['thumbnail'], 'storage/') === 0) 
                            ? $fs['thumbnail'] 
                            : 'storage/thumbnails/' . $fs['thumbnail'];
                    }
                    $playlist_data[] = [
                        'id' => $fs['id'],
                        'title' => $fs['title'],
                        'artist' => $fs['artist'],
                        'audio_file' => $fs['audio_file'],
                        'thumbnail' => $p_cover
                    ];
                }
                $playlist_json = json_encode($playlist_data, JSON_UNESCAPED_UNICODE);
            ?>
            <div class="glass-card song-card">
                <div class="song-cover-wrapper">
                    <img src="<?php echo url($cover_src); ?>" alt="<?php echo sanitize($song['title']); ?>" class="song-card-cover">
                    <button class="song-play-overlay-btn" onclick='playTrackOnPlayer(<?php echo htmlspecialchars($song_json, ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($playlist_json, ENT_QUOTES, 'UTF-8'); ?>)'>
                        <i class="fa-solid fa-play"></i>
                    </button>
                </div>
                <div class="song-card-info">
                    <h4 class="song-card-title"><?php echo sanitize($song['title']); ?></h4>
                    <p class="song-card-artist"><?php echo sanitize($song['artist']); ?></p>
                </div>
                <div class="song-card-meta">
                    <span class="genre-tag"><?php echo sanitize($song['genre']); ?></span>
                    <span class="plays-count"><i class="fa-solid fa-headphones"></i> <?php echo number_format($song['plays']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Additional Custom Styles for Home Page -->
<style>
.hero-section {
    position: relative;
    padding: 100px 0 140px 0;
    text-align: center;
    overflow: hidden;
    background: #120f17;
}

.liquid-ether-hero {
    position: relative;
    overflow: hidden;
    background: #120f17;
    isolation: isolate;
}

#liquidEtherCanvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
    z-index: 0;
}

.liquid-ether-overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background:
    radial-gradient(circle at 50% 50%, rgba(0,0,0,0.02), rgba(0,0,0,0.28) 75%),
    linear-gradient(to bottom, rgba(0,0,0,0.08), rgba(0,0,0,0.25));
}

.hero-glow {
    position: absolute;
    bottom: -10%;
    left: 50%;
    transform: translateX(-50%);
    width: 600px;
    height: 300px;
    background: radial-gradient(circle, var(--green-main) 0%, transparent 70%);
    opacity: 0.12;
    filter: blur(80px);
    pointer-events: none;
    z-index: 2;
}

.hero-content {
    position: relative;
    z-index: 5;
}
.hero-title {
    font-family: 'Kaushan Script', cursive;
    font-size: clamp(42px, 6vw, 72px);
    font-weight: 800;
    line-height: 1.12;
    letter-spacing: 1px;
    text-align: center;
    color: #f4fff9;
    margin-bottom: 30px;
}

.hero-script {
    display: block;
    margin-top: 20px;
    font-family: 'Kaushan Script', cursive;
    font-weight: 800;
    font-size: clamp(34px, 5vw, 58px);
    color: #7cf4b2;
    text-shadow: 0 0 14px rgba(35, 85, 62, 0.22);
}

.hero-subtitle {
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 1.25rem;
    color: rgba(97, 110, 98, 0.69);  
    max-width: 720px;
    margin: 0 auto 40px;

}

.hero-actions {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.feature-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 35px 30px;
}

.feature-icon {
    font-size: 2.2rem;
    color: var(--green-main);
    margin-bottom: 20px;
    filter: drop-shadow(0 0 8px var(--green-main));
}

.feature-card h3 {
    font-size: 1.35rem;
    margin-bottom: 12px;
}

.feature-card p {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin-bottom: 25px;
    flex-grow: 1;
}

.feature-link {
    color: var(--green-main);
    font-weight: 600;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.feature-link i {
    transition: transform 0.2s;
}

.feature-card:hover .feature-link i {
    transform: translateX(5px);
}

.section-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.section-header-row .section-title {
    margin-bottom: 0;
}

/* Song Card Styles */
.song-card {
    padding: 16px;
    border-radius: 16px;
}

.song-cover-wrapper {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.song-card-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.song-play-overlay-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--green-main);
    color: var(--bg-main);
    border: none;
    cursor: pointer;
    font-size: 1.25rem;     
    display: flex;  
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 0 15px var(--green-main);
}

.song-cover-wrapper:hover .song-card-cover {
    transform: scale(1.08);
}

.song-cover-wrapper:hover .song-play-overlay-btn {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.song-card-info {
    margin-bottom: 10px;
}

.song-card-title {
    font-size: 1.05rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.song-card-artist {
    font-size: 0.85rem;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.song-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
}

.genre-tag {
    background: var(--green-dark);
    color: var(--green-main);
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 500;
}

.plays-count {
    color: var(--text-muted);
}
</style>

<!-- animation cho section1-->

<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('liquidEtherCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let w = 0;
    let h = 0;
    let time = 0;

    let mouseX = 0;
    let mouseY = 0;
    let smoothMouseX = 0;
    let smoothMouseY = 0;

    const blobs = [];
    const blobCount = 14;

    function resizeCanvas() {
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();

        w = rect.width;
        h = rect.height;

        canvas.width = w * dpr;
        canvas.height = h * dpr;

        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        mouseX = w * 0.5;
        mouseY = h * 0.5;
        smoothMouseX = w * 0.5;
        smoothMouseY = h * 0.5;
    }

    function initBlobs() {
        blobs.length = 0;

        const colors = [
            'rgba(32, 77, 62, 0.36)',
            'rgba(20, 53, 25, 0.34)',
            'rgba(20, 61, 35, 0.26)',
            'rgba(11, 81, 38, 0.24)'
        ];

        for (let i = 0; i < blobCount; i++) {
            blobs.push({
                x: Math.random() * w,
                y: Math.random() * h,
                baseR: 140 + Math.random() * 180,
                vx: (-0.25 + Math.random() * 0.5),
                vy: (-0.18 + Math.random() * 0.36),
                phase: Math.random() * Math.PI * 2,
                color: colors[i % colors.length]
            });
        }
    }

    resizeCanvas();
    initBlobs();

    window.addEventListener('resize', () => {
        resizeCanvas();
        initBlobs();
    });

    document.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouseX = e.clientX - rect.left;
        mouseY = e.clientY - rect.top;
    });

    function drawBlob(blob, i) {
    blob.x += blob.vx;
    blob.y += blob.vy;

    if (blob.x < -260) blob.x = w + 260;
    if (blob.x > w + 260) blob.x = -260;
    if (blob.y < -260) blob.y = h + 260;
    if (blob.y > h + 260) blob.y = -260;

    const pulse = Math.sin(time * 0.001 + blob.phase) * 35;
    const radius = blob.baseR + pulse;

    const dx = smoothMouseX - blob.x;
    const dy = smoothMouseY - blob.y;
    const dist = Math.sqrt(dx * dx + dy * dy);

    // Vùng ảnh hưởng của chuột: càng lớn càng dễ thấy
    const influence = Math.max(0, 1 - dist / 850);

    // Lực hút về chuột: càng lớn hiệu ứng càng mạnh
    const finalX = blob.x + dx * influence * 0.58;
    const finalY = blob.y + dy * influence * 0.58;

    // Khi chuột lại gần, mảng liquid phồng to hơn
    const finalR = radius + influence * 190;

    const g = ctx.createRadialGradient(finalX, finalY, 0, finalX, finalY, finalR);

    g.addColorStop(0, blob.color);
    g.addColorStop(0.35, 'rgba(21, 66, 23, 0.27)');
    g.addColorStop(0.65, 'rgba(9, 32, 30, 0.88)');
    g.addColorStop(1, 'rgba(0, 0, 0, 0)');

    ctx.fillStyle = g;
    ctx.beginPath();
    ctx.arc(finalX, finalY, finalR, 0, Math.PI * 2);
    ctx.fill();
}

    function drawWisps() {
        ctx.save();
        ctx.globalCompositeOperation = 'lighter';

        for (let i = 0; i < 8; i++) {
            const yBase = h * (0.18 + i * 0.1);
            const phase = i * 0.7;

            ctx.beginPath();

            for (let x = -60; x <= w + 60; x += 12) {
                const p = x / w;

                const y =
                    yBase +
                    Math.sin(p * 8 + time * 0.0008 + phase) * 28 +
                    Math.cos(p * 13 - time * 0.0005 + phase) * 18 +
                    Math.sin((smoothMouseX / w) * Math.PI + phase) * 6;

                if (x === -60) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }

            ctx.strokeStyle = i % 2 === 0
                ? 'rgba(0, 255, 130, 0.045)'
                : 'rgba(30, 180, 90, 0.035)';

            ctx.lineWidth = 16;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
        }

        ctx.restore();
    }

    function animate(t) {
        time = t;

        smoothMouseX += (mouseX - smoothMouseX) * 0.16;
        smoothMouseY += (mouseY - smoothMouseY) * 0.16;

        ctx.clearRect(0, 0, w, h);

        ctx.fillStyle = '#120f17';
        ctx.fillRect(0, 0, w, h);

        ctx.save();
        ctx.filter = 'blur(38px) saturate(145%)';
        ctx.globalCompositeOperation = 'screen';

        for (let i = 0; i < blobs.length; i++) {
            drawBlob(blobs[i], i);
        }

        ctx.restore();

        ctx.save();
        ctx.filter = 'blur(18px)';
        drawWisps();
        ctx.restore();

        requestAnimationFrame(animate);
    }

    requestAnimationFrame(animate);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
