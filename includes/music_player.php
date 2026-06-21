<?php
// Music player cố định ở cuối màn hình
?>
<div class="fixed-player" id="fixedPlayer" style="display: none;">
    <div class="player-container">
        <!-- Thông tin bài hát đang phát -->
        <div class="player-track-info">
            <img src="<?php echo url('assets/images/default_song.jpg'); ?>" alt="Track Cover" class="player-track-cover" id="playerTrackCover">
            <div class="player-track-meta">
                <div class="player-track-title" id="playerTrackTitle">Chưa chọn bài hát</div>
                <div class="player-track-artist" id="playerTrackArtist">Nghệ sĩ</div>
            </div>
            <?php if (isLoggedIn()): ?>
                <button class="player-fav-btn" id="playerFavBtn" data-song-id="" title="Thêm vào yêu thích">
                    <i class="fa-regular fa-heart"></i>
                </button>
            <?php endif; ?>
        </div>

        <!-- Trình điều khiển chính -->
        <div class="player-controls-wrapper">
            <div class="player-controls">
                <button class="control-btn" id="playerPrevBtn" title="Bài trước"><i class="fa-solid fa-backward-step"></i></button>
                <button class="control-btn play-btn" id="playerPlayBtn" title="Phát"><i class="fa-solid fa-play"></i></button>
                <button class="control-btn" id="playerNextBtn" title="Bài tiếp theo"><i class="fa-solid fa-forward-step"></i></button>
            </div>
            
            <div class="player-progress-area">
                <span class="time-current" id="playerTimeCurrent">00:00</span>
                <div class="progress-bar-container" id="playerProgressContainer">
                    <div class="progress-bar-fill" id="playerProgressFill"></div>
                </div>
                <span class="time-total" id="playerTimeTotal">00:00</span>
            </div>
        </div>

        <!-- Điều chỉnh âm lượng và hiệu ứng -->
        <div class="player-right-controls">
            <!-- Hiệu ứng sóng nhạc nhỏ khi đang phát -->
            <div class="player-wave-visualizer" id="playerWaveVisualizer">
                <span class="wave-bar"></span>
                <span class="wave-bar"></span>
                <span class="wave-bar"></span>
                <span class="wave-bar"></span>
                <span class="wave-bar"></span>
            </div>
            
            <div class="player-volume-wrapper">
                <button class="volume-btn" id="playerVolumeBtn" title="Âm lượng"><i class="fa-solid fa-volume-high"></i></button>
                <div class="volume-slider-container" id="playerVolumeContainer">
                    <div class="volume-slider-fill" id="playerVolumeFill" style="width: 80%;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Audio Element ẩn -->
    <audio id="mainAudioElement" style="display: none;"></audio>
</div>
