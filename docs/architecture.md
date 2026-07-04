# Tài Liệu Thiết Kế Hệ Thống — MusicAI
*** Thầy có thể sử dụng để xem các sơ đồ hệ thống trên phần mềm mermaid ***

> Tài liệu mô tả kiến trúc tổng thể, sơ đồ thực thể quan hệ (ERD), biểu đồ Use Case, Activity Diagram và luồng hoạt động của hệ thống MusicAI.

---

## 1. Kiến Trúc Hệ Thống (System Architecture)

```
┌─────────────────────────────────────────────────────────────────┐
│                        TRÌNH DUYỆT (Browser)                    │
│           HTML5 / CSS3 / JavaScript (Vanilla)                   │
│     Glassmorphism UI · Neon Green Theme · Web Audio API         │
└────────────────────────┬────────────────────────────────────────┘
                         │  HTTP Request / AJAX (fetch / XMLHttpRequest)
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                   WEB SERVER — Apache (XAMPP)                   │
│                    PHP 8.0 · PDO · Session                      │
│                                                                 │
│  ┌──────────────┐  ┌───────────────┐  ┌──────────────────────┐  │
│  │  Pages (.php)│  │  API (/api/*) │  │  Admin (/admin/*)    │  │
│  │  index.php   │  │  auth/        │  │  dashboard.php       │  │
│  │  music.php   │  │  music/       │  │  songs.php           │  │
│  │  generate.php│  │  ai/          │  │  users.php           │  │
│  │  beatmaker   │  │  beatmaker/   │  │  ai_music.php        │  │
│  │  library.php │  │  user/        │  │  beats.php           │  │
│  └──────────────┘  └───────────────┘  └──────────────────────┘  │
└───────────┬─────────────────────┬───────────────────────────────┘
            │  PDO (MySQL)        │  cURL (HTTP POST JSON)
            ▼                     ▼
┌───────────────────┐   ┌────────────────────────────────────────┐
│  MySQL Database   │   │       Python AI Server (FastAPI)       │
│  music_ai         │   │       ai-server/main.py                │
│                   │   │       http://127.0.0.1:8000            │
│  · users          │   │                                        │
│  · songs          │   │  POST /generate  → Mô phỏng tạo nhạc   │
│  · ai_songs       │   │  GET  /status    → Kiểm tra tiến độ    │
│  · beat_mixes     │   │  GET  /health    → Kiểm tra hoạt động  │
│  · beat_sounds    │   │                                        │
│  · favorites      │   │  [Fallback Mode]: Nếu server AI chưa   │
│  · listening_     │   │  khởi chạy, PHP tự động dùng file      │
│    history        │   │  demo MP3 có sẵn trong storage/demo/   │
└───────────────────┘   └────────────────────────────────────────┘
```

---

## 2. Sơ Đồ Thực Thể Quan Hệ — ERD (Entity Relationship Diagram)

```mermaid
erDiagram
    users {
        int id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        varchar avatar
        timestamp created_at
        timestamp updated_at
    }

    songs {
        int id PK
        varchar title
        varchar artist
        varchar genre
        varchar audio_file
        varchar thumbnail
        int plays
        timestamp created_at
    }

    ai_songs {
        int id PK
        int user_id FK
        text prompt
        varchar genre
        varchar mood
        int duration
        varchar audio_file
        timestamp created_at
    }

    beat_sounds {
        int id PK
        varchar name
        enum category
        varchar audio_file
        enum status
        timestamp created_at
    }

    beat_mixes {
        int id PK
        int user_id FK
        varchar name
        longtext mix_data
        timestamp created_at
    }

    favorites {
        int id PK
        int user_id FK
        int song_id FK
        timestamp created_at
    }

    listening_history {
        int id PK
        int user_id FK
        int song_id FK
        timestamp listened_at
    }

    users ||--o{ ai_songs : "tạo"
    users ||--o{ beat_mixes : "lưu"
    users ||--o{ favorites : "yêu thích"
    users ||--o{ listening_history : "nghe"
    songs ||--o{ favorites : "được yêu thích"
    songs ||--o{ listening_history : "được nghe"
```

---

## 3. Biểu Đồ Use Case

