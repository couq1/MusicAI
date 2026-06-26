<?php
$page_title = 'Bài hát yêu thích - MusicAI';
$page_desc = 'Xem danh sách bài hát bạn đã yêu thích và thưởng thức lại chúng bất kỳ lúc nào.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Chỉ người dùng đã đăng nhập mới vào được trang này
requireLogin();

$user_id = $_SESSION['user']['id'];
$fav_songs = [];

if ($db_connected && $conn) {
    try {
        // Lấy danh sách bài hát yêu thích thông qua bảng trung gian favorites
        $stmt = $conn->prepare("SELECT s.* FROM songs s JOIN favorites f ON s.id = f.song_id WHERE f.user_id = ? ORDER BY f.id DESC");
        $stmt->execute([$user_id]);
        $fav_songs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $fav_songs = $fallback_songs;
    }
} else {
    // Dự phòng
    $fav_songs = $fallback_songs;
}
?>

<div class="container favorites-page-container">
    <h2 class="section-title"><i class="fa-solid fa-heart"></i> MusicAI Favorites</h2>
    
    <?php if (empty($fav_songs)): ?>
        <div class="glass-card empty-favorites">
            <i class="fa-solid fa-heart-crack"></i>
            <h3>Danh sách yêu thích trống</h3>
            <p>Hãy khám phá kho nhạc và nhấn nút tim để lưu lại những tác phẩm bạn yêu thích.</p>
            <a href="<?php echo url('music.php'); ?>" class="btn btn-primary" style="margin-top: 15px;">Nghe nhạc ngay</a>
        </div>
    <?php else: ?>
        <div class="favorites-list">
            <?php 
                // Tạo danh sách playlist để phát liên tiếp
                $playlist_data = [];
                foreach ($fav_songs as $s) {
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
            
            <?php foreach ($fav_songs as $song): ?>
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
                        'is_favorite' => true
                    ];
                    $song_json = json_encode($song_data, JSON_UNESCAPED_UNICODE);
                ?>
               <div class="glass-card favorite-row">

    <img 
        src="<?php echo url($cover_src); ?>" 
        alt="<?php echo sanitize($song['title']); ?>" 
        class="favorite-cover"
    >

    <div class="favorite-info">
        <h4><?php echo sanitize($song['title']); ?></h4>
        <p><?php echo sanitize($song['artist']); ?></p>
        <span><?php echo sanitize($song['genre']); ?></span>
    </div>

    <div class="favorite-actions">
        <button 
            class="favorite-play-btn"
            onclick='playTrackOnPlayer(<?php echo htmlspecialchars($song_json, ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($playlist_json, ENT_QUOTES, 'UTF-8'); ?>)'
            title="Nghe lại"
        >
            <i class="fa-solid fa-play"></i>
            Nghe lại
        </button>

        <button 
            class="song-fav-btn active favorite-remove-btn" 
            data-song-id="<?php echo $song['id']; ?>" 
            onclick="toggleFavFromPage(<?php echo $song['id']; ?>, this)" 
            title="Bỏ yêu thích"
        >
            <i class="fa-solid fa-heart"></i>
        </button>
    </div>

</div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.empty-favorites {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.empty-favorites i {
    font-size: 3.5rem;
    color: var(--green-dark);
    margin-bottom: 20px;
}

.empty-favorites h3 {
    color: var(--text-main);
    margin-bottom: 8px;
}
</style>

<script>
/**
 * Click gỡ tim nhanh ngay trên trang favorites.php
 */
async function toggleFavFromPage(songId, btn) {
    const res = await callAPI(BASE_URL + 'api/music/favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `song_id=${songId}`
    });
    
    if (res.success) {
        showToast(res.message, 'success');
        
        // Remove card element out of DOM if unfavorited
        if (!res.data.is_favorite) {
            const card = btn.closest('.favorite-row');
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => {
                card.remove();
                // Nếu hết bài hát thì reload trang để hiện placeholder trống
                const grid = document.querySelector('.favorites-list');
                if (grid && grid.children.length === 0) {
                    window.location.reload();
                }
            }, 300);
        }
        
        // Đồng bộ với nút tim ở player cố định dưới chân trang
        const playerFavBtn = document.getElementById('playerFavBtn');
        if (playerFavBtn && playerFavBtn.getAttribute('data-song-id') == songId) {
            playerFavBtn.classList.remove('active');
            playerFavBtn.querySelector('i').className = 'fa-regular fa-heart';
        }
    } else {
        showToast(res.message, 'error');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
