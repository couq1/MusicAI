import uuid
import time
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

app = FastAPI(
    title="MusicAI Mock Local AI Server",
    description="Giả lập máy chủ xử lý tác vụ sinh nhạc AI sử dụng FastAPI.",
    version="1.0"
)

# Cấu hình CORS cho phép PHP Frontend gọi chéo nguồn trực tiếp
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Lưu trữ các tiến trình tạo nhạc trong RAM
tasks = {}

class GenerationRequest(BaseModel):
    prompt: str
    genre: str
    mood: str
    duration: int = 30

@app.get("/")
def read_root():
    return {
        "status": "online",
        "message": "MusicAI Mock Local AI Server is running!",
        "endpoints": {
            "POST /generate": "Bắt đầu sinh nhạc",
            "GET /status/{task_id}": "Kiểm tra tiến độ tác vụ"
        }
    }

@app.post("/generate")
def generate_music(req: GenerationRequest):
    """
    Nhận yêu cầu tạo nhạc AI từ PHP và bắt đầu xử lý giả lập.
    """
    task_id = f"task_ai_{uuid.uuid4().hex[:12]}"
    
    # Định vị các file sound loop có sẵn tương ứng
    mock_loops = {
        "lofi": "storage/demo/lofi_loop.mp3",
        "edm": "storage/demo/edm_loop.mp3",
        "trap": "storage/demo/trap_loop.mp3",
        "chill": "storage/demo/chill_loop.mp3",
        "piano": "storage/demo/piano_loop.mp3",
        "ambient": "storage/demo/ambient_loop.mp3",
        "hiphop": "storage/demo/hiphop_loop.mp3"
    } 
    
    genre_key = req.genre.lower()
    audio_file = mock_loops.get(genre_key, "storage/samples/lofi_loop.mp3")
    
    # Khởi tạo trạng thái đang xử lý (processing) với thời gian chờ giả lập 3s
    tasks[task_id] = {
        "task_id": task_id,
        "status": "processing",
        "prompt": req.prompt,
        "genre": req.genre,
        "mood": req.mood,
        "duration": req.duration,
        "audio_file": audio_file,
        "created_at": time.time(),
        "eta": 3.0  # Giả lập xử lý trong 3 giây
    }
    
    return {
        "success": True,
        "task_id": task_id,
        "message": "Đã thêm yêu cầu sinh nhạc vào hàng đợi AI!"
    }

@app.get("/status/{task_id}")
def check_status(task_id: str):
    """
    JS Client Polling kiểm tra tiến độ tạo nhạc AI.
    """
    if task_id not in tasks:
        raise HTTPException(status_code=404, detail="Không tìm thấy mã tiến trình sinh nhạc này!")
        
    task = tasks[task_id]
    
    # Kiểm tra thời gian trôi qua để chuyển đổi trạng thái sang hoàn thành (completed)
    elapsed = time.time() - task["created_at"]
    if task["status"] == "processing" and elapsed >= task["eta"]:
        task["status"] = "completed"
        
    return {
        "task_id": task["task_id"],
        "status": task["status"],
        "prompt": task["prompt"],
        "genre": task["genre"],
        "mood": task["mood"],
        "duration": task["duration"],
        "audio_file": task["audio_file"]
    }

if __name__ == "__main__":
    import uvicorn
    print("Khởi chạy Mock AI Server tại http://127.0.0.1:8000")
    uvicorn.run("main:app", host="127.0.0.1", port=8000, reload=True)
