// Quản lý điều khiển Beatmaker (Trình trộn nhạc / Incredibox style)

document.addEventListener('DOMContentLoaded', () => {
    // Kiểm tra xem có đang ở trang beatmaker không
    const beatmakerContainer = document.getElementById('beatmakerContainer');
    if (!beatmakerContainer) return;

    const playAllBtn = document.getElementById('bmPlayAll');
    const stopAllBtn = document.getElementById('bmStopAll');
    const resetBtn = document.getElementById('bmReset');
    const saveMixBtn = document.getElementById('bmSaveMix');

    // Lưu các đối tượng âm thanh đang hoạt động: slotId -> { audio, soundData }
    const activeSlots = {};
    let isPlaying = false;

    // Bộ lọc tab danh mục sound
    const tabs = document.querySelectorAll('.palette-tab');
    const soundItems = document.querySelectorAll('.sound-item');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const category = tab.getAttribute('data-tab');
            soundItems.forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Nghe thử nhanh các sound item mẫu bằng nút play nhỏ
    document.querySelectorAll('.sound-item-play-preview').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const parent = btn.closest('.sound-item');
            const file = parent.getAttribute('data-audio-file');

            let tempAudio = btn.tempAudio;
            if (!tempAudio) {
                tempAudio = new Audio(BASE_URL + file.replace(/^\/+/, ''));
                btn.tempAudio = tempAudio;

                tempAudio.addEventListener('ended', () => {
                    btn.innerHTML = '<i class="fa-solid fa-play"></i>';
                });
            }

            if (tempAudio.paused) {
                // Tắt tất cả các preview đang phát khác
                document.querySelectorAll('.sound-item-play-preview').forEach(otherBtn => {
                    if (otherBtn !== btn && otherBtn.tempAudio) {
                        otherBtn.tempAudio.pause();
                        otherBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
                    }
                });

                tempAudio.play().then(() => {
                    btn.innerHTML = '<i class="fa-solid fa-square"></i>';
                }).catch(err => console.error('Play preview error:', err));
            } else {
                tempAudio.pause();
                tempAudio.currentTime = 0;
                btn.innerHTML = '<i class="fa-solid fa-play"></i>';
            }
        });
    });

    // Đăng ký nhận sự kiện thả Sound từ dragdrop.js
    document.addEventListener('sound-dropped', (e) => {
        const { slotId, sound } = e.detail;
        loadSoundToSlot(slotId, sound);
    });

    /**
     * Nạp bài nhạc loop vào slot nhân vật
     */
    function loadSoundToSlot(slotId, sound) {
        // Tạm dừng player nhạc chính của web để tránh trùng âm thanh
        const mainAudio = document.getElementById('mainAudioElement');
        if (mainAudio && !mainAudio.paused) {
            mainAudio.pause();
            const mainPlayBtn = document.getElementById('playerPlayBtn');
            if (mainPlayBtn) mainPlayBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            const mainWave = document.getElementById('playerWaveVisualizer');
            if (mainWave) mainWave.classList.remove('playing');
        }

        // Dọn dẹp slot nếu đang có sound khác chạy
        removeSoundFromSlot(slotId);

        const slotEl = document.querySelector(`.beatmaker-slot[data-slot-id="${slotId}"]`);
        if (!slotEl) return;

        // Tạo đối tượng Audio phát Loop
        const audio = new Audio(BASE_URL + sound.audio_file.replace(/^\/+/, ''));
        audio.loop = true;

        activeSlots[slotId] = {
            audio: audio,
            soundData: sound
        };

        // Cập nhật giao diện Slot
        slotEl.classList.add('active');
        const avatar = slotEl.querySelector('.slot-avatar');
        avatar.innerHTML = getIconForCategory(sound.category);
        slotEl.querySelector('.slot-sound-name').textContent = sound.name;

        // Nếu đang trong trạng thái phát nhạc thì chạy ngay loop vừa thả vào
        if (isPlaying) {
            audio.play().catch(e => console.error('Play loop error:', e));
        } else {
            // Nếu là sound đầu tiên thì tự động phát tất cả luôn
            isPlaying = true;
            playAllSlots();
        }

        updateVisualizerState();
        showToast(`Đã thêm sound "${sound.name}" vào Slot ${slotId}`);
    }

    /**
     * Xóa sound khỏi slot nhân vật
     */
    function removeSoundFromSlot(slotId) {
        if (activeSlots[slotId]) {
            activeSlots[slotId].audio.pause();
            activeSlots[slotId].audio.src = '';
            delete activeSlots[slotId];
        }

        const slotEl = document.querySelector(`.beatmaker-slot[data-slot-id="${slotId}"]`);
        if (slotEl) {
            slotEl.classList.remove('active');
            slotEl.querySelector('.slot-avatar').innerHTML = '<i class="fa-solid fa-plus"></i>';
            slotEl.querySelector('.slot-sound-name').textContent = 'Kéo sound vào đây';
        }

        if (Object.keys(activeSlots).length === 0) {
            isPlaying = false;
        }
        updateVisualizerState();
    }

    // Gắn sự kiện xóa cho các nút X tại từng slot
    document.querySelectorAll('.remove-sound-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const slotId = btn.closest('.beatmaker-slot').getAttribute('data-slot-id');
            removeSoundFromSlot(slotId);
        });
    });

    /**
     * Phát tất cả các slot đang active
     */
    function playAllSlots() {
        const activeKeys = Object.keys(activeSlots);
        if (activeKeys.length === 0) {
            showToast('Vui lòng kéo thả âm thanh vào slot trước khi phát!', 'error');
            return;
        }

        isPlaying = true;
        activeKeys.forEach(slotId => {
            activeSlots[slotId].audio.play().catch(e => console.error(e));
        });

        playAllBtn.className = 'btn btn-primary';
        stopAllBtn.className = 'btn btn-secondary';
        updateVisualizerState();
    }

    /**
     * Tạm dừng toàn bộ các loop
     */
    function stopAllSlots() {
        isPlaying = false;
        Object.keys(activeSlots).forEach(slotId => {
            activeSlots[slotId].audio.pause();
        });

        playAllBtn.className = 'btn btn-secondary';
        stopAllBtn.className = 'btn btn-primary';
        updateVisualizerState();
    }

    /**
     * Khởi động lại toàn bộ bàn trộn
     */
    function resetBeatmaker() {
        stopAllSlots();
        Object.keys(activeSlots).forEach(slotId => {
            removeSoundFromSlot(slotId);
        });
        isPlaying = false;
        showToast('Đã xóa tất cả sound khỏi các slot.');
    }

    playAllBtn.addEventListener('click', playAllSlots);
    stopAllBtn.addEventListener('click', stopAllSlots);
    resetBtn.addEventListener('click', resetBeatmaker);

    // Tự động tải bản mix được chọn nếu có sẵn dữ liệu nạp từ server
    if (window.PRELOADED_MIX && window.PRELOADED_MIX.length > 0) {
        window.PRELOADED_MIX.forEach((sound, index) => {
            const slotId = (index + 1).toString();
            if (index < 5) {
                loadSoundToSlot(slotId, sound);
            }
        });
        showToast('Đã tự động tải và kích hoạt bản Beat Mix của bạn!', 'success');
    }

    // Lưu bản mix cá nhân
    if (saveMixBtn) {
        saveMixBtn.addEventListener('click', async () => {
            if (!IS_LOGGED_IN) {
                showToast('Vui lòng đăng nhập để lưu bản beat mix cá nhân!', 'error');
                return;
            }

            const activeKeys = Object.keys(activeSlots);
            if (activeKeys.length === 0) {
                showToast('Chưa có âm thanh nào được kích hoạt để lưu bản mix!', 'error');
                return;
            }

            const name = prompt('Nhập tên cho bản Beat Mix này:', 'My New Beat Mix');
            if (name === null) return;
            if (!name.trim()) {
                showToast('Tên bản Beat Mix không được để trống!', 'error');
                return;
            }

            const soundIds = activeKeys.map(key => activeSlots[key].soundData.id);

            saveMixBtn.disabled = true;
            saveMixBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

            try {
                const res = await callAPI(BASE_URL + 'api/beatmaker/save_mix.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `name=${encodeURIComponent(name)}&mix_data=${encodeURIComponent(JSON.stringify(soundIds))}`
                });

                if (res.success) {
                    showToast('Đã lưu bản Beat Mix thành công vào thư viện!', 'success');
                } else {
                    showToast(res.message, 'error');
                }
            } catch (err) {
                console.error('Save mix error:', err);
                showToast('Lỗi khi kết nối API lưu bản mix!', 'error');
            } finally {
                saveMixBtn.disabled = false;
                saveMixBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Lưu bản Mix';
            }
        });
    }

    function getIconForCategory(cat) {
        switch (cat) {
            case 'drums': return '<i class="fa-solid fa-drum"></i>';
            case 'bass': return '<i class="fa-solid fa-guitar"></i>';
            case 'melody': return '<i class="fa-solid fa-music"></i>';
            case 'vocals': return '<i class="fa-solid fa-microphone-lines"></i>';
            case 'effects': return '<i class="fa-solid fa-wand-magic-sparkles"></i>';
            default: return '<i class="fa-solid fa-plus"></i>';
        }
    }

    function updateVisualizerState() {
        const visualizerEvent = new CustomEvent('visualizer-state-change', {
            detail: {
                active: isPlaying && Object.keys(activeSlots).length > 0
            }
        });
        document.dispatchEvent(visualizerEvent);
    }
});
