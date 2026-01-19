document.addEventListener('DOMContentLoaded', function() {

    
    // 1. ИНИЦИАЛИЗАЦИЯ СЛАЙДЕРОВ
    

    if (document.querySelector('.products-swiper')) {
        console.log('Проверяем устройства...');
        console.log('Ширина экрана:', window.innerWidth, 'px');
        
        // Проверяем мобильное устройство
        if (window.innerWidth <= 991) {
            console.log('✅ Мобильное устройство, инициализируем свайпер товаров');
            
            const productsSwiper = new Swiper('.products-swiper', {
                loop: true,                    // Бесконечная прокрутка
                speed: 400,                    // Скорость анимации
                slidesPerView: 1,              // 1 слайд на экране
                spaceBetween: 15,              // Отступ между слайдами
                centeredSlides: true,          // Центрируем активный слайд
                grabCursor: true,              // Курсор "рука" при наведении
                allowTouchMove: true,          // Разрешаем свайп пальцем
                
                // Навигация стрелками
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                    disabledClass: 'swiper-button-disabled'
                },
                
                // События для отладки
                on: {
                    init: function() {
                        console.log('✅ Свайпер товаров инициализирован!');
                        console.log('Всего слайдов: ' + this.slides.length);
                        console.log('Зацикливание: ' + this.params.loop);
                        
                        // Принудительно показываем кнопки
                        const nextBtn = this.navigation.nextEl;
                        const prevBtn = this.navigation.prevEl;
                        if (nextBtn) {
                            nextBtn.style.display = 'flex';
                            nextBtn.style.opacity = '1';
                            console.log('Кнопка Next:', nextBtn);
                        }
                        if (prevBtn) {
                            prevBtn.style.display = 'flex';
                            prevBtn.style.opacity = '1';
                            console.log('Кнопка Prev:', prevBtn);
                        }
                    },
                    
                    slideChange: function() {
                        console.log('Текущий слайд: ' + (this.realIndex + 1) + ' из ' + this.slides.length);
                    }
                }
            });
            
            // Тестируем клики на кнопках
            const nextBtn = document.querySelector('.products-swiper .swiper-button-next');
            const prevBtn = document.querySelector('.products-swiper .swiper-button-prev');
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Тестируем свайп
            const swiperEl = document.querySelector('.products-swiper');
            if (swiperEl) {
                swiperEl.addEventListener('touchstart', function() {
                });
                
                swiperEl.addEventListener('touchend', function() {
                });
                
                // Для десктопа (если тестируешь мышкой)
                swiperEl.addEventListener('mousedown', function() {
                    console.log('Mouse down (начало свайпа мышкой)');
                });
                
                swiperEl.addEventListener('mouseup', function() {
                    console.log('Mouse up (конец свайпа мышкой)');
                });
            }
        } else {

        }
    }
    
    // Слайдер отзывов (работает всегда)
    if (document.querySelector('.reviews-swiper')) {
        console.log('Инициализируем слайдер отзывов...');
        
        new Swiper('.reviews-swiper', {
            loop: true,
            speed: 600,
            autoplay: { 
                delay: 5000,
                disableOnInteraction: false 
            },
            slidesPerView: 1,
            spaceBetween: 20,
            grabCursor: true,
            breakpoints: {
                768: { 
                    slidesPerView: 2,
                    spaceBetween: 20 
                },
                992: { 
                    slidesPerView: 3,
                    spaceBetween: 20 
                }
            },
            on: {
                init: function() {
                    console.log('✅ Слайдер отзывов инициализирован');
                }
            }
        });
    }
    
    // 2. ФУНКЦИОНАЛ СРАВНЕНИЯ ТОВАРОВ
    
    const compareButtons = document.querySelectorAll('.btn-secondary[data-product-id]');
    compareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            
            let compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
            const exists = compareItems.some(item => item.id == productId);
            
            if (!exists) {
                compareItems.push({ 
                    id: productId, 
                    name: productName, 
                    date: new Date().toISOString() 
                });
                localStorage.setItem('compareItems', JSON.stringify(compareItems));
                this.textContent = '✓ В сравнении';
                this.classList.add('btn-success');
                this.classList.remove('btn-secondary');
                showNotification(`Товар "${productName}" добавлен в сравнение!`);
            } else {
                compareItems = compareItems.filter(item => item.id != productId);
                localStorage.setItem('compareItems', JSON.stringify(compareItems));
                this.textContent = 'В сравнение';
                this.classList.remove('btn-success');
                this.classList.add('btn-secondary');
                showNotification(`Товар "${productName}" удален из сравнения!`);
            }
        });
    });
    
    // Функция обновления кнопок сравнения
    function updateCompareButtons() {
        const compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
        compareButtons.forEach(button => {
            const productId = button.getAttribute('data-product-id');
            if (compareItems.some(item => item.id == productId)) {
                button.textContent = '✓ В сравнении';
                button.classList.add('btn-success');
                button.classList.remove('btn-secondary');
            }
        });
    }
    
    updateCompareButtons();
    
    
    // 4. ПОИСК ПО ТОВАРАМ
    
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    
    if (searchBtn && searchInput) {
        function performSearch() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                window.location.href = `catalog.php?search=${encodeURIComponent(searchTerm)}`;
            }
        }
        
        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    // 5. ФУНКЦИЯ УВЕДОМЛЕНИЙ
    
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        // Стили для уведомления
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    
    // 7. ОБРАБОТЧИК ИЗМЕНЕНИЯ РАЗМЕРА ОКНА
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            console.log('Размер окна изменен:', window.innerWidth, 'px');
            

        }, 250);
    });
});

// 8. BITRIX24 ВИДЖЕТ (не трогаем)
(function(w,d,u){
    var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
    var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
})(window,document,'https://cdn-ru.bitrix24.by/b15313854/crm/site_button/loader_6_ykawzi.js');
