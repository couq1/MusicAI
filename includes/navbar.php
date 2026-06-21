<?php
// Navbar chung cho trang người dùng
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="main-navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <a href="<?php echo url('index.php'); ?>" class="navbar-logo">
            <span class="logo-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
            <span class="logo-text">Music<span class="highlight">AI</span></span>
        </a>
        
        <!-- Toggle Menu Mobile -->
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        
        <!-- Links Menu -->
        <ul class="navbar-links" id="navbarLinks">
            <li>
                <a href="<?php echo url('index.php'); ?>" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i> <span>Trang chủ</span>
                </a>
            </li>
            <li>
                <a href="<?php echo url('music.php'); ?>" class="<?php echo $current_page == 'music.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-music"></i> <span>Nghe nhạc</span>
                </a>
            </li>
            <li>
                <a href="<?php echo url('generate.php'); ?>" class="<?php echo $current_page == 'generate.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-wand-magic"></i> <span>Tạo nhạc AI</span>
                </a>
            </li>
            <li>
                <a href="<?php echo url('beatmaker.php'); ?>" class="<?php echo $current_page == 'beatmaker.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-shapes"></i> <span>Beatmaker</span>
                </a>
            </li>
            
            <?php if (isLoggedIn()): ?>
                <li>
                    <a href="<?php echo url('library.php'); ?>" class="<?php echo $current_page == 'library.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-compact-disc"></i> <span>Thư viện</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('favorites.php'); ?>" class="<?php echo $current_page == 'favorites.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-heart"></i> <span>Yêu thích</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo url('history.php'); ?>" class="<?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i> <span>Lịch sử</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <!-- User Actions -->
        <div class="navbar-actions">
            <?php if (isLoggedIn()): ?>
                <div class="user-profile-menu">
                    <div class="profile-trigger" id="profileTrigger">
                        <img src="<?php echo url(!empty($_SESSION['user']['avatar']) ? $_SESSION['user']['avatar'] : DEFAULT_AVATAR); ?>" alt="Avatar" class="avatar-small">
                        <span class="username-text"><?php echo sanitize($_SESSION['user']['name']); ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <ul class="profile-dropdown" id="profileDropdown">
                        <?php if (isAdmin()): ?>
                            <li>
                                <a href="<?php echo url('admin/dashboard.php'); ?>">
                                    <i class="fa-solid fa-chart-line"></i> Trang Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?php echo url('logout.php'); ?>" class="logout-link">
                                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?php echo url('login.php'); ?>" class="btn btn-outline btn-sm">Đăng nhập</a>
                <a href="<?php echo url('register.php'); ?>" class="btn btn-primary btn-sm">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
