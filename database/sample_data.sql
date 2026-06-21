-- ==========================================
-- DỮ LIỆU MẪU BAN ĐẦU CHO HỆ THỐNG MUSICAI
-- Phục vụ chạy thử nghiệm trên XAMPP
-- ==========================================

USE `music_ai`;

-- 1. Chèn thành viên mẫu (Mật khẩu trùng khớp với password_verify)
-- Tài khoản admin: admin@musicai.local / Mật khẩu: admin123
-- Tài khoản user: user@musicai.local / Mật khẩu: user123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`) VALUES
(1, 'Quản Trị Viên', 'admin@musicai.local', '$2y$10$BCuIZM3n0DbXHITbPcYKOu0xLglBcClfv5lUweFBc2Fr4mXAVvk6O', 'admin', 'assets/images/avatar.jpg'),
(2, 'Thành Viên Thử Nghiệm', 'user@musicai.local', '$2y$10$g75U0sjf0gkvGGOjW2StGu4Dj/KNsNWtndfaGA3HZVJdlx5pfsL06', 'user', 'assets/images/avatar.jpg');

-- 2. Chèn các bài hát mặc định hệ thống
INSERT INTO `songs` (`id`, `title`, `artist`, `genre`, `audio_file`, `thumbnail`, `plays`) VALUES
(1, 'Lofi Dream', 'Sunset Beats', 'Lofi', 'storage/samples/lofi_loop.mp3', 'assets/images/dream.jpg', 1250),
(2, 'EDM Energy', 'Cyber DJ', 'EDM', 'storage/samples/edm_loop.mp3', 'assets/images/edm.jpg', 980),
(3, 'Acoustic Sun', 'Nature Sound', 'Chill', 'storage/samples/chill_loop.mp3', 'assets/images/aucotic.jpg', 640),
(4, 'Hard Trap Anthem', 'Bass Producer', 'Trap', 'storage/samples/trap_loop.mp3', 'assets/images/trap.jpg', 36),
(5, 'lofi_chill', 'Q1','lofi','storage/samples/lofi_chill.mp3', 'assets/images/chill.jpg', 2403),
(6, 'Piano', 'Alex','Piano','storage/samples/piano_loop.mp3', 'assets/images/piano.jpg', 18),
(7, 'ambient_music', 'Antony','ambient','storage/samples/ambient_loop.mp3', 'assets/images/ambient.jpg', 30),
(8, 'hiphop_dance', 'Vicky','hiphop_dance','storage/samples/hiphop_loop.mp3', 'assets/images/hiphop.jpg', 968);   
 

-- 3. Chèn các beat sound mẫu cho Beatmaker Studio
INSERT INTO `beat_sounds` (`id`, `name`, `category`, `audio_file`, `status`) VALUES
-- Drums (Trống)
(1, 'Acoustic Drums', 'drums', 'storage/beats/drums1.mp3', 'active'),
(2, 'Cyber Drums', 'drums', 'storage/beats/drums2.mp3', 'active'),
(3, 'Trap Drums', 'drums', 'storage/beats/drums3.mp3', 'active'),
(4, 'HipHop Drums', 'drums', 'storage/beats/drums4.mp3', 'active'),

-- Bass (Âm trầm)
(5, 'Drum Bassline', 'bass', 'storage/beats/bass1.mp3', 'active'),
(6, 'Synth Sub Bass', 'bass', 'storage/beats/bass2.mp3', 'active'),
(7, 'Deep Bassline', 'bass', 'storage/beats/bass3.mp3', 'active'),
(8, 'RO Bass', 'bass', 'storage/beats/bass4.mp3', 'active'),

-- Melody (Giai điệu)
(9, 'Synth Leads', 'melody', 'storage/beats/melody1.mp3', 'active'),
(10, 'Lofi Guitar', 'melody', 'storage/beats/melody2.mp3', 'active'),
(11, 'Synth Edm', 'melody', 'storage/beats/melody3.mp3', 'active'),
(12, ' Bell Pick', 'melody', 'storage/beats/melody4.mp3', 'active'),

-- Vocals (Giọng ca)
(13, 'Choir Pad Vocal', 'vocals', 'storage/beats/vocals1.mp3', 'active'),
(14, 'Melodic Chant', 'vocals', 'storage/beats/vocals2.mp3', 'active'),
(15, 'Chrismas_Vocal', 'vocals', 'storage/beats/vocals3.mp3', 'active'),
(16, 'Pod', 'vocals', 'storage/beats/vocals4.mp3', 'active'),

-- Effects (Hiệu ứng)
(17, 'Shadow', 'effects', 'storage/beats/effects1.mp3', 'active'),
(18, 'CL', 'effects', 'storage/beats/effects2.mp3', 'active'),
(19, 'Space FX Riser', 'effects', 'storage/beats/effects3.mp3', 'active'),
(20, 'Vinyl Crackles', 'effects', 'storage/beats/effects4.mp3', 'active');



