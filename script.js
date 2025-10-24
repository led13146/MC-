// script.js - 网站功能脚本
document.addEventListener('DOMContentLoaded', function() {
    // 初始化背景图片轮换
    initBackgroundImages();
    
    // 初始化公告弹窗
    initAnnouncementModal();
});

// 初始化背景图片轮换
function initBackgroundImages() {
    const backgroundContainer = document.getElementById('background-container');
    const backgroundImages = document.querySelectorAll('.background-image');
    
    if (!backgroundContainer || backgroundImages.length === 0) return;
    
    // 预加载背景图片
    function preloadBackgroundImages() {
        // 使用随机图片API
        backgroundImages.forEach((img, index) => {
            // 使用要求的随机图片API，不带任何参数
            const imageUrl = `https://api.r10086.com/樱道随机图片api接口.php?图片系列=我的世界系列1`;
            
            const image = new Image();
            image.src = imageUrl;
            image.onload = function() {
                img.style.backgroundImage = `url(${imageUrl})`;
                
                // 如果是第一张图片，设置为活动状态
                if (index === 0) {
                    img.classList.add('active');
                }
            };
            image.onerror = function() {
                // 如果图片加载失败，使用CSS渐变背景
                img.style.background = getRandomGradient();
            };
        });
    }
    
    // 获取随机渐变背景
    function getRandomGradient() {
        const gradients = [
            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
        ];
        return gradients[Math.floor(Math.random() * gradients.length)];
    }
    
    // 切换背景图片
    function rotateBackgroundImages() {
        const activeImage = document.querySelector('.background-image.active');
        if (!activeImage) return;
        
        let nextImage = activeImage.nextElementSibling;
        
        if (!nextImage) {
            nextImage = backgroundImages[0];
        }
        
        // 为下一张图片重新加载随机图片
        const newImageUrl = `https://api.r10086.com/樱道随机图片api接口.php?图片系列=我的世界系列1`;
        const tempImage = new Image();
        tempImage.src = newImageUrl;
        tempImage.onload = function() {
            nextImage.style.backgroundImage = `url(${newImageUrl})`;
        };
        tempImage.onerror = function() {
            nextImage.style.background = getRandomGradient();
        };
        
        activeImage.classList.remove('active');
        nextImage.classList.add('active');
    }
    
    // 初始化
    preloadBackgroundImages();
    
    // 每10秒切换一次背景图片
    setInterval(rotateBackgroundImages, 10000);
}

// 初始化公告弹窗
function initAnnouncementModal() {
    const announcementModal = document.getElementById('announcementModal');
    const closeModalBtn = document.querySelector('#announcementModal .btn-primary');
    
    if (announcementModal && closeModalBtn) {
        // 立即关闭弹窗的函数
        function closeAnnouncementModal() {
            announcementModal.style.display = 'none';
        }
        
        closeModalBtn.addEventListener('click', closeAnnouncementModal);
        
        announcementModal.addEventListener('click', function(e) {
            if (e.target === announcementModal) {
                closeAnnouncementModal();
            }
        });
        
        // 添加键盘支持：按ESC键关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && announcementModal.style.display !== 'none') {
                closeAnnouncementModal();
            }
        });
    }
}