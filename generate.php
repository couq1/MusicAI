<?php
$page_title = 'Tạo nhạc AI - MusicAI';
$page_desc = 'Sử dụng mô hình trí tuệ nhân tạo để tự động biên soạn các bản nhạc độc quyền dựa trên prompt mô tả của bạn.';
$extra_css = ['generator.css'];
$extra_js = ['ai-generator.js'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Yêu cầu người dùng đăng nhập mới được truy cập trang này
requireLogin();
?>

<div class="container">
    <h2 class="section-title"><i class="fa-solid fa-wand-magic-sparkles"></i> MusicAI Generator </h2>
    
    <div class="generator-layout">
        <!-- Panel Form Điều khiển nhập liệu -->
        <div class="glass-card generator-form-panel">
            <form id="generatorForm">
                <div class="form-group">
                    <label for="prompt">Nhập mô tả ý tưởng bài nhạc</label>
                    <div class="prompt-textarea-wrapper">
                        <textarea id="prompt" name="prompt" class="form-control" placeholder="Ví dụ: A chill lofi beat with electric piano and soft rain sounds in the background..." maxlength="250" required></textarea>
                        <span class="prompt-char-counter" id="charCounter">0/250</span>
                    </div>
                </div>
                
                <div class="options-grid">
                    <div class="form-group">
                        <label for="genre">Thể loại</label>
                        <select id="genre" name="genre" class="form-control">
                            <option value="Lofi">Lofi</option>
                            <option value="EDM">EDM</option>
                            <option value="Ambient">Ambient</option>

                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mood">Tâm trạng</label>
                        <select id="mood" name="mood" class="form-control">
                            <option value="Relax">Thư giãn (Relax)</option>
                            <option value="Happy">Vui vẻ (Happy)</option>
                            <option value="Sad">U buồn (Sad)</option>
                            <option value="Dark">Kịch tính/Tối tăm (Dark)</option>
                            <option value="Energetic">Năng động (Energetic)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">Thời lượng</label>
                        <select id="duration" name="duration" class="form-control">
                            <option value="15">15 giây</option>
                            <option value="30" selected>30 giây</option>
                            <option value="60">60 giây</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 15px;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Bắt đầu tạo nhạc AI
                </button>
            </form>
        </div>
        
        <!-- Panel kết quả / Trạng thái loading -->
        <div class="glass-card generator-result-panel">
            <!-- 1. Trạng thái mặc định khi chưa bấm tạo -->
            <div class="result-placeholder" id="resultPlaceholder">
                <i class="fa-solid fa-compact-disc"></i>
                <h3>Đang đợi yêu cầu tạo nhạc</h3>
                <p>Nhập mô tả ở bảng bên trái và nhấn nút "Bắt đầu tạo nhạc" để trải nghiệm công nghệ AI.</p>
            </div>
            
            <!-- 2. Trạng thái đang tải (Loading Animation) -->
            <div class="generator-loading-wrapper" id="generatorLoading" style="display: none;">
                <div class="generator-loading-spinner">
                    <div class="spinner-outer"></div>
                    <div class="spinner-inner"></div>
                    <div class="spinner-icon"><i class="fa-solid fa-microchip"></i></div>
                </div>
                <div class="loading-text">AI đang sáng tác âm nhạc...</div>
                <div class="loading-subtext">Quá trình này có thể mất từ 15 đến 30 giây. Xin vui lòng giữ kết nối.</div>
            </div>
            
            <!-- 3. Kết quả bài nhạc sau khi tạo xong -->
            <div class="result-card" id="resultCard" style="display: none;">
                <div class="result-header">
                    <div class="result-icon-box">
                        <i class="fa-solid fa-volume-high"></i>
                    </div>
                    <div class="result-meta">
                        <h3 id="resultTitle">Bản Nhạc AI Sáng Tạo</h3>
                        <p id="resultMeta">Thể loại: Lofi | Tâm trạng: Relax | Thời lượng: 30s</p>
                    </div>
                </div>
                
                <div class="result-prompt-box">
                    <strong>Prompt mô tả:</strong>
                    <span id="resultPrompt">"A chill lofi beat with electric piano"</span>
                </div>
                
                <!-- Mini Player phát thử bài hát vừa tạo -->
                <div class="result-player">
                    <div class="result-player-controls">
                        <button class="result-play-btn" id="resultPlayBtn" title="Phát thử">
                            <i class="fa-solid fa-play"></i>
                        </button>
                        
                        <div class="result-progress-wrapper">
                            <div class="result-progress-bar" id="resultProgress">
                                <div class="result-progress-fill" id="resultProgressFill"></div>
                            </div>
                            <div class="result-time-info">
                                <span id="resultTimeCurrent">00:00</span>
                                <span id="resultTimeTotal">00:30</span>
                            </div>
                        </div>
                    </div>
                    <!-- Audio element chứa link stream nhạc AI -->
                    <audio id="resultAudioElement" style="display: none;"></audio>
                </div>
                
                <div class="result-actions">
                    <button class="btn btn-primary" id="saveAiSongBtn" style="flex-grow: 1;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Lưu vào thư viện cá nhân
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const promptTextarea = document.getElementById('prompt');
    const charCounter = document.getElementById('charCounter');
    
    // Đếm ký tự prompt
    if (promptTextarea && charCounter) {
        promptTextarea.addEventListener('input', () => {
            const length = promptTextarea.value.length;
            charCounter.textContent = `${length}/250`;
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
