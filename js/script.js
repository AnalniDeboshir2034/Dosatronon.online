
document.addEventListener('DOMContentLoaded', function() {

    if (document.querySelector('.hero-swiper')) {
        const heroSwiper = new Swiper('.hero-swiper', {
            loop: true,
            speed: 600,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
        });
        

    }
    
    
    if (document.querySelector('.reviews-swiper')) {
        const reviewsSwiper = new Swiper('.reviews-swiper', {
            loop: true,
            speed: 400,
            slidesPerView: 1,
            spaceBetween: 20,
            autoplay: {
                delay: 4000,
            },
            pagination: {
                el: '.reviews-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.reviews-next',
                prevEl: '.reviews-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                },
                992: {
                    slidesPerView: 3,
                }
            }
        });
        

    }
});