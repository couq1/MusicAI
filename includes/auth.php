<?php
// Quản lý xác thực và phân quyền người dùng

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Đảm bảo BASE_URL được định nghĩa
if (!defined('BASE_URL')) {
    define('BASE_URL', '/MusicAI/');
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

/**
 * Kiểm tra người dùng hiện tại có phải là Admin hay không
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Bắt buộc người dùng phải đăng nhập, nếu chưa sẽ chuyển hướng tới login.php
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

/**
 * Bắt buộc người dùng phải là Admin, nếu không sẽ chặn và hiển thị thông báo
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        echo "<!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <title>Truy cập bị từ chối</title>
            <style>
                body { background: #050807; color: #f4fff9; font-family: sans-serif; text-align: center; padding-top: 100px; }
                h1 { color: #00ff88; }
                a { color: #8da99a; text-decoration: none; border: 1px solid #00ff88; padding: 10px 20px; border-radius: 30px; display: inline-block; margin-top: 20px; transition: all 0.3s; }
                a:hover { background: #00ff88; color: #050807; box-shadow: 0 0 15px #00ff88; }
            </style>
        </head>
        <body>
            <h1>403 Access Denied</h1>
            <p>Xin lỗi! Bạn không có quyền truy cập vào khu vực Quản trị Admin.</p>
            <a href='" . BASE_URL . "index.php'>Quay lại Trang chủ</a>
        </body>
        </html>";
        exit();
    }
}
