<?php
$page_title = 'MusicAI';
$page_desc = 'Khám phá hàng chục bài hát đỉnh cao, tìm kiếm và lọc nhạc theo thể loại để thưởng thức tức thì.';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Nhận tham số tìm kiếm và bộ lọc thể loại từ URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre_filter = isset($_GET['genre']) ? trim($_GET['genre']) : '';

$songs = [];

// Thực thi truy vấn nếu kết nối DB thành công
if ($db_connected && $conn) {
    try {
        $query = "SELECT * FROM songs WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (title LIKE ? OR artist LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($genre_filter)) {
            $query .= " AND genre = ?";
            $params[] = $genre_filter;
        }
        
        $query .= " ORDER BY id DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $songs = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Dự phòng bằng mảng dữ liệu mẫu PHP khi xảy ra lỗi query
        $songs = filter_fallback_songs($fallback_songs, $search, $genre_filter);
    }
} else {
    // Dự phòng khi chưa tạo DB
    $songs = filter_fallback_songs($fallback_songs, $search, $genre_filter);
}

// Bộ lọc cho dữ liệu dự phòng PHP
function filter_fallback_songs($fallback_songs, $search, $genre_filter) {
    $filtered = [];
    foreach ($fallback_songs as $song) {
        $match = true;
        if (!empty($search)) {
            if (stripos($song['title'], $search) === false && stripos($song['artist'], $search) === false) {
                $match = false;
            }
        }
        if (!empty($genre_filter)) {
            if (strcasecmp($song['genre'], $genre_filter) !== 0) {
                $match = false;
            }
        }
        if ($match) {
            $filtered[] = $song;
        }
    }
    return $filtered;
}

// Lấy danh sách thể loại để lọc
$genres = $fallback_genres;
if ($db_connected && $conn) {
    try {
        $stmt = $conn->query("SELECT DISTINCT genre FROM songs");
        $db_genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($db_genres)) {
            $genres = $db_genres;
        }
    } catch (PDOException $e) {}
}
?>

<div class="container music-page-container">
    <div class="music-header-controls">
        <h2 class="section-title"><i class="fa-solid fa-music"></i>Music The World</h2>
        
        <!-- Form lọc và Tìm kiếm -->
        <form method="GET" action="" class="filter-form">
            <div class="search-input-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="search" class="form-control search-control" placeholder="Tìm tên bài hát, nghệ sĩ..." value="<?php echo sanitize($search); ?>">
            </div>
            
            <div class="genre-select-wrapper">
                <select name="genre" class="form-control filter-control" onchange="this.form.submit()">
                    <option value="">Tất cả thể loại</option>
                    <?php foreach ($genres as $gen): ?>
                        <option value="<?php echo sanitize($gen); ?>" <?php echo $genre_filter === $gen ? 'selected' : ''; ?>>
                            <?php echo sanitize($gen); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
            <?php if (!empty($search) || !empty($genre_filter)): ?>
                <a href="<?php echo url('music.php'); ?>" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Danh sách bài hát dạng card -->
    <?php if (empty($songs)): ?>
        <div class="glass-card no-results">
            <i class="fa-solid fa-circle-question"></i>
            <h3>Không tìm thấy bài hát nào</h3>
            <p>Thử tìm kiếm với từ khóa khác hoặc chuyển sang thể loại khác.</p>
        </div>
    <?php else: ?>
        <div class="grid-4">
            <?php 
                // Thiết lập playlist JSON để cho phép phát liên tiếp các bài trong danh sách
                $playlist_data = [];
                foreach ($songs as $s) {
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
            
            <?php foreach ($songs as $song): ?>
                <?php 
                    $cover_src = 'assets/images/1.jpg';
                    if (!empty($song['thumbnail'])) {
                        $cover_src = (strpos($song['thumbnail'], 'assets/') === 0 || strpos($song['thumbnail'], 'storage/') === 0) 
                            ? $song['thumbnail'] 
                            : 'storage/thumbnails/' . $song['thumbnail'];
                    }
                    
                    // Nạp trạng thái bài hát
                    $song_data = [
                        'id' => $song['id'],
                        'title' => $song['title'],
                        'artist' => $song['artist'],
                        'audio_file' => $song['audio_file'],
                        'thumbnail' => $cover_src,
                        'is_favorite' => false
                    ];
                    
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
                ?>
                <div class="glass-card song-card-item">
                    <div class="song-cover-box">
                        <img src="<?php echo url($cover_src); ?>" alt="<?php echo sanitize($song['title']); ?>" class="song-item-cover">
                        <button class="play-overlay-btn" onclick='playTrackOnPlayer(<?php echo htmlspecialchars($song_json, ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($playlist_json, ENT_QUOTES, 'UTF-8'); ?>)' title="Phát bài hát">
                            <i class="fa-solid fa-play"></i>
                        </button>
                    </div>
                    
                    <div class="song-item-info">
                        <h4 class="song-item-title" title="<?php echo sanitize($song['title']); ?>"><?php echo sanitize($song['title']); ?></h4>
                        <p class="song-item-artist"><?php echo sanitize($song['artist']); ?></p>
                        
                        <div class="song-item-meta">
                            <span class="genre-tag"><?php echo sanitize($song['genre']); ?></span>
                            <span class="plays-count"><i class="fa-solid fa-headphones"></i> <?php echo number_format($song['plays']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.music-header-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 40px;
}

.music-header-controls .section-title {
    margin-bottom: 0;
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
    flex-grow: 1;
    justify-content: flex-end;
}

.search-input-wrapper {
    position: relative;
    width: 300px;
    max-width: 100%;
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.search-control {
    padding-left: 40px;
}

.genre-select-wrapper {
    width: 200px;
}

.song-card-item {
    padding: 16px;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
}

.song-cover-box {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.song-item-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.play-overlay-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: var(--green-main);
    color: var(--bg-main);
    border: none;
    cursor: pointer;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 0 15px var(--green-main);
}

.song-cover-box:hover .song-item-cover {
    transform: scale(1.08);
}

.song-cover-box:hover .play-overlay-btn {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.song-item-info {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.song-item-title {
    font-size: 1.05rem;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.song-item-artist {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.song-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    margin-top: auto;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.no-results i {
    font-size: 3rem;
    color: var(--green-dark);
    margin-bottom: 15px;
}

.no-results h3 {
    color: var(--text-main);
    margin-bottom: 8px;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
