// Trình phát nhạc cố định của MusicAI

class AudioPlayer {
    constructor() {
        this.audio = document.getElementById('mainAudioElement');
        this.player = document.getElementById('fixedPlayer');
        this.playBtn = document.getElementById('playerPlayBtn');
        this.prevBtn = document.getElementById('playerPrevBtn');
        this.nextBtn = document.getElementById('playerNextBtn');
        this.favBtn = document.getElementById('playerFavBtn');
        this.progressContainer = document.getElementById('playerProgressContainer');
        this.progressFill = document.getElementById('playerProgressFill');
        this.timeCurrent = document.getElementById('playerTimeCurrent');
        this.timeTotal = document.getElementById('playerTimeTotal');
        this.volumeBtn = document.getElementById('playerVolumeBtn');
        this.volumeContainer = document.getElementById('playerVolumeContainer');
        this.volumeFill = document.getElementById('playerVolumeFill');
        this.trackCover = document.getElementById('playerTrackCover');
        this.trackTitle = document.getElementById('playerTrackTitle');
        this.trackArtist = document.getElementById('playerTrackArtist');
        this.waveVisualizer = document.getElementById('playerWaveVisualizer');

        this.currentPlaylist = [];
        this.currentTrackIndex = -1;
        this.isMuted = false;
        this.lastVolume = 0.8;

        if (this.audio) {
            this.initEvents();
            // Thiết lập âm lượng mặc định
            this.audio.volume = this.lastVolume;
        }
    }

    initEvents() {
        // Play/Pause click
        this.playBtn.addEventListener('click', () => this.togglePlay());

        // Next/Prev clicks
        this.prevBtn.addEventListener('click', () => this.prevTrack());
        this.nextBtn.addEventListener('click', () => this.nextTrack());

        // Favorite click
        if (this.favBtn) {
            this.favBtn.addEventListener('click', () => this.toggleFavorite());
        }

        // Audio Element events
        this.audio.addEventListener('timeupdate', () => this.updateProgress());
        this.audio.addEventListener('loadedmetadata', () => this.onMetadataLoaded());
        this.audio.addEventListener('ended', () => this.nextTrack());

        // Progress seeker click
        this.progressContainer.addEventListener('click', (e) => this.seek(e));

        // Volume controls
        this.volumeBtn.addEventListener('click', () => this.toggleMute());
        this.volumeContainer.addEventListener('click', (e) => this.setVolume(e));

        // Lắng nghe sự kiện phát bài hát toàn cục
        document.addEventListener('play-track', (e) => {
            const { song, playlist } = e.detail;
            this.playSong(song, playlist);
        });
    }

    playSong(song, playlist = []) {
        if (playlist && playlist.length > 0) {
            this.currentPlaylist = playlist;
            this.currentTrackIndex = this.currentPlaylist.findIndex(s => s.id == song.id);
        } else if (this.currentPlaylist.length === 0 || !this.currentPlaylist.find(s => s.id == song.id)) {
            this.currentPlaylist = [song];
            this.currentTrackIndex = 0;
        } else {
            this.currentTrackIndex = this.currentPlaylist.findIndex(s => s.id == song.id);
        }

        this.audio.src = song.audio_file.startsWith('http') || song.audio_file.startsWith('assets') || song.audio_file.startsWith('storage')
            ? BASE_URL + song.audio_file.replace(/^\/+/, '')
            : BASE_URL + 'storage/songs/' + song.audio_file;

        this.audio.play().then(() => {
            this.updatePlayerUI(song);
            this.logPlay(song.id);
        }).catch(err => {
            console.error('Play failed:', err);
            showToast('Không thể phát tập tin âm thanh này!', 'error');
        });
    }

    togglePlay() {
        if (!this.audio.src) return;

        if (this.audio.paused) {
            this.audio.play().then(() => {
                this.playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                this.waveVisualizer.classList.add('playing');
            }).catch(err => console.error(err));
        } else {
            this.audio.pause();
            this.playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            this.waveVisualizer.classList.remove('playing');
        }
    }

    prevTrack() {
        if (this.currentPlaylist.length === 0 || this.currentTrackIndex === -1) return;
        this.currentTrackIndex = (this.currentTrackIndex - 1 + this.currentPlaylist.length) % this.currentPlaylist.length;
        this.playSong(this.currentPlaylist[this.currentTrackIndex]);
    }

    nextTrack() {
        if (this.currentPlaylist.length === 0 || this.currentTrackIndex === -1) return;
        this.currentTrackIndex = (this.currentTrackIndex + 1) % this.currentPlaylist.length;
        this.playSong(this.currentPlaylist[this.currentTrackIndex]);
    }

