// Бургер-меню и dropdown (объединенный скрипт)
document.addEventListener('DOMContentLoaded', function() {
    // Элементы бургера
    const burgerBtn = document.getElementById('burgerBtn');
    const mainNav = document.getElementById('mainNav');
    const navOverlay = document.getElementById('navOverlay');
    
    // Элементы dropdown каталога
    const catalogLink = document.getElementById('catalogLink');
    const catalogDropdown = document.getElementById('catalogDropdown');
    
    // Состояния
    let isMenuOpen = false;
    let isDropdownOpen = false;
    
    // Проверяем мобилку
    function isMobile() {
        return window.innerWidth <= 992;
    }
    
    // ============= БУРГЕР-МЕНЮ =============
    if (burgerBtn && mainNav) {
        // Инициализация ARIA
        burgerBtn.setAttribute('aria-expanded', 'false');
        mainNav.setAttribute('aria-hidden', isMobile() ? 'true' : 'false');
        
        // Открытие меню
        function openMenu() {
            isMenuOpen = true;
            burgerBtn.classList.add('active');
            mainNav.classList.add('active');
            if (navOverlay) navOverlay.classList.add('active');
            document.body.classList.add('menu-open');
            
            burgerBtn.setAttribute('aria-expanded', 'true');
            mainNav.setAttribute('aria-hidden', 'false');
        }
        
        // Закрытие меню
        function closeMenu() {
            isMenuOpen = false;
            burgerBtn.classList.remove('active');
            mainNav.classList.remove('active');
            if (navOverlay) navOverlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            
            burgerBtn.setAttribute('aria-expanded', 'false');
            mainNav.setAttribute('aria-hidden', 'true');
            
            // Фокусируемся на кнопке
            burgerBtn.focus();
            
            // Закрываем dropdown если открыт
            if (catalogLink && catalogDropdown) {
                catalogLink.classList.remove('active');
                catalogDropdown.classList.remove('active');
                isDropdownOpen = false;
            }
        }
        
        // Клик по бургеру
        burgerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isMenuOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });
        
        // Клик по оверлею
        if (navOverlay) {
            navOverlay.addEventListener('click', function(e) {
                if (isMenuOpen && isMobile()) {
                    e.stopPropagation();
                    closeMenu();
                }
            });
        }
        
        // Клик по ссылкам в меню (кроме dropdown)
        const navLinks = mainNav.querySelectorAll('.nav__link:not(.catalog-link)');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (isMobile() && isMenuOpen) {
                    closeMenu();
                }
            });
        });
        
        // Закрытие по клику вне меню (только на мобилке)
        document.addEventListener('click', function(e) {
            if (!isMobile() || !isMenuOpen) return;
            
            const target = e.target;
            const isClickInsideMenu = mainNav.contains(target);
            const isClickOnBurger = burgerBtn.contains(target);
            const isClickOnOverlay = navOverlay && navOverlay.contains(target);
            
            // Если кликнули вне меню и не по бургеру
            if (!isClickInsideMenu && !isClickOnBurger && !isClickOnOverlay) {
                closeMenu();
            }
        });
        
        // Закрытие по Esc
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMenuOpen && isMobile()) {
                closeMenu();
            }
        });
        
        // Ресайз окна
        window.addEventListener('resize', function() {
            if (isMenuOpen && !isMobile()) {
                closeMenu();
            }
            // Обновляем ARIA при ресайзе
            if (!isMenuOpen) {
                mainNav.setAttribute('aria-hidden', isMobile() ? 'true' : 'false');
            }
        });
    }
    
    // ============= DROPDOWN КАТАЛОГА =============
    if (catalogLink && catalogDropdown) {
        // Только на мобилке
        if (isMobile()) {
            catalogLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Переключаем состояние
                isDropdownOpen = !isDropdownOpen;
                this.classList.toggle('active');
                catalogDropdown.classList.toggle('active');
            });
            
            // Обработчик для ссылок в dropdown
            const dropdownLinks = catalogDropdown.querySelectorAll('a');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (isMobile() && isMenuOpen) {
                        // Закрываем меню после выбора категории
                        if (burgerBtn && mainNav) {
                            burgerBtn.classList.remove('active');
                            mainNav.classList.remove('active');
                            if (navOverlay) navOverlay.classList.remove('active');
                            document.body.classList.remove('menu-open');
                            isMenuOpen = false;
                            
                            burgerBtn.setAttribute('aria-expanded', 'false');
                            mainNav.setAttribute('aria-hidden', 'true');
                        }
                    }
                    
                    // Закрываем dropdown
                    catalogLink.classList.remove('active');
                    catalogDropdown.classList.remove('active');
                    isDropdownOpen = false;
                });
            });
        }
        
        // Ресайз - сбрасываем dropdown на десктопе
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                catalogLink.classList.remove('active');
                catalogDropdown.classList.remove('active');
                isDropdownOpen = false;
            }
        });
    }
});

// ============= ФОРМА КОНТАКТОВ (оставляем как есть) =============
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
            document.body.insertAdjacentElement('afterbegin', errorDiv);
        }
        
        setTimeout(() => errorDiv.remove(), 5000);
    }
    

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
});