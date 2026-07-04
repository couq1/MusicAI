-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 04, 2026 lúc 01:53 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `music_ai`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ai_songs`
--

CREATE TABLE `ai_songs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID của thành viên sở hữu',
  `prompt` text NOT NULL COMMENT 'Mô tả đầu vào gửi cho AI',
  `genre` varchar(50) NOT NULL COMMENT 'Thể loại nhạc do AI áp dụng',
  `mood` varchar(50) NOT NULL COMMENT 'Tâm trạng bài hát',
  `duration` int(11) NOT NULL COMMENT 'Thời lượng nhạc (giây)',
  `audio_file` varchar(255) NOT NULL COMMENT 'Đường dẫn file âm thanh kết quả',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ai_songs`
--

INSERT INTO `ai_songs` (`id`, `user_id`, `prompt`, `genre`, `mood`, `duration`, `audio_file`, `created_at`) VALUES
(1, 3, '123', 'Lofi', 'Relax', 15, 'storage/demo/lofi3.mp3', '2026-06-25 16:54:14'),
(2, 1, '123', 'Lofi', 'Relax', 30, 'storage/demo/lofi2.mp3', '2026-06-26 11:02:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beat_mixes`
--

CREATE TABLE `beat_mixes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Thành viên sở hữu bản mix',
  `name` varchar(150) NOT NULL COMMENT 'Tên bản mix tự đặt',
  `mix_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Mảng lưu trữ ID các loops được kích hoạt' CHECK (json_valid(`mix_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beat_sounds`
--

CREATE TABLE `beat_sounds` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Tên của sound loop',
  `category` enum('drums','bass','melody','vocals','effects') NOT NULL COMMENT 'Nhóm phân loại trống, bass, vocal...',
  `audio_file` varchar(255) NOT NULL COMMENT 'Đường dẫn file loop âm thanh',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT 'Trạng thái hoạt động',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `beat_sounds`
--

INSERT INTO `beat_sounds` (`id`, `name`, `category`, `audio_file`, `status`, `created_at`) VALUES
(1, ' Drums1', 'drums', 'storage/beats/drums1.mp3', 'active', '2026-06-21 16:08:13'),
(2, ' Drums2', 'drums', 'storage/beats/drums2.mp3', 'active', '2026-06-21 16:08:13'),
(3, ' Drums3', 'drums', 'storage/beats/drums3.mp3', 'active', '2026-06-21 16:08:13'),
(4, ' Drums4', 'drums', 'storage/beats/drums4.mp3', 'active', '2026-06-21 16:08:13'),
(5, 'Drums5', 'drums', 'storage/beats/drums5.mp3', 'active', '2026-06-21 16:08:13'),
(6, 'Bass1', 'bass', 'storage/beats/bass1.mp3', 'active', '2026-06-21 16:08:13'),
(7, 'Bass2', 'bass', 'storage/beats/bass2.mp3', 'active', '2026-06-21 16:08:13'),
(8, 'Bass3', 'bass', 'storage/beats/bass3.mp3', 'active', '2026-06-21 16:08:13'),
(9, 'Bass4', 'bass', 'storage/beats/bass4.mp3', 'active', '2026-06-21 16:08:13'),
(10, 'Bass5', 'bass', 'storage/beats/bass5.mp3', 'active', '2026-06-21 16:08:13'),
(11, 'Melody1', 'melody', 'storage/beats/melody1.mp3', 'active', '2026-06-21 16:08:13'),
(12, 'Melody2', 'melody', 'storage/beats/melody2.mp3', 'active', '2026-06-21 16:08:13'),
(13, 'Melody3', 'melody', 'storage/beats/melody3.mp3', 'active', '2026-06-21 16:08:13'),
(14, 'Melody4', 'melody', 'storage/beats/melody4.mp3', 'active', '2026-06-21 16:08:13'),
(15, 'Melody5', 'melody', 'storage/beats/melody5.mp3', 'active', '2026-06-21 16:08:13'),
(16, 'Vocals1', 'vocals', 'storage/beats/vocals1.mp3', 'active', '2026-06-21 16:08:13'),
(17, 'Vocals2', 'vocals', 'storage/beats/vocals2.mp3', 'active', '2026-06-21 16:08:13'),
(18, 'Vocals3', 'vocals', 'storage/beats/vocals3.mp3', 'active', '2026-06-21 16:08:13'),
(19, 'Vocals4', 'vocals', 'storage/beats/vocals4.mp3', 'active', '2026-06-21 16:08:13'),
(20, 'Vocals5', 'vocals', 'storage/beats/vocals5.mp3', 'active', '2026-06-21 16:08:13'),
(21, 'Effects1', 'effects', 'storage/beats/effects1.mp3', 'active', '2026-06-21 16:08:13'),
(22, 'Effects2', 'effects', 'storage/beats/effects2.mp3', 'active', '2026-06-21 16:08:13'),
(23, 'Effects3', 'effects', 'storage/beats/effects3.mp3', 'active', '2026-06-21 16:08:13'),
(24, 'Effects4', 'effects', 'storage/beats/effects4.mp3', 'active', '2026-06-21 16:08:13'),
(25, 'Effects5', 'effects', 'storage/beats/effects5.mp3', 'active', '2026-06-21 16:08:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `song_id`, `created_at`) VALUES
(5, 2, 6, '2026-06-26 11:07:54'),
(6, 2, 5, '2026-06-26 11:07:56'),
(7, 2, 7, '2026-06-26 11:30:32'),
(8, 2, 4, '2026-06-26 11:30:35'),
(9, 2, 3, '2026-06-26 11:30:38'),
(10, 2, 1, '2026-06-26 11:30:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `listening_history`
--

CREATE TABLE `listening_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `listened_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `listening_history`
--

INSERT INTO `listening_history` (`id`, `user_id`, `song_id`, `listened_at`) VALUES
(1, 2, 8, '2026-06-21 16:08:38'),
(2, 2, 7, '2026-06-21 16:08:52'),
(3, 2, 6, '2026-06-21 16:09:01'),
(4, 2, 8, '2026-06-21 16:09:05'),
(5, 2, 8, '2026-06-21 16:11:12'),
(6, 2, 8, '2026-06-21 16:11:16'),
(7, 2, 8, '2026-06-21 16:12:03'),
(8, 2, 5, '2026-06-21 16:36:18'),
(9, 2, 4, '2026-06-21 16:36:45'),
(10, 2, 3, '2026-06-21 16:36:48'),
(11, 2, 2, '2026-06-21 16:36:50'),
(12, 2, 1, '2026-06-21 16:36:52'),
(13, 2, 8, '2026-06-26 10:58:26'),
(15, 1, 8, '2026-06-26 11:01:31'),
(17, 1, 7, '2026-06-26 11:01:42'),
(19, 2, 6, '2026-06-26 11:07:53'),
(20, 2, 5, '2026-06-26 11:07:55'),
(21, 2, 5, '2026-06-26 11:27:05'),
(22, 2, 7, '2026-06-26 11:30:31'),
(23, 2, 4, '2026-06-26 11:30:35'),
(24, 2, 3, '2026-06-26 11:30:37'),
(25, 2, 5, '2026-06-26 11:30:39'),
(26, 2, 1, '2026-06-26 11:30:40'),
(27, 2, 1, '2026-06-26 11:45:40'),
(28, 2, 3, '2026-06-26 11:45:41'),
(29, 2, 4, '2026-06-26 11:45:42'),
(30, 2, 1, '2026-06-26 11:45:43'),
(31, 2, 3, '2026-06-26 11:45:44'),
(32, 2, 4, '2026-06-26 11:45:45'),
(33, 1, 12, '2026-06-26 12:13:37'),
(34, 1, 12, '2026-06-26 12:13:54'),
(35, 1, 9, '2026-06-26 12:28:15'),
(36, 1, 13, '2026-06-26 12:31:56'),
(37, 1, 31, '2026-07-03 17:16:08'),
(38, 1, 30, '2026-07-03 17:16:13'),
(39, 1, 29, '2026-07-03 17:16:15'),
(40, 1, 28, '2026-07-03 17:16:20'),
(41, 1, 27, '2026-07-03 17:16:22'),
(42, 1, 26, '2026-07-03 17:16:23'),
(43, 1, 25, '2026-07-03 17:16:27'),
(44, 1, 24, '2026-07-03 17:16:31'),
(45, 1, 20, '2026-07-03 17:16:33'),
(46, 1, 47, '2026-07-04 05:39:51'),
(47, 1, 47, '2026-07-04 05:41:19'),
(48, 2, 47, '2026-07-04 05:44:36'),
(49, 2, 47, '2026-07-04 05:44:44'),
(50, 2, 46, '2026-07-04 05:49:24'),
(51, 2, 25, '2026-07-04 05:53:12'),
(52, 2, 24, '2026-07-04 05:56:31'),
(53, 2, 23, '2026-07-04 06:00:44'),
(54, 2, 22, '2026-07-04 06:03:21'),
(55, 2, 21, '2026-07-04 06:07:45'),
(56, 2, 20, '2026-07-04 06:12:27'),
(57, 2, 47, '2026-07-04 06:15:34'),
(58, 2, 46, '2026-07-04 06:20:14'),
(59, 2, 25, '2026-07-04 06:24:01'),
(60, 2, 24, '2026-07-04 06:27:21'),
(61, 2, 23, '2026-07-04 06:31:33'),
(62, 2, 22, '2026-07-04 06:34:11'),
(63, 2, 21, '2026-07-04 06:38:34'),
(64, 2, 20, '2026-07-04 06:43:16'),
(65, 2, 47, '2026-07-04 06:46:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL COMMENT 'Tiêu đề bài hát',
  `artist` varchar(100) NOT NULL COMMENT 'Tên ca sĩ / nhóm nhạc',
  `genre` varchar(50) NOT NULL COMMENT 'Thể loại âm nhạc',
  `audio_file` varchar(255) NOT NULL COMMENT 'Đường dẫn tệp tin âm nhạc (.mp3)',
  `thumbnail` varchar(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh bìa bài hát',
  `plays` int(11) DEFAULT 0 COMMENT 'Tổng số lượt phát nhạc',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `songs`
--

INSERT INTO `songs` (`id`, `title`, `artist`, `genre`, `audio_file`, `thumbnail`, `plays`, `created_at`) VALUES
(1, 'Lofi Dream', 'Sunset Beats', 'Lofi', 'storage/samples/lofi_loop.mp3', 'assets/images/dream.jpg', 1254, '2026-06-21 16:08:13'),
(2, 'EDM Energy', 'Cyber DJ', 'EDM', 'storage/samples/edm_loop.mp3', 'assets/images/edm.jpg', 981, '2026-06-21 16:08:13'),
(3, 'Acoustic Sun', 'Nature Sound', 'Lofi', 'storage/samples/chill_loop.mp3', 'assets/images/aucotic.jpg', 644, '2026-06-21 16:08:13'),
(4, 'Hard Trap Anthem', 'Bass Producer', 'Rap', 'storage/samples/trap_loop.mp3', 'assets/images/trap.jpg', 40, '2026-06-21 16:08:13'),
(5, 'Lofi_chill for learning\r\n', 'Q1', 'Lofi', 'storage/samples/lofi_chill.mp3', 'assets/images/chill.jpg', 2407, '2026-06-21 16:08:13'),
(6, 'Piano', 'Alex', 'Piano', 'storage/samples/piano_loop.mp3', 'assets/images/piano.jpg', 20, '2026-06-21 16:08:13'),
(7, 'Ambient_music\r\n', 'Antony', 'Ambient', 'storage/samples/ambient_loop.mp3', 'assets/images/ambient.jpg', 33, '2026-06-21 16:08:13'),
(8, 'Hiphop_dance', 'Vicky', 'HipHop', 'storage/samples/hiphop_loop.mp3', 'assets/images/hiphop.jpg', 975, '2026-06-21 16:08:13'),
(9, 'Người hay nói gặp người lặng im', 'Hiếu Thứ Hai', 'Rap', 'storage/samples/1782476004_6a3e6ce4231c1.mp3', 'assets/images/1782476004_6a3e6ce423627.jpg', 3, '2026-06-26 12:13:24'),
(10, 'Hóa Đơn', 'Trịnh Kiên', 'Pop', 'storage/samples/1782477103_6a3e712f0c780.mp3', 'assets/images/1782477103_6a3e712f0d5af.png', 1, '2026-06-26 12:31:43'),
(14, 'Hãy Trao cho anh', 'Sơn Tùng MTP', 'Pop', 'storage/samples/1783096309_6a47e3f5f11fd.mp3', 'assets/images/1783096309_6a47e3f5f1bdb.png', 0, '2026-07-03 16:31:49'),
(15, 'Hoa nở không màu', 'Hoài Lâm', 'Pop', 'storage/samples/1783096350_6a47e41e44b66.mp3', 'assets/images/1783096350_6a47e41e44f16.png', 0, '2026-07-03 16:32:30'),
(16, 'Tìm lại bầu trời', 'Tuấn Hứng', 'Pop', 'storage/samples/1783096385_6a47e44111460.mp3', 'assets/images/1783096385_6a47e44111a11.png', 0, '2026-07-03 16:33:05'),
(17, 'Nhạt', 'Phan Mạnh Quỳnh', 'Pop', 'storage/samples/1783096445_6a47e47de2596.mp3', 'assets/images/1783096445_6a47e47de2ce9.png', 0, '2026-07-03 16:34:05'),
(18, 'Ambient-432Hz', 'Calim', 'Ambient', 'storage/samples/1783097023_6a47e6bf94aed.mp3', 'assets/images/1783097023_6a47e6bf957f1.png', 0, '2026-07-03 16:43:43'),
(19, 'HipHopbasso', 'JabbWokee', 'Hiphop', 'storage/samples/1783097073_6a47e6f1b2668.mp3', 'assets/images/1783097073_6a47e6f1b2c1f.png', 0, '2026-07-03 16:44:33'),
(20, 'Ordinary', 'Alex', 'US-UK', 'storage/samples/1783097997_6a47ea8daa3a7.mp3', 'assets/images/1783097997_6a47ea8dab32d.png', 3, '2026-07-03 16:59:57'),
(21, 'Perfect', 'Ed Sheeran', 'US-UK', 'storage/samples/1783098053_6a47eac55bc60.mp3', 'assets/images/1783098053_6a47eac55ce38.png', 2, '2026-07-03 17:00:53'),
(22, 'Shape of you', 'Ed SheeranEd', 'US-UK', 'storage/samples/1783098084_6a47eae4e0e5c.mp3', 'assets/images/1783098084_6a47eae4e150b.png', 2, '2026-07-03 17:01:24'),
(23, 'Old town road', 'Lil Nax X', 'US-UK', 'storage/samples/1783098123_6a47eb0b14f6b.mp3', 'assets/images/1783098123_6a47eb0b158cc.png', 2, '2026-07-03 17:02:03'),
(24, 'Die with a smile', 'Lady Gaga, Bruno Mars', 'US-UK', 'storage/samples/1783098175_6a47eb3f01e4e.mp3', 'assets/images/1783098175_6a47eb3f022bb.png', 3, '2026-07-03 17:02:55'),
(25, 'The lazzy song', 'Bruno Mars', 'US-UK', 'storage/samples/1783098199_6a47eb573b36b.mp3', 'assets/images/1783098199_6a47eb573b88c.png', 3, '2026-07-03 17:03:19'),
(26, 'Em', 'Binz', 'Rap', 'storage/samples/1783098700_6a47ed4c52fbd.mp3', 'assets/images/1783098700_6a47ed4c538f8.jpg', 1, '2026-07-03 17:11:40'),
(27, 'Bắc Bling (Bắc Ninh)', 'Hòa Minzy feat Tuấn cry', 'Pop', 'storage/samples/1783098744_6a47ed786952f.mp3', 'assets/images/1783098744_6a47ed786993e.jpg', 1, '2026-07-03 17:12:24'),
(28, 'Come my way', 'Sơn Tùng MTP', 'Pop', 'storage/samples/1783098772_6a47ed94c7094.mp3', 'assets/images/1783098772_6a47ed94c781e.jpg', 1, '2026-07-03 17:12:52'),
(29, 'Đừng làm trái tim anh đau', 'Sơn Tùng MTP', 'Pop', 'storage/samples/1783098831_6a47edcf6304d.mp3', 'assets/images/1783098831_6a47edcf63552.jpg', 1, '2026-07-03 17:13:51'),
(30, 'Muộn rồi mà sao còn', 'Sơn Tùng MTP', 'Pop', 'storage/samples/1783098851_6a47ede3cc0b0.mp3', 'assets/images/1783098851_6a47ede3cc50d.jpg', 1, '2026-07-03 17:14:11'),
(31, 'Waiting for u', 'Mono', 'Pop', 'storage/samples/1783098882_6a47ee0281718.mp3', 'assets/images/1783098882_6a47ee0281d00.jpg', 1, '2026-07-03 17:14:42'),
(32, 'Lost Withn', 'A Himitsu', 'Ambient', 'storage/samples/1783142402_6a489802b4913.mp3', 'assets/images/1783142402_6a489802b5450.jpg', 0, '2026-07-04 05:20:02'),
(34, 'We\'re Finally Landing', 'Home', 'Ambient', 'storage/samples/1783142614_6a4898d6b54c8.mp3', 'assets/images/1783142614_6a4898d6b5db7.jpg', 0, '2026-07-04 05:23:34'),
(35, 'Faded', 'Alan Walker', 'EDM', 'storage/samples/1783142656_6a48990079c87.mp3', 'assets/images/1783142656_6a4899007a304.jpg', 0, '2026-07-04 05:24:16'),
(36, 'Levels', 'Avicii', 'EDM', 'storage/samples/1783142683_6a48991b440ef.mp3', 'assets/images/1783142683_6a48991b448e7.jpg', 0, '2026-07-04 05:24:43'),
(37, 'Animals', 'Martin Garrix', 'EDM', 'storage/samples/1783142864_6a4899d0ae6d7.mp3', 'assets/images/1783142864_6a4899d0aecbf.jpg', 0, '2026-07-04 05:27:44'),
(38, 'An Thần', 'LowG', 'Rap', 'storage/samples/1783142996_6a489a543edd3.mp3', 'assets/images/1783142996_6a489a543f893.jpg', 0, '2026-07-04 05:29:56'),
(39, 'Một triệu like', 'Đen Vâu', 'Rap', 'storage/samples/1783143073_6a489aa1c911c.mp3', 'assets/images/1783143073_6a489aa1c998f.jpg', 0, '2026-07-04 05:31:13'),
(42, 'Lớn rồi còn khóc nhè', 'Trúc Nhân', 'Pop', 'storage/samples/1783143306_6a489b8a5c36d.mp3', 'assets/images/1783143306_6a489b8a5c94d.jpg', 0, '2026-07-04 05:35:06'),
(43, 'Vùng kí ức', 'Chillies', 'Pop', 'storage/samples/1783143324_6a489b9c9767f.mp3', 'assets/images/1783143324_6a489b9c97e75.jpg', 0, '2026-07-04 05:35:24'),
(44, 'Không thể say', 'Hiếu Thứ Hai', 'Rap', 'storage/samples/1783143356_6a489bbc1afc9.mp3', 'assets/images/1783143356_6a489bbc1b822.jpg', 0, '2026-07-04 05:35:56'),
(45, 'Bâng khuân', 'JusstaTee', 'Rap', 'storage/samples/1783143382_6a489bd6c2f15.mp3', 'assets/images/1783143382_6a489bd6c3c85.jpg', 0, '2026-07-04 05:36:22'),
(46, 'Happy for you', 'Lukas Graham, Vũ', 'US-UK', 'storage/samples/1783143443_6a489c1382d98.mp3', 'assets/images/1783143443_6a489c138361e.jpg', 2, '2026-07-04 05:37:23'),
(47, 'Payphone', 'Maroon 5', 'US-UK', 'storage/samples/1783143475_6a489c33cdce3.mp3', 'assets/images/1783143475_6a489c33ce3e8.jpg', 7, '2026-07-04 05:37:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Họ và tên thành viên',
  `email` varchar(150) NOT NULL COMMENT 'Địa chỉ email đăng nhập',
  `password` varchar(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa bcrypt',
  `role` enum('user','admin') DEFAULT 'user' COMMENT 'Phân quyền tài khoản',
  `avatar` varchar(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh đại diện',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Quản Trị Viên', 'admin@musicai.local', '$2y$10$BCuIZM3n0DbXHITbPcYKOu0xLglBcClfv5lUweFBc2Fr4mXAVvk6O', 'admin', 'storage/avatars/user_1_1783137603.png', '2026-06-21 16:08:13', '2026-07-04 04:59:40'),
(2, 'Demo User Test', 'user@musicai.local', '$2y$10$g75U0sjf0gkvGGOjW2StGu4Dj/KNsNWtndfaGA3HZVJdlx5pfsL06', 'user', 'storage/avatars/user_2_1783140887.png', '2026-06-21 16:08:13', '2026-07-04 04:55:27'),
(3, 'LE ANH QUOC', 'lequoc0305@gmail.com', '$2y$10$N8eDEz8WnEi0zVpAdHrn9eo1Gj9TgjE0URjkhWRAswmE5s1a0/pBi', 'user', 'storage/avatars/user_3_1782398471.png', '2026-06-25 14:29:11', '2026-06-25 14:41:11');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `ai_songs`
--
ALTER TABLE `ai_songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `beat_mixes`
--
ALTER TABLE `beat_mixes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `beat_sounds`
--
ALTER TABLE `beat_sounds`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_song_unique` (`user_id`,`song_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Chỉ mục cho bảng `listening_history`
--
ALTER TABLE `listening_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Chỉ mục cho bảng `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `ai_songs`
--
ALTER TABLE `ai_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `beat_mixes`
--
ALTER TABLE `beat_mixes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beat_sounds`
--
ALTER TABLE `beat_sounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `listening_history`
--
ALTER TABLE `listening_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT cho bảng `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `ai_songs`
--
ALTER TABLE `ai_songs`
  ADD CONSTRAINT `ai_songs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `beat_mixes`
--
ALTER TABLE `beat_mixes`
  ADD CONSTRAINT `beat_mixes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