    updatePlayerUI(song) {
        this.player.style.display = 'block';

        // Cover thumbnail
        let coverSrc = BASE_URL + 'assets/images/default_song.jpg';
        if (song.thumbnail) {
            coverSrc = song.thumbnail.startsWith('http') || song.thumbnail.startsWith('assets') || song.thumbnail.startsWith('storage')
                ? BASE_URL + song.thumbnail.replace(/^\/+/, '')
                : BASE_URL + 'storage/thumbnails/' + song.thumbnail;
        }
        this.trackCover.src = coverSrc;
        this.trackTitle.textContent = song.title;
        this.trackArtist.textContent = song.artist;
        this.playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
        this.waveVisualizer.classList.add('playing');

        // Favorite state button
        if (this.favBtn) {
            this.favBtn.setAttribute('data-song-id', song.id);
            if (song.is_favorite) {
                this.favBtn.classList.add('active');
                this.favBtn.querySelector('i').className = 'fa-solid fa-heart';
            } else {
                this.favBtn.classList.remove('active');
                this.favBtn.querySelector('i').className = 'fa-regular fa-heart';
            }
        }
    }

    onMetadataLoaded() {
        this.timeTotal.textContent = this.formatTime(this.audio.duration);
    }

    updateProgress() {
        if (isNaN(this.audio.duration)) return;
        const percent = (this.audio.currentTime / this.audio.duration) * 100;
        this.progressFill.style.width = percent + '%';
        this.timeCurrent.textContent = this.formatTime(this.audio.currentTime);
    }

    seek(e) {
        if (isNaN(this.audio.duration)) return;
        const rect = this.progressContainer.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const width = rect.width;
        const newTime = (clickX / width) * this.audio.duration;
        this.audio.currentTime = newTime;
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        this.audio.muted = this.isMuted;
        if (this.isMuted) {
            this.volumeBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
            this.volumeFill.style.width = '0%';
        } else {
            this.volumeBtn.innerHTML = this.lastVolume > 0.5 ? '<i class="fa-solid fa-volume-high"></i>' : '<i class="fa-solid fa-volume-low"></i>';
            this.volumeFill.style.width = (this.lastVolume * 100) + '%';
        }
    }

    setVolume(e) {
        const rect = this.volumeContainer.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const width = rect.width;
        let volume = clickX / width;
        volume = Math.max(0, Math.min(1, volume)); // Clamp 0 to 1

        this.audio.volume = volume;
        this.lastVolume = volume;
        this.isMuted = volume === 0;
        this.audio.muted = this.isMuted;

        this.volumeFill.style.width = (volume * 100) + '%';
        if (this.isMuted) {
            this.volumeBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
        } else if (volume > 0.5) {
            this.volumeBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
        } else {
            this.volumeBtn.innerHTML = '<i class="fa-solid fa-volume-low"></i>';
        }
    }

    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    async logPlay(songId) {
        const res = await callAPI(BASE_URL + 'api/music/history.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `song_id=${songId}`
        });

        if (!res.success) {
            console.warn('Logging play error:', res.message);
        }
    }

    async toggleFavorite() {
        const songId = this.favBtn.getAttribute('data-song-id');
        if (!songId) return;

        if (!IS_LOGGED_IN) {
            showToast('Vui lòng đăng nhập để thêm vào danh sách yêu thích!', 'error');
            return;
        }

        const res = await callAPI(BASE_URL + 'api/music/favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `song_id=${songId}`
        });

        if (res.success) {
            const isFav = res.data.is_favorite;
            if (isFav) {
                this.favBtn.classList.add('active');
                this.favBtn.querySelector('i').className = 'fa-solid fa-heart';
            } else {
                this.favBtn.classList.remove('active');
                this.favBtn.querySelector('i').className = 'fa-regular fa-heart';
            }
            showToast(res.message);

            // Sync UI với nút yêu thích trên trang (nếu có)
            const pageFavBtn = document.querySelector(`.song-fav-btn[data-song-id="${songId}"]`);
            if (pageFavBtn) {
                if (isFav) {
                    pageFavBtn.classList.add('active');
                    pageFavBtn.querySelector('i').className = 'fa-solid fa-heart';
                } else {
                    pageFavBtn.classList.remove('active');
                    pageFavBtn.querySelector('i').className = 'fa-regular fa-heart';
                }
            }
        } else {
            showToast(res.message, 'error');
        }
    }
}

// Khởi tạo trình phát nhạc toàn cục
let globalAudioPlayer = null;
document.addEventListener('DOMContentLoaded', () => {
    globalAudioPlayer = new AudioPlayer();
});

/**
 * Hàm gọi nhanh để phát một bài hát từ giao diện
 * @param {string} songJson - JSON string của bài hát
 * @param {string} playlistJson - JSON string của danh sách bài hát
 */
function playTrackOnPlayer(songJson, playlistJson = '[]') {
    try {
        const song = typeof songJson === 'string' ? JSON.parse(songJson) : songJson;
        const playlist = typeof playlistJson === 'string' ? JSON.parse(playlistJson) : playlistJson;

        const event = new CustomEvent('play-track', {
            detail: { song, playlist }
        });
        document.dispatchEvent(event);
    } catch (e) {
        console.error('Error parsing song details for player:', e);
    }
}
