// Бургер-меню с поддержкой доступности и ресайза
(function() {
    'use strict';
    
    let isMenuOpen = false;
    let focusableElements = [];
    let firstFocusableElement = null;
    let lastFocusableElement = null;
    
    function isMobile() {
        return window.innerWidth <= 992;
    }
    
    function getFocusableElements(container) {
        const focusableSelectors = [
            'a[href]',
            'button:not([disabled])',
            'textarea:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            '[tabindex]:not([tabindex="-1"])'
        ];
        return Array.from(container.querySelectorAll(focusableSelectors.join(', ')));
    }
    
    function trapFocus(container) {
        focusableElements = getFocusableElements(container);
        if (focusableElements.length > 0) {
            firstFocusableElement = focusableElements[0];
            lastFocusableElement = focusableElements[focusableElements.length - 1];
            firstFocusableElement.focus();
        }
    }
    
    function releaseFocus() {
        focusableElements = [];
        firstFocusableElement = null;
        lastFocusableElement = null;
    }
    
    function openMenu(burgerBtn, mainNav, navOverlay) {
        isMenuOpen = true;
        burgerBtn.classList.add('active');
        mainNav.classList.add('active');
        if (navOverlay) navOverlay.classList.add('active');
        document.body.classList.add('menu-open');
        
        // ARIA
        burgerBtn.setAttribute('aria-expanded', 'true');
        mainNav.setAttribute('aria-hidden', 'false');
        
        // Фокус-трап
        if (isMobile()) {
            trapFocus(mainNav);
        }
    }
    
    function closeMenu(burgerBtn, mainNav, navOverlay) {
        isMenuOpen = false;
        burgerBtn.classList.remove('active');
        mainNav.classList.remove('active');
        if (navOverlay) navOverlay.classList.remove('active');
        document.body.classList.remove('menu-open');
        
        // ARIA
        burgerBtn.setAttribute('aria-expanded', 'false');
        mainNav.setAttribute('aria-hidden', 'true');
        
        // Возвращаем фокус на кнопку
        burgerBtn.focus();
        releaseFocus();
    }
    
    function initBurgerMenu() {
        const burgerBtn = document.getElementById('burgerBtn');
        const mainNav = document.getElementById('mainNav');
        const navOverlay = document.getElementById('navOverlay');
        
        if (!burgerBtn || !mainNav) {
            return;
        }
        
        // Инициализация ARIA
        burgerBtn.setAttribute('aria-expanded', 'false');
        burgerBtn.setAttribute('aria-controls', 'mainNav');
        mainNav.setAttribute('aria-hidden', isMobile() ? 'true' : 'false');
        
        // Открытие/закрытие меню
        burgerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isMenuOpen) {
                closeMenu(burgerBtn, mainNav, navOverlay);
            } else {
                openMenu(burgerBtn, mainNav, navOverlay);
            }
        });
        
        // Закрытие по клику на оверлей
        if (navOverlay) {
            navOverlay.addEventListener('click', function() {
                if (isMenuOpen && isMobile()) {
                    closeMenu(burgerBtn, mainNav, navOverlay);
                }
            });
        }
        
        // Закрытие по Esc
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMenuOpen && isMobile()) {
                closeMenu(burgerBtn, mainNav, navOverlay);
            }
        });
        
        // Фокус-трап внутри меню
        if (mainNav) {
            mainNav.addEventListener('keydown', function(e) {
                if (!isMenuOpen || !isMobile()) return;
                
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        // Shift + Tab
                        if (document.activeElement === firstFocusableElement) {
                            e.preventDefault();
                            lastFocusableElement.focus();
                        }
                    } else {
                        // Tab
                        if (document.activeElement === lastFocusableElement) {
                            e.preventDefault();
                            firstFocusableElement.focus();
                        }
                    }
                }
            });
        }
        
        // Закрытие меню при клике на ссылку (только на мобилке)
        const navLinks = mainNav.querySelectorAll('.nav__link:not(.catalog-link)');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (isMobile()) {
                    closeMenu(burgerBtn, mainNav, navOverlay);
                }
            });
        });
        
        // Обработка resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                const wasMobile = isMobile();
                const nowMobile = window.innerWidth <= 992;
                
                // Если перешли с мобилки на десктоп - закрываем меню
                if (wasMobile && !nowMobile && isMenuOpen) {
                    closeMenu(burgerBtn, mainNav, navOverlay);
                }
                
                // Обновляем ARIA
                mainNav.setAttribute('aria-hidden', nowMobile && !isMenuOpen ? 'true' : 'false');
            }, 100);
        });
    }
    
    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBurgerMenu);
    } else {
        initBurgerMenu();
    }
})();

