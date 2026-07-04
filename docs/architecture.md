# Tài Liệu Thiết Kế Hệ Thống MusicAI

Tài liệu này mô tả các sơ đồ luồng hoạt động, cấu trúc cơ sở dữ liệu và kiến trúc tổng quan của dự án MusicAI.

---

## 1. Sơ đồ Thực thể Liên kết (Entity-Relationship Diagram - ERD)

Sơ đồ ERD mô tả mối quan hệ giữa các bảng trong cơ sở dữ liệu MySQL:

```mermaid
erDiagram
    USERS ||--o{ AI_SONGS : "creates"
    USERS ||--o{ BEAT_MIXES : "saves"
    USERS ||--o{ FAVORITES : "likes"
    USERS ||--o{ LISTENING_HISTORY : "listens"
    SONGS ||--o{ FAVORITES : "favorited"
    SONGS ||--o{ LISTENING_HISTORY : "recorded"
    
    USERS {
        int id PK
        string name
        string email UK
        string password
        enum role
        string avatar
        timestamp created_at
    }
    SONGS {
        int id PK
        string title
        string artist
        string genre
        string audio_file
        string thumbnail
        int plays
        timestamp created_at
    }
    AI_SONGS {
        int id PK
        int user_id FK
        text prompt
        string genre
        string mood
        int duration
        string audio_file
        timestamp created_at
    }
    BEAT_SOUNDS {
        int id PK
        string name
        enum category
        string audio_file
        enum status
        timestamp created_at
    }
    BEAT_MIXES {
        int id PK
        int user_id FK
        string name
        json mix_data
        timestamp created_at
    }
    FAVORITES {
        int id PK
        int user_id FK
        int song_id FK
        timestamp created_at
    }
    LISTENING_HISTORY {
        int id PK
        int user_id FK
        int song_id FK
        timestamp listened_at
    }
```

---

## 2. Sơ đồ Ca sử dụng (Use Case Diagram)

Phân chia các quyền hạn chức năng giữa **Người dùng thông thường** và **Quản trị viên (Admin)**:

```mermaid
graph TD
    User([Người dùng]) --> UC_Listen([Nghe nhạc & Tìm kiếm])
    User --> UC_Favorite([Yêu thích bài hát])
    User --> UC_Generate([Tạo nhạc AI])
    User --> UC_Beatmaker([Chơi nhạc Beatmaker])
    User --> UC_SaveMix([Lưu bản Beat Mix])
    
    Admin([Quản trị viên]) --> UC_Dashboard([Xem Dashboard Thống kê])
    Admin --> UC_ManageUsers([Quản lý Thành viên CRUD])
    Admin --> UC_ManageSongs([Quản lý Bài hát CRUD])
    Admin --> UC_ManageAISongs([Quản lý Nhạc AI của User])
    Admin --> UC_ManageBeats([Quản lý Beat Loops])
    Admin --> UC_Settings([Cấu hình Website])
    
    Admin --> User
```

---

## 3. Sơ đồ Luồng hoạt động Tạo nhạc AI (Activity Diagram)

Mô tả luồng xử lý từ khi người dùng gửi mô tả prompt cho đến khi nhận được tệp nhạc AI từ server (có cơ chế dự phòng tự động khi AI Server offline):

```mermaid
stateDiagram-v2
    [*] --> RequestForm: User nhập prompt, thể loại, thời lượng
    RequestForm --> SendRequest: Nhấp nút "Tạo nhạc AI"
    SendRequest --> CallServer: PHP API chuyển tiếp request tới Python AI Server
    CallServer --> CheckServerStatus: Python Server hoạt động?
    
    state CheckServerStatus <<choice>>
    CheckServerStatus --> ActiveServer: Hoạt động (Phản hồi Task ID)
    CheckServerStatus --> InactiveServer: Ngoại tuyến (Tự sinh Mock Task ID)
    
    ActiveServer --> PollingStatus: Client JS gửi yêu cầu kiểm tra mỗi 1 giây
    InactiveServer --> SimulationCompleted: Tự động hoàn thành ngay (Mock Loop)
    
    PollingStatus --> IsCompleted: Trạng thái trả về "completed"?
    
    state IsCompleted <<choice>>
    IsCompleted --> PollingStatus: Chưa (Đang xử lý)
    IsCompleted --> SimulationCompleted: Rồi
    
    SimulationCompleted --> DisplayPlayer: Hiện trình phát nghe thử kết quả
    DisplayPlayer --> SaveToLibrary: Nhấp nút "Lưu vào Thư viện"
    SaveToLibrary --> Saved: Lưu vào DB / Session Thư viện
    Saved --> [*]
```

---

## 4. Sơ đồ Kiến trúc Hệ thống (Architecture Diagram)

Mô tả kiến trúc **3 tầng** của hệ thống: Tầng Giao diện (Client), Tầng Xử lý (Server), và Tầng Dữ liệu & AI. Các thành phần giao tiếp qua REST API trả JSON, PDO cho Database, và cURL cho AI Server.

```mermaid
graph TD
    subgraph TIER1["🖥️ TẦNG 1: CLIENT (Frontend)"]
        Browser["<b>Trình duyệt Web</b><br/>HTML5 / CSS3 / JavaScript<br/>(Glassmorphism UI)"]
    end

    subgraph TIER2["⚙️ TẦNG 2: SERVER (Backend)"]
        PHP["<b>Apache Server</b><br/>PHP Backend<br/>(PDO · cURL · Session)"]
        AIServer["<b>AI Server</b><br/>Python FastAPI<br/>:8000"]
    end

    subgraph TIER3["🗄️ TẦNG 3: DỮ LIỆU"]
        MySQL[("<b>MySQL Database</b><br/>7 bảng dữ liệu")]
        Storage["<b>File Storage</b><br/>/storage/<br/>(audio · beats · demo)"]
    end

    Browser -- "AJAX / Fetch API → JSON" --> PHP
    PHP -- "HTML Response" --> Browser

    PHP -- "PDO Connection" --> MySQL
    PHP -- "R/W Files" --> Storage

    PHP -- "cURL POST Request" --> AIServer
    AIServer -- "JSON (task_id · status · file)" --> PHP

    AIServer -- "Đọc file demo" --> Storage
```

### Luồng xử lý Request tổng quát

```mermaid
flowchart LR
    A([👤 User Request]) --> B[PHP Backend]
    B --> C{Loại xử lý}
    C -- "Truy vấn dữ liệu" --> D[(MySQL DB)]
    C -- "Tạo nhạc AI" --> E[Python FastAPI]
    D --> F[JSON Response]
    E --> F
    F --> G([✅ Client nhận kết quả])
```
