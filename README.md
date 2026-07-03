# MusicAI - Website Nghe Nhạc, Tạo Nhạc AI & Beatmaker Studio 
### http://localhost/MusicAI/index.php

MusicAI là một nền tảng website nghe nhạc hiện đại kết hợp trí tuệ nhân tạo (AI) để sinh nhạc tự động theo prompt và phòng thu Beatmaker Studio độc đáo (theo phong cách chơi nhạc của Incredibox). Dự án được phát triển hoàn toàn bằng **PHP thuần (PDO)** kết hợp **HTML5/CSS3/JS thuần** (Glassmorphism & Neon Green theme) và một **Python AI Server (FastAPI)** giả lập.

---

## 1. Cấu Trúc Thư Mục Dự Án

```text
MusicAI/
├── admin/                  # Khu vực quản trị Admin
│   ├── admin_header.php    # Giao diện header & sidebar chung của Admin
│   ├── admin_footer.php    # Giao diện footer chung của Admin
│   ├── dashboard.php       # Dashboard thống kê số liệu
│   ├── users.php           # Quản lý thành viên (CRUD)
│   ├── songs.php           # Quản lý bài hát gốc hệ thống (Upload file cứng)
│   ├── ai_music.php        # Quản lý nhạc do AI của user tạo ra
│   ├── beats.php           # Quản lý beat sound loop của Beatmaker
│   └── settings.php        # Cấu hình website toàn cục
├── ai-server/              # Máy chủ AI mô phỏng (FastAPI)
│   └── main.py             # File khởi chạy FastAPI mock
├── api/                    # Hệ thống API backend trả về JSON
│   ├── auth/               # API Xác thực (login.php, register.php)
│   ├── music/              # API Lịch sử & Yêu thích (favorite.php, history.php)
│   ├── ai/                 # API Tạo & Lưu nhạc AI (generate_music.php, check_status.php, save_ai_song.php)
│   └── beatmaker/          # API Lưu bản Mix (save_mix.php)
├── assets/                 # Các tài nguyên tĩnh (CSS, JS, Images, Fonts)
│   ├── css/                # style.css, player.css, beatmaker.css, generator.css...
│   ├── js/                 # main.js, player.js, beatmaker.js, ai-generator.js, dragdrop.js...
│   └── images/             # default-avatar.png, default_song.jpg..., Ảnh bìa bài hát
├── config/                 # Các tệp cấu hình lõi hệ thống
│   ├── config.php          # Cấu hình đường dẫn, hằng số chung
│   ├── database.php        # Cấu hình kết nối MySQL PDO (có Fallback Demo Mode)
│   ├── ai_server.php       # Cấu hình kết nối AI Server
│   └── settings.json       # File cấu hình động từ trang Admin
├── database/               # Cơ sở dữ liệu SQL
│   ├── music_ai.sql        # File cấu trúc bảng
│   └── sample_data.sql     # File dữ liệu mẫu khởi tạo
├── docs/                   # Tài liệu sơ đồ thiết kế
│   └── architecture.md     # Sơ đồ ERD, Use Case, Activity, Architecture
├── includes/               # Giao diện dùng chung cho trang Client
│   ├── header.php          # Header & Head meta
│   ├── footer.php          # Footer chung
│   ├── navbar.php          # Thanh menu điều hướng
│   ├── auth.php            # Xử lý phân quyền Session
│   └── music_player.php    # Trình phát nhạc cố định dưới màn hình
├── storage/                # Thư mục lưu trữ tệp tải lên (upload)
│   ├── audio/              # Nhạc gốc tải lên
│   ├── beats/              # Loops beatmaker
│   ├── demo/               # Nhạc demo cho phần AI
│   ├── samples/            # Các sound loop cho phần music
├── index.php               # Trang chủ giới thiệu bài hát
├── music.php               # Trang nghe nhạc & tìm kiếm, lọc thể loại
├── generate.php            # Trang tạo nhạc AI bằng prompt
├── beatmaker.php           # Trang Mixer Beatmaker Studio
├── library.php             # Thư viện cá nhân (Nhạc AI đã tạo & Beat Mix đã lưu)
├── favorites.php           # Danh sách nhạc yêu thích
├── history.php             # Lịch sử nghe nhạc gần đây
├── login.php               # Giao diện Đăng nhập
├── register.php            # Giao diện Đăng ký
└── logout.php              # Xử lý Đăng xuất
```