// Обработка формы в split макете
document.addEventListener('DOMContentLoaded', function() {
    const contactFormSplit = document.getElementById('contactFormSplit');
    const textareaSplit = document.getElementById('messageSplit');
    const charsCounterSplit = document.querySelector('.current-chars-split');
    
    if (contactFormSplit) {
        // Счетчик символов
        if (textareaSplit && charsCounterSplit) {
            textareaSplit.addEventListener('input', function() {
                charsCounterSplit.textContent = this.value.length;
                
                if (this.value.length > 1000) {
                    charsCounterSplit.style.color = 'hsl(0, 84%, 60%)';
                } else {
                    charsCounterSplit.style.color = 'var(--foreground)';
                }
            });
        }
        
        // Отправка формы
        contactFormSplit.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('nameSplit').value.trim(),
                email: document.getElementById('emailSplit').value.trim(),
                phone: document.getElementById('phoneSplit').value.trim(),
                company: document.getElementById('companySplit').value.trim(),
                subject: document.getElementById('subjectSplit').value,
                message: document.getElementById('messageSplit').value.trim(),
                agree: document.getElementById('agreeSplit').checked
            };
            
            // Валидация
            if (!formData.name || !formData.email || !formData.phone || !formData.subject || !formData.agree) {
                showError('Пожалуйста, заполните все обязательные поля');
                return;
            }
            
            // Email валидация
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(formData.email)) {
                showError('Пожалуйста, введите корректный email адрес');
                return;
            }
            
            // Телефон валидация
            const phoneDigits = formData.phone.replace(/\D/g, '');
            if (phoneDigits.length < 9) {
                showError('Пожалуйста, введите корректный номер телефона');
                return;
            }
            
            // Имитация отправки
            const submitBtn = document.querySelector('.btn-submit-split');
            if (!submitBtn) return;
            
            const btnText = submitBtn.querySelector('.btn-text-split');
            if (!btnText) return;
            
            const originalText = btnText.textContent;
            
            submitBtn.disabled = true;
            btnText.textContent = 'Отправка...';
            
            setTimeout(() => {
                // Показываем успех
                const successHTML = `
                    <div class="success-message">
                        <div class="success-icon">✅</div>
                        <h3>Запрос отправлен!</h3>
                        <p>Спасибо, ${formData.name}! Мы свяжемся с вами в течение 30 минут.</p>
                        <p>Номер заявки: #${Date.now().toString().slice(-6)}</p>
                        <button class="btn btn-primary" onclick="this.parentElement.remove(); contactFormSplit.style.display = 'block';">
                            Отправить ещё
                        </button>
                    </div>
                `;
                
                contactFormSplit.style.display = 'none';
                contactFormSplit.insertAdjacentHTML('afterend', successHTML);
                
                // Восстанавливаем кнопку
                submitBtn.disabled = false;
                btnText.textContent = originalText;
            }, 2000);
        });

    }
    
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.style.cssText = `
            background: hsl(0 84% 60% / 0.1);
            border: 1px solid hsl(0, 84%, 60%);
            color: hsl(0, 84%, 80%);
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            text-align: center;
            animation: slideIn 0.3s ease;
        `;
        errorDiv.textContent = message;
        
        const formHeader = document.querySelector('.form-header');
        if (formHeader) {
            formHeader.insertAdjacentElement('afterend', errorDiv);
        } else {
            // Если form-header не найден, добавляем в начало body
            document.body.insertAdjacentElement('afterbegin', errorDiv);
        }
        
        setTimeout(() => errorDiv.remove(), 5000);
    }
});

const successStyles = document.createElement('style');
successStyles.textContent = `
    .success-message {
        background: hsl(120 84% 60% / 0.1);
        border: 1px solid hsl(120 84% 60%);
        border-radius: var(--radius);
        padding: 30px;
        text-align: center;
        animation: slideIn 0.3s ease;
    }
    
    .success-icon {
        font-size: 3rem;
        margin-bottom: 20px;
    }
    
    .success-message h3 {
        color: var(--foreground);
        margin-bottom: 15px;
        font-size: 1.5rem;
    }
    
    .success-message p {
        color: var(--muted-foreground);
        margin-bottom: 10px;
        line-height: 1.5;
    }
    
    .success-message .btn {
        margin-top: 20px;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(successStyles);

document.addEventListener('click', function(e) {

    if (window.innerWidth > 992) return;
    
    const burgerBtn = document.getElementById('burgerBtn');
    const mainNav = document.getElementById('mainNav');
    const navOverlay = document.getElementById('navOverlay');
    

    if (!burgerBtn || !burgerBtn.classList.contains('active')) return;
    

    const clicked = e.target;

    if (!burgerBtn.contains(clicked) && !mainNav.contains(clicked) && 
        (!navOverlay || !navOverlay.contains(clicked))) {
        

        burgerBtn.classList.remove('active');
        mainNav.classList.remove('active');
        if (navOverlay) navOverlay.classList.remove('active');
        document.body.classList.remove('menu-open');

        burgerBtn.setAttribute('aria-expanded', 'false');
        if (mainNav) mainNav.setAttribute('aria-hidden', 'true');
    }
});