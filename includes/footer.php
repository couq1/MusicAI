<?php
// Footer chung cho trang người dùng
?>
    </div> <!-- Kết thúc .app-container từ header.php -->

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <h3>Music<span class="highlight">AI</span></h3>
                <p>Nền tảng âm nhạc thế hệ mới kết hợp Trí tuệ nhân tạo (AI Generator) và trải nghiệm chơi beat tương tác kiểu Incredibox.</p>
                <div class="footer-socials">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#"><i class="fa-brands fa-soundcloud"></i></a>
                    <a href="#"><i class="fa-brands fa-github"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h4>Khám phá</h4>
                <ul>
                    <li><a href="<?php echo url('music.php'); ?>"><i class="fa-solid fa-play"></i> Nghe nhạc</a></li>
                    <li><a href="<?php echo url('generate.php'); ?>"><i class="fa-solid fa-wand-magic-sparkles"></i> Tạo nhạc AI</a></li>
                    <li><a href="<?php echo url('beatmaker.php'); ?>"><i class="fa-solid fa-shapes"></i> Beatmaker</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>Thư viện cá nhân</h4>
                <ul>
                    <li><a href="<?php echo url('library.php'); ?>"><i class="fa-solid fa-folder"></i> Thư viện của tôi</a></li>
                    <li><a href="<?php echo url('favorites.php'); ?>"><i class="fa-solid fa-heart"></i> Bài hát yêu thích</a></li>
                    <li><a href="<?php echo url('history.php'); ?>"><i class="fa-solid fa-history"></i> Lịch sử đã nghe</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2026 MusicAI.</p>
        </div>
    </footer>

    <!-- Gọi Music Player cố định ở dưới trang -->
    <?php include_once __DIR__ . '/music_player.php'; ?>

    <!-- Cấu hình các biến toàn cục cho JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const IS_LOGGED_IN = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>

    <!-- Các file JS lõi -->
    <script src="<?php echo url('assets/js/main.js'); ?>"></script>
    <script src="<?php echo url('assets/js/player.js'); ?>"></script>
    
    <!-- Các file JS bổ sung theo từng trang -->
    <?php 
    if (isset($extra_js) && is_array($extra_js)) {
        foreach ($extra_js as $js_file) {
            echo '<script src="' . url('assets/js/' . $js_file) . '"></script>' . "\n";
        }
    }
    ?>
</body>
</html>
