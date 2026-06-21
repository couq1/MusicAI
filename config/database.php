<?php
// Cấu hình Database kết nối bằng PDO
define('DB_HOST', 'localhost');
define('DB_NAME', 'music_ai');
define('DB_USER', 'root');
define('DB_PASS', '');

$conn = null;
$db_connected = false;

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $db_connected = true;
} catch (PDOException $e) {
    // Không làm sập toàn bộ giao diện, ghi nhận lỗi và cho phép sử dụng dữ liệu mẫu
    $db_connected = false;
    error_log("Database connection error: " . $e->getMessage());
}

// ----------------- DỮ LIỆU MẪU FALLBACK (Dành cho việc chạy thử khi chưa tạo DB) -----------------

$fallback_songs = [
    [
        'id' => 1,
        'title' => 'Lofi Study Sessions',
        'artist' => 'Lofi AI',
        'genre' => 'Lofi',
        'audio_file' => 'assets/audio/sample_lofi.mp3',
        'thumbnail' => 'assets/images/sample_lofi.jpg',
        'plays' => 1250,
        'created_at' => '2026-06-15 08:00:00'
    ],
    [
        'id' => 2,
        'title' => 'Synthwave Neon Rider',
        'artist' => 'Retro Synth AI',
        'genre' => 'EDM',
        'audio_file' => 'assets/audio/sample_edm.mp3',
        'thumbnail' => 'assets/images/sample_edm.jpg',
        'plays' => 980,
        'created_at' => '2026-06-16 14:30:00'
    ],
    [
        'id' => 3,
        'title' => 'Emotional Piano Solo',
        'artist' => 'Classical AI',
        'genre' => 'Piano',
        'audio_file' => 'assets/audio/sample_piano.mp3',
        'thumbnail' => 'assets/images/sample_piano.jpg',
        'plays' => 640,
        'created_at' => '2026-06-17 19:15:00'
    ],
    [
        'id' => 4,
        'title' => 'Hard Trap Anthem',
        'artist' => 'Beats AI',
        'genre' => 'Trap',
        'audio_file' => 'assets/audio/sample_trap.mp3',
        'thumbnail' => 'assets/images/sample_trap.jpg',
        'plays' => 2450,
        'created_at' => '2026-06-18 21:00:00'
    ]
];

$fallback_genres = ['Lofi', 'EDM', 'Trap', 'Chill', 'Piano', 'Ambient', 'Hip-hop'];
$fallback_sounds = [
    // Drums
    ['id' => 1, 'name' => 'Acoustic Kick', 'category' => 'drums', 'audio_file' => 'assets/audio/drums/drum1.wav'],
    ['id' => 2, 'name' => 'HipHop Beat', 'category' => 'drums', 'audio_file' => 'assets/audio/drums/drum2.wav'],
    // Bass
    ['id' => 3, 'name' => 'Deep Sub Bass', 'category' => 'bass', 'audio_file' => 'assets/audio/bass/bass1.wav'],
    ['id' => 4, 'name' => 'Synth Bassline', 'category' => 'bass', 'audio_file' => 'assets/audio/bass/bass2.wav'],
    // Melody
    ['id' => 5, 'name' => 'Dreamy Synth Chords', 'category' => 'melody', 'audio_file' => 'assets/audio/melody/melody1.wav'],
    ['id' => 6, 'name' => 'Chill Lofi Guitar', 'category' => 'melody', 'audio_file' => 'assets/audio/melody/melody2.wav'],
    // Vocals
    ['id' => 7, 'name' => 'Vocal Vocal FX', 'category' => 'vocals', 'audio_file' => 'assets/audio/vocals/vocal1.wav'],
    ['id' => 8, 'name' => 'Atmospheric Vocal', 'category' => 'vocals', 'audio_file' => 'assets/audio/vocals/vocal2.wav'],
    // Effects
    ['id' => 9, 'name' => 'Sci-Fi Laser', 'category' => 'effects', 'audio_file' => 'assets/audio/effects/fx1.wav'],
    ['id' => 10, 'name' => 'Vinyl Crackle', 'category' => 'effects', 'audio_file' => 'assets/audio/effects/fx2.wav'],
];

$fallback_ai_songs = [
    [
        'id' => 1,
        'user_name' => 'Demo User',
        'prompt' => 'A dark trap beat with piano melody',
        'genre' => 'Trap',
        'mood' => 'Dark',
        'duration' => 30,
        'audio_file' => 'storage/generated/mock_ai_song.mp3',
        'created_at' => '2026-06-19 12:00:00'
    ]
];

$fallback_beat_mixes = [
    [
        'id' => 1,
        'user_name' => 'Demo User',
        'name' => 'Lofi Evening Vibe',
        'mix_data' => json_encode([1, 3, 6, 8]), // Danh sách ID các sound trong mix
        'created_at' => '2026-06-19 18:30:00'
    ]
];
