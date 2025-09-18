// แก้ไขโค้ด JS เดิมของคุณดังนี้
$(document).ready(function() {
    const items = $('.carousel-item');
    const totalItems = items.length;
    let currentIndex = 0;

    function updateCarousel() {
        items.each(function(index) {
            $(this).removeClass('active prev-item next-item');
            
            // หา index ของรูปก่อนหน้าและรูปถัดไปในรูปแบบวงกลม
            const prevIndex = (currentIndex - 1 + totalItems) % totalItems;
            const nextIndex = (currentIndex + 1) % totalItems;

            if (totalItems <= 1) {
                // ถ้ามีแค่รูปเดียวให้แสดงรูปนั้นเป็น active
                if (index === 0) {
                    $(this).addClass('active');
                }
                return;
            }

            // กำหนด class ตามตำแหน่งปัจจุบัน
            if (index === currentIndex) {
                $(this).addClass('active');
            } else if (index === prevIndex) {
                $(this).addClass('prev-item');
            } else if (index === nextIndex) {
                $(this).addClass('next-item');
            }
        });
    }

    $('.carousel-nav.next').on('click', function() {
        currentIndex = (currentIndex + 1) % totalItems;
        updateCarousel();
    });

    $('.carousel-nav.prev').on('click', function() {
        currentIndex = (currentIndex - 1 + totalItems) % totalItems;
        updateCarousel();
    });

    // ส่วนของ Swipe Gesture (เพิ่มใหม่)
    let touchStartX = 0;
    const carouselContainer = $('.profile-image-carousel')[0]; // ใช้ container ที่ครอบทั้งหมด

    if (carouselContainer) {
        carouselContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
        });

        carouselContainer.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const minSwipeDistance = 50; 

            if (touchEndX < touchStartX - minSwipeDistance) {
                // Swipe Left
                currentIndex = (currentIndex + 1) % totalItems;
                updateCarousel();
            } else if (touchEndX > touchStartX + minSwipeDistance) {
                // Swipe Right
                currentIndex = (currentIndex - 1 + totalItems) % totalItems;
                updateCarousel();
            }
        });
    }

    // Initial load
    if (totalItems > 0) {
        updateCarousel();
    }
});

/* เพิ่มโค้ดส่วนนี้ต่อท้ายโค้ด JS เดิม */

let touchStartX = 0;
let touchEndX = 0;
const carouselContainer = $('.carousel-container')[0];

if (carouselContainer) {
    carouselContainer.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
    });

    carouselContainer.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].clientX;
        handleSwipe();
    });

    function handleSwipe() {
        const minSwipeDistance = 50; // กำหนดระยะเลื่อนขั้นต่ำเป็น 50px

        if (touchEndX < touchStartX - minSwipeDistance) {
            // เลื่อนไปทางซ้าย (ปัดจากขวาไปซ้าย)
            currentIndex = (currentIndex + 1) % totalItems;
            updateCarousel();
        } else if (touchEndX > touchStartX + minSwipeDistance) {
            // เลื่อนไปทางขวา (ปัดจากซ้ายไปขวา)
            currentIndex = (currentIndex - 1 + totalItems) % totalItems;
            updateCarousel();
        }
    }
}