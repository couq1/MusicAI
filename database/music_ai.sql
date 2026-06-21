-- ==========================================
-- SƠ ĐỒ CẤU TRÚC CƠ SỞ DỮ LIỆU DỰ ÁN MUSICAI
-- Chạy trên MySQL / MariaDB (XAMPP phpMyAdmin)
-- ==========================================

CREATE DATABASE IF NOT EXISTS `music_ai` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `music_ai`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `listening_history`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `beat_mixes`;
DROP TABLE IF EXISTS `beat_sounds`;
DROP TABLE IF EXISTS `ai_songs`;
DROP TABLE IF EXISTS `songs`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Bảng Lưu Trữ Thành Viên (users)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT 'Họ và tên thành viên',
  `email` VARCHAR(150) NOT NULL UNIQUE COMMENT 'Địa chỉ email đăng nhập',
  `password` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa bcrypt',
  `role` ENUM('user', 'admin') DEFAULT 'user' COMMENT 'Phân quyền tài khoản',
  `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh đại diện',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng Lưu Trữ Bài Hát Gốc Của Hệ Thống (songs)
CREATE TABLE `songs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL COMMENT 'Tiêu đề bài hát',
  `artist` VARCHAR(100) NOT NULL COMMENT 'Tên ca sĩ / nhóm nhạc',
  `genre` VARCHAR(50) NOT NULL COMMENT 'Thể loại âm nhạc',
  `audio_file` VARCHAR(255) NOT NULL COMMENT 'Đường dẫn tệp tin âm nhạc (.mp3)',
  `thumbnail` VARCHAR(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh bìa bài hát',
  `plays` INT DEFAULT 0 COMMENT 'Tổng số lượt phát nhạc',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng Lưu Trữ Bài Nhạc Sinh Bởi AI (ai_songs)
CREATE TABLE `ai_songs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL COMMENT 'ID của thành viên sở hữu',
  `prompt` TEXT NOT NULL COMMENT 'Mô tả đầu vào gửi cho AI',
  `genre` VARCHAR(50) NOT NULL COMMENT 'Thể loại nhạc do AI áp dụng',
  `mood` VARCHAR(50) NOT NULL COMMENT 'Tâm trạng bài hát',
  `duration` INT NOT NULL COMMENT 'Thời lượng nhạc (giây)',
  `audio_file` VARCHAR(255) NOT NULL COMMENT 'Đường dẫn file âm thanh kết quả',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng Cấu Hình Loops Cho Bộ Mixer Beatmaker (beat_sounds)
CREATE TABLE `beat_sounds` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT 'Tên của sound loop',
  `category` ENUM('drums', 'bass', 'melody', 'vocals', 'effects') NOT NULL COMMENT 'Nhóm phân loại trống, bass, vocal...',
  `audio_file` VARCHAR(255) NOT NULL COMMENT 'Đường dẫn file loop âm thanh',
  `status` ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Trạng thái hoạt động',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng Lưu Trữ Bản Beat Phối Mix Do Người Dùng Lưu (beat_mixes)
CREATE TABLE `beat_mixes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL COMMENT 'Thành viên sở hữu bản mix',
  `name` VARCHAR(150) NOT NULL COMMENT 'Tên bản mix tự đặt',
  `mix_data` JSON NOT NULL COMMENT 'Mảng lưu trữ ID các loops được kích hoạt',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng Trung Gian Lưu Danh Sách Yêu Thích (favorites)
CREATE TABLE `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `song_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_song_unique` (`user_id`, `song_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng Lưu Lịch Sử Nghe Gần Đây (listening_history)
CREATE TABLE `listening_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `song_id` INT NOT NULL,
  `listened_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
