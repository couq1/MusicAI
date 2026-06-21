// Các tính năng JavaScript chung của MusicAI

document.addEventListener('DOMContentLoaded', () => {
    // Menu mobile toggle
    const menuToggle = document.getElementById('menuToggle');
    const navbarLinks = document.getElementById('navbarLinks');
    if (menuToggle && navbarLinks) {
        menuToggle.addEventListener('click', () => {
            navbarLinks.classList.toggle('show');
        });
    }

    // Profile dropdown
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        // Đóng dropdown khi click bên ngoài
        document.addEventListener('click', () => {
            profileDropdown.classList.remove('show');
        });
    }
});

/**
 * Hiển thị Toast thông báo đẹp ở góc màn hình
 * @param {string} message - Nội dung thông báo
 * @param {string} type - 'success' hoặc 'error'
 */
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type === 'error' ? 'toast-error' : ''}`;
    
    const icon = type === 'error' ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-circle-check';
    toast.innerHTML = `
        <i class="${icon}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    // Tự động xóa khỏi DOM sau 4 giây
    setTimeout(() => {
        toast.remove();
        if (container.children.length === 0) {
            container.remove();
        }
    }, 4000);
}

/**
 * Gọi API bằng fetch đồng bộ
 * @param {string} url - Endpoint của API
 * @param {object} options - Options truyền cho fetch
 */
async function callAPI(url, options = {}) {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`Lỗi kết nối HTTP: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        return {
            success: false,
            message: 'Đã xảy ra lỗi kết nối đến máy chủ!'
        };
    }
}

/**
 * Xác nhận hành động xóa dữ liệu
 */
function confirmDelete(message = 'Bạn có chắc chắn muốn thực hiện hành động xóa này?') {
    return confirm(message);
}
