<?php
// Cấu hình kết nối AI Server local (Python FastAPI hoặc Flask)
define('AI_SERVER_URL', 'http://127.0.0.1:8000');
define('AI_GENERATE_ENDPOINT', AI_SERVER_URL . '/generate');
define('AI_TIMEOUT', 60); // Thời gian chờ tạo nhạc tối đa (giây)