---

## 2. Hướng Dẫn Khởi Chạy Dự Án

### Bước 1: Thiết lập XAMPP và Web Server
1. Copy thư mục **MusicAI** vào thư mục chạy web của XAMPP (thông thường là `C:\xampp\htdocs\`).
2. Mở **XAMPP Control Panel** và khởi động dịch vụ **Apache** và **MySQL**.

### Bước 2: Cấu hình Cơ sở dữ liệu MySQL
1. Truy cập trang quản trị phpMyAdmin tại địa chỉ `http://localhost/phpmyadmin/`.
2. Tạo mới một cơ sở dữ liệu có tên là `music_ai`.
3. Chọn cơ sở dữ liệu `music_ai` vừa tạo, nhấp chọn tab **Import** (Nhập), tiến hành chọn và chạy lần lượt các file:
   * `database/music_ai.sql` (Cấu trúc bảng)
   * `database/sample_data.sql` (Dữ liệu mẫu thành viên và bài hát)
4. Mở file `config/database.php` và điều chỉnh lại cấu hình tài khoản kết nối MySQL (username, password) nếu có thay đổi trong XAMPP của bạn.

> [!NOTE]
> **Chế độ Dự Phòng (Demo Mode)**: Nếu bạn chưa import cơ sở dữ liệu, website vẫn hoạt động bình thường nhờ cơ chế tự động Fallback nạp toàn bộ danh sách bài hát tĩnh, đồng thời lưu trữ các bài hát AI tự tạo và bản Beat Mix vào session để thuận tiện chạy thử nghiệm nhanh.

### Bước 3: Đăng nhập Tài khoản Thử nghiệm
Hệ thống đã có sẵn 2 tài khoản thử nghiệm với mật khẩu tương ứng:
* **Tài khoản Admin**:
  * Email: `admin@musicai.local`
  * Mật khẩu: `admin123`
* **Tài khoản User**:
  * Email: `user@musicai.local`
  * Mật khẩu: `user123`

---

## 3. Hướng Dẫn Chạy Máy Chủ AI (Python FastAPI)

Phần tính năng Tạo nhạc AI trong `generate.php` kết nối trực tiếp tới một REST API server chạy bằng Python. Để khởi chạy mock AI server này:

1. Đảm bảo máy tính của bạn đã cài đặt Python 3.
2. Mở Terminal (Command Prompt / Powershell) tại thư mục dự án và cài đặt các thư viện cần thiết:
   ```bash
   pip install fastapi uvicorn pydantic
   ```
3. Khởi chạy máy chủ AI bằng lệnh:
   ```bash
   python ai-server/main.py
   ```
4. Máy chủ sẽ chạy tại địa chỉ `http://127.0.0.1:8000`. Khi bạn thao tác tạo nhạc AI tại trang `generate.php`, PHP API sẽ gửi cURL request tới Python server, mô phỏng sinh nhạc trong 3 giây và trả về tệp âm thanh tương ứng với thể loại bạn yêu cầu.

---

## 4. Các Chức Năng Chính Trên Website

1. **Nghe nhạc**: Tìm kiếm bài hát theo tên, lọc bài hát theo thể loại (Lofi, EDM, rap, Chill). Trình phát nhạc cố định hỗ trợ Play, Pause, Next, Previous, Volume, Progress Bar, Yêu thích và Tự động lưu Lịch sử.
2. **Tạo nhạc AI**: Nhập mô tả (Prompt), chọn thể loại, tâm trạng và thời lượng. Giao diện hiển thị hiệu ứng Loading đếm ngược và cho phép nghe thử trước khi nhấn lưu vào thư viện cá nhân.
3. **Beatmaker Studio**: Trình Mixer kéo thả 5 kênh. Kéo các Sound loop mẫu (Drums, Bass, Melody, Vocals, Effects) thả vào các slot nhân vật để kích hoạt âm thanh phối hợp. Bạn có thể Lưu bản Mix và Nạp lại bản Mix từ thư viện.
4. **Trang Admin**: Giao diện sidebar tối giản thống kê số liệu tổng quan bài hát, người dùng, quản lý CRUD thành viên, upload nhạc gốc (.mp3 và cover ảnh bìa), duyệt danh sách nhạc AI tự tạo và điều chỉnh cấu hình toàn trang.
