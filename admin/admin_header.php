<?php
// Admin Header chung cho các trang Dashboard
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Kiểm tra quyền Admin
requireAdmin();

$current_admin_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin Panel' : 'Admin Dashboard'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Core App CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    
    <style>
        /* CSS Giao diện Quản trị riêng */
        body {
            background-color: #030605;
            color: #f4fff9;
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 260px;
            background-color: #07130f;
            border-right: 1px solid rgba(0, 255, 136, 0.15);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }
        
        .sidebar-brand {
            padding: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-heading);
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
        }
        
        .sidebar-brand span {
            color: var(--green-main);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
            padding: 0 16px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all var(--transition-fast);
        }
        
        .sidebar-menu a:hover, .sidebar-menu li.active a {
            color: var(--green-main);
            background: rgba(0, 255, 136, 0.08);
            text-shadow: 0 0 5px var(--green-glow);
        }
        
        .admin-main-wrapper {
            margin-left: 260px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        
        .admin-navbar {
            height: 70px;
            background: #050807;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
        }
        
        .admin-content {
            padding: 40px;
            flex-grow: 1;
        }
        
        /* Stats Dashboard cards */
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(0, 255, 136, 0.12);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stat-info h4 {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .stat-info .stat-value {
            font-size: 1.8rem;
            font-family: var(--font-heading);
            font-weight: 800;
            color: #fff;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(0, 255, 136, 0.08);
            color: var(--green-main);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        
        /* Table Styles */
        .admin-table-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 24px;
            margin-top: 20px;
            overflow-x: auto;
        }
        
        .table-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        
        .admin-table th, .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            font-size: 0.9rem;
        }
        
        .admin-table th {
            font-family: var(--font-heading);
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid rgba(255, 255, 255, 0.08);
        }
        
        .admin-table tr:hover td {
            background: rgba(255, 255, 255, 0.015);
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-success { background: rgba(0, 255, 136, 0.12); color: var(--green-main); }
        .badge-warning { background: rgba(255, 204, 0, 0.12); color: #ffcc00; }
        .badge-danger { background: rgba(255, 77, 77, 0.12); color: #ff4d4d; }
        
        /* Admin Form modal or card */
        .admin-form-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(0, 255, 136, 0.18);
            border-radius: 16px;
            padding: 30px;
            max-width: 650px;
            margin: 20px auto;
        }
        
        .alert-box {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .alert-success { background: rgba(0, 255, 136, 0.1); border: 1px solid var(--green-main); color: var(--green-main); }
        .alert-danger { background: rgba(255, 77, 77, 0.1); border: 1px solid #ff4d4d; color: #ff4d4d; }
    </style>
</head>
<body>
    
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-gauge-high"></i> Music<span>AI Admin</span>
        </div>
        <ul class="sidebar-menu">
            <li class="<?php echo $current_admin_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo url('admin/dashboard.php'); ?>">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li class="<?php echo $current_admin_page == 'users.php' ? 'active' : ''; ?>">
                <a href="<?php echo url('admin/users.php'); ?>">
                    <i class="fa-solid fa-user-group"></i> Thành viên
                </a>
            </li>
            <li class="<?php echo $current_admin_page == 'songs.php' ? 'active' : ''; ?>">
                <a href="<?php echo url('admin/songs.php'); ?>">
                    <i class="fa-solid fa-music"></i> Quản lý Bài hát
                </a>
            </li>
            <li class="<?php echo $current_admin_page == 'ai_music.php' ? 'active' : ''; ?>">
                <a href="<?php echo url('admin/ai_music.php'); ?>">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Nhạc AI tự tạo
                </a>
            </li>
            <li class="<?php echo $current_admin_page == 'beats.php' ? 'active' : ''; ?>">
                <a href="<?php echo url('admin/beats.php'); ?>">
                    <i class="fa-solid fa-shapes"></i> Beatmaker Sounds
                </a>
            </li>

            <li style="margin-top: 40px; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 15px;">
                <a href="<?php echo url('index.php'); ?>" style="color: #ff9900;">
                    <i class="fa-solid fa-house"></i> Về Trang chủ
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Wrapper -->
    <div class="admin-main-wrapper">
        <!-- Admin Nav -->
        <nav class="admin-navbar">
            <h3 style="font-family: var(--font-heading);"><?php echo isset($page_title) ? $page_title : 'Admin Dashboard'; ?></h3>
            <div class="admin-profile-info" style="display:flex; align-items:center; gap: 10px;">
                <img src="<?php echo url(!empty($_SESSION['user']['avatar']) ? $_SESSION['user']['avatar'] : DEFAULT_AVATAR); ?>" alt="Admin Avatar" style="width:32px; height:32px; border-radius:50%; object-fit:cover; border:1px solid var(--green-main);">
                <span><?php echo sanitize($_SESSION['user']['name']); ?> (Admin)</span>
            </div>
        </nav>
        
        <div class="admin-content">
