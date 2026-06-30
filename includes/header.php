<?php
// Khởi chạy các file cấu hình và kiểm tra session
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($page_desc) ? $page_desc : 'MusicAI - Nền tảng nghe nhạc, tạo nhạc AI và sáng tạo Beat chất lượng cao.'; ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME . ' | Nghe nhạc & Tạo nhạc AI'; ?></title>
    
    <!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Kaushan+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/player.css'); ?>">
    <?php  
    if (isset($extra_css) && is_array($extra_css)) {
        foreach ($extra_css as $css_file) {
            echo '<link rel="stylesheet" href="' . url('assets/css/' . $css_file) . '">' . "\n";
        }
    }
    ?>
    <link rel="stylesheet" href="<?php echo url('assets/css/responsive.css'); ?>">
</head>
<body>
    <div class="app-container">