```mermaid
graph TD
    Guest["👤 Khách (Guest)"]
    User["👤 Người Dùng (User)"]
    Admin["👤 Quản Trị Viên (Admin)"]

    subgraph MusicAI_System["🎵 Hệ Thống MusicAI"]
        UC1["Xem trang chủ & danh sách nhạc"]
        UC2["Nghe nhạc (Music Player)"]
        UC3["Tìm kiếm & lọc theo thể loại"]
        UC4["Đăng ký tài khoản"]
        UC5["Đăng nhập"]
        UC6["Tạo nhạc AI bằng Prompt"]
        UC7["Lưu nhạc AI vào thư viện"]
        UC8["Sử dụng Beatmaker Studio"]
        UC9["Lưu bản Beat Mix"]
        UC10["Quản lý danh sách yêu thích"]
        UC11["Xem lịch sử nghe nhạc"]
        UC12["Cập nhật hồ sơ & ảnh đại diện"]
        UC13["Quản lý người dùng (CRUD)"]
        UC14["Upload bài hát mới"]
        UC15["Quản lý nhạc AI & Beat Mix"]
        UC16["Cấu hình hệ thống"]
    end

    Guest --> UC1
    Guest --> UC2
    Guest --> UC3
    Guest --> UC4
    Guest --> UC5

    User --> UC1
    User --> UC2
    User --> UC3
    User --> UC6
    User --> UC7
    User --> UC8
    User --> UC9
    User --> UC10
    User --> UC11
    User --> UC12

    Admin --> UC13
    Admin --> UC14
    Admin --> UC15
    Admin --> UC16
    Admin --> UC1
    Admin --> UC2
```

---

## 4. Activity Diagram — Luồng Tạo Nhạc AI

```mermaid
flowchart TD
    A([Bắt đầu]) --> B{Đã đăng nhập?}
    B -- Chưa --> C[Chuyển hướng đến trang Đăng nhập]
    C --> D([Kết thúc])
    B -- Rồi --> E[Hiển thị trang generate.php]
    E --> F[Người dùng nhập Prompt, Genre, Mood, Duration]
    F --> G[Nhấn nút 'Tạo Nhạc AI']
    G --> H[POST → api/ai/generate_music.php]
    H --> I{Python AI Server\nđang chạy?}
    I -- Có --> J[Gửi cURL request tới\nhttp://127.0.0.1:8000/generate]
    J --> K[Nhận task_id từ AI Server]
    I -- Không --> L[Chọn file demo MP3 ngẫu nhiên\ntheo genre từ storage/demo/]
    L --> M[Tạo mock_task_id lưu vào Session]
    K --> N[JS polling → api/ai/check_status.php]
    M --> N
    N --> O{Task đã\nhoàn thành?}
    O -- Chưa --> P[Hiển thị loading + đếm ngược]
    P --> N
    O -- Xong --> Q[Hiển thị Audio Player để nghe thử]
    Q --> R{Người dùng\nmuốn lưu?}
    R -- Không --> S([Kết thúc / Tạo lại])
    R -- Có --> T[POST → api/ai/save_ai_song.php]
    T --> U{DB kết nối\nđược?}
    U -- Có --> V[INSERT INTO ai_songs]
    U -- Không --> W[Lưu vào Session demo_ai_songs]
    V --> X[Thông báo lưu thành công]
    W --> X
    X --> Y([Kết thúc])
```

---

## 5. Activity Diagram — Luồng Nghe Nhạc & Tương Tác

```mermaid
flowchart TD
    A([Bắt đầu]) --> B[Người dùng truy cập music.php]
    B --> C[Tải danh sách bài hát từ DB / Fallback]
    C --> D[Người dùng chọn bài hát]
    D --> E[Music Player khởi động ở thanh dưới]
    E --> F[Ghi lịch sử nghe → listening_history]
    F --> G[Tăng lượt plays của bài hát]
    G --> H{Người dùng\nthao tác?}
    H -- Yêu thích --> I[POST → api/music/favorite.php]
    I --> J[Toggle INSERT / DELETE favorites]
    H -- Xem lịch sử --> K[Truy cập history.php]
    H -- Tiếp tục nghe --> H
    H -- Dừng --> L([Kết thúc])
```

---

## 6. Activity Diagram — Luồng Beatmaker Studio

```mermaid
flowchart TD
    A([Bắt đầu]) --> B[Truy cập beatmaker.php]
    B --> C[Tải danh sách beat_sounds từ DB / Fallback]
    C --> D[Hiển thị 5 kênh: Drums, Bass, Melody, Vocals, Effects]
    D --> E[Người dùng kéo Sound Loop vào slot nhân vật]
    E --> F[Web Audio API phát âm thanh theo vòng lặp]
    F --> G{Thêm / xóa\nloop khác?}
    G -- Có --> E
    G -- Không --> H{Muốn lưu\nbản Mix?}
    H -- Không --> I([Kết thúc])
    H -- Có --> J{Đã đăng nhập?}
    J -- Chưa --> K[Yêu cầu đăng nhập]
    K --> I
    J -- Rồi --> L[Nhập tên bản Mix]
    L --> M[POST → api/beatmaker/save_mix.php]
    M --> N[INSERT INTO beat_mixes với JSON mix_data]
    N --> O[Thông báo lưu thành công]
    O --> I([Kết thúc])
```