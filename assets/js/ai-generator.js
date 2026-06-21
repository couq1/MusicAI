// Xử lý trang Tạo nhạc bằng Trí tuệ nhân tạo (AI Generator)

document.addEventListener('DOMContentLoaded', () => {
    const generatorForm = document.getElementById('generatorForm');
    const generatorLoading = document.getElementById('generatorLoading');
    const resultPlaceholder = document.getElementById('resultPlaceholder');
    const resultCard = document.getElementById('resultCard');
    
    // Các phần tử hiển thị kết quả
    const resultPrompt = document.getElementById('resultPrompt');
    const resultMeta = document.getElementById('resultMeta');
    const resultAudioElement = document.getElementById('resultAudioElement');
    const resultPlayBtn = document.getElementById('resultPlayBtn');
    const resultProgress = document.getElementById('resultProgress');
    const resultProgressFill = document.getElementById('resultProgressFill');
    const resultTimeCurrent = document.getElementById('resultTimeCurrent');
    const resultTimeTotal = document.getElementById('resultTimeTotal');
    const saveAiSongBtn = document.getElementById('saveAiSongBtn');
    
    let generatedSongData = null; // Chứa thông tin bài nhạc đã tạo thành công
    
    if (generatorForm) {
        generatorForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!IS_LOGGED_IN) {
                showToast('Vui lòng đăng nhập để sử dụng chức năng tạo nhạc AI!', 'error');
                return;
            }
            
            const prompt = document.getElementById('prompt').value.trim();
            const genre = document.getElementById('genre').value;
            const mood = document.getElementById('mood').value;
            const duration = document.getElementById('duration').value;
            
            if (!prompt) {
                showToast('Vui lòng nhập mô tả để tạo nhạc!', 'error');
                return;
            }
            
            // Hiển thị trạng thái Loading, ẩn kết quả cũ
            generatorLoading.style.display = 'flex';
            if (resultPlaceholder) resultPlaceholder.style.display = 'none';
            if (resultCard) resultCard.style.display = 'none';
            
            try {
                // Gọi API gửi yêu cầu tạo nhạc tới backend PHP
                const response = await fetch(BASE_URL + 'api/ai/generate_music.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `prompt=${encodeURIComponent(prompt)}&genre=${genre}&mood=${mood}&duration=${duration}`
                });
                
                const res = await response.json();
                
                if (res.success) {
                    // Tiến hành Polling kiểm tra trạng thái tiến trình tạo nhạc
                    pollStatus(res.data.task_id || null, res.data);
                } else {
                    generatorLoading.style.display = 'none';
                    if (resultPlaceholder) resultPlaceholder.style.display = 'flex';
                    showToast(res.message, 'error');
                }
            } catch (err) {
                console.error('Submit generate form error:', err);
                generatorLoading.style.display = 'none';
                if (resultPlaceholder) resultPlaceholder.style.display = 'flex';
                showToast('Không thể kết nối đến API Tạo nhạc AI!', 'error');
            }
        });
    }
    
    /**
     * Vòng lặp kiểm tra trạng thái tạo nhạc (Polling)
     */
    function pollStatus(taskId, fallbackData) {
        if (!taskId) {
            // Nếu API không phản hồi taskId mà trả ngay dữ liệu mẫu thì hiển thị trực tiếp
            setTimeout(() => {
                displayResult(fallbackData);
            }, 2500); // Tạo độ trễ ảo giả lập tiến trình AI đang tạo
            return;
        }
        
        let attempts = 0;
        const maxAttempts = 60; // Chờ tối đa 60 giây
        
        const intervalId = setInterval(async () => {
            attempts++;
            
            try {
                const res = await callAPI(BASE_URL + `api/ai/check_status.php?task_id=${taskId}`);
                
                if (res.success && res.data.status === 'completed') {
                    clearInterval(intervalId);
                    displayResult(res.data);
                } else if (res.success && res.data.status === 'failed') {
                    clearInterval(intervalId);
                    generatorLoading.style.display = 'none';
                    if (resultPlaceholder) resultPlaceholder.style.display = 'flex';
                    showToast('Tạo nhạc thất bại từ máy chủ AI!', 'error');
                } else if (attempts >= maxAttempts) {
                    clearInterval(intervalId);
                    generatorLoading.style.display = 'none';
                    if (resultPlaceholder) resultPlaceholder.style.display = 'flex';
                    showToast('Quá thời gian phản hồi từ máy chủ AI!', 'error');
                }
            } catch (e) {
                clearInterval(intervalId);
                generatorLoading.style.display = 'none';
                if (resultPlaceholder) resultPlaceholder.style.display = 'flex';
                showToast('Lỗi trong quá trình kiểm tra trạng thái tạo nhạc!', 'error');
            }
        }, 1000);
    }
    
    /**
     * Hiển thị kết quả nhạc AI đã tạo
     */
    function displayResult(data) {
        generatedSongData = data;
        generatorLoading.style.display = 'none';
        if (resultPlaceholder) resultPlaceholder.style.display = 'none';
        if (resultCard) resultCard.style.display = 'block';
        
        // Reset nút lưu
        saveAiSongBtn.disabled = false;
        saveAiSongBtn.className = 'btn btn-primary';
        saveAiSongBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Lưu vào thư viện';
        
        // Cập nhật text thông tin
        resultPrompt.textContent = `"${data.prompt}"`;
        resultMeta.textContent = `Thể loại: ${data.genre} | Tâm trạng: ${data.mood} | Thời lượng: ${data.duration}s`;
        
        // Nạp nguồn file nhạc
        resultAudioElement.src = BASE_URL + data.audio_file.replace(/^\/+/, '');
        resultProgressFill.style.width = '0%';
        resultTimeCurrent.textContent = '00:00';
        resultTimeTotal.textContent = formatTime(data.duration);
        resultPlayBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
        
        showToast('Tạo bài nhạc AI thành công!', 'success');
    }
    
    // Điều khiển trình nghe thử kết quả AI
    if (resultPlayBtn && resultAudioElement) {
        resultPlayBtn.addEventListener('click', () => {
            if (!resultAudioElement.src) return;
            
            if (resultAudioElement.paused) {
                // Tạm dừng trình phát nhạc cố định dưới trang để tránh chồng chéo âm thanh
                const mainAudio = document.getElementById('mainAudioElement');
                if (mainAudio && !mainAudio.paused) {
                    mainAudio.pause();
                    const mainPlayBtn = document.getElementById('playerPlayBtn');
                    if (mainPlayBtn) mainPlayBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
                    const mainWave = document.getElementById('playerWaveVisualizer');
                    if (mainWave) mainWave.classList.remove('playing');
                }
                
                resultAudioElement.play().then(() => {
                    resultPlayBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                }).catch(err => console.error(err));
            } else {
                resultAudioElement.pause();
                resultPlayBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            }
        });
        
        resultAudioElement.addEventListener('timeupdate', () => {
            if (isNaN(resultAudioElement.duration)) return;
            const percent = (resultAudioElement.currentTime / resultAudioElement.duration) * 100;
            resultProgressFill.style.width = percent + '%';
            resultTimeCurrent.textContent = formatTime(resultAudioElement.currentTime);
        });
        
        resultAudioElement.addEventListener('ended', () => {
            resultPlayBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            resultProgressFill.style.width = '0%';
            resultTimeCurrent.textContent = '00:00';
        });
        
        resultProgress.addEventListener('click', (e) => {
            if (isNaN(resultAudioElement.duration)) return;
            const rect = resultProgress.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            resultAudioElement.currentTime = (clickX / width) * resultAudioElement.duration;
        });
    }
    
    // Lưu bài nhạc AI vào Thư viện cá nhân
    if (saveAiSongBtn) {
        saveAiSongBtn.addEventListener('click', async () => {
            if (!generatedSongData) return;
            
            saveAiSongBtn.disabled = true;
            saveAiSongBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
            
            try {
                const res = await callAPI(BASE_URL + 'api/ai/save_ai_song.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `prompt=${encodeURIComponent(generatedSongData.prompt)}&genre=${generatedSongData.genre}&mood=${generatedSongData.mood}&duration=${generatedSongData.duration}&audio_file=${encodeURIComponent(generatedSongData.audio_file)}`
                });
                
                if (res.success) {
                    showToast('Đã lưu bài hát vào thư viện cá nhân thành công!', 'success');
                    saveAiSongBtn.innerHTML = '<i class="fa-solid fa-check"></i> Đã lưu thành công';
                    saveAiSongBtn.className = 'btn btn-secondary';
                } else {
                    showToast(res.message, 'error');
                    saveAiSongBtn.disabled = false;
                    saveAiSongBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Lưu vào thư viện';
                }
            } catch (err) {
                console.error('Save AI song error:', err);
                showToast('Đã xảy ra lỗi khi kết nối API lưu bài hát!', 'error');
                saveAiSongBtn.disabled = false;
                saveAiSongBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Lưu vào thư viện';
            }
        });
    }
    
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
});
