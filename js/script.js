// Бургер-меню
(function() {
    'use strict';
    
    function initBurgerMenu() {
        const burgerBtn = document.getElementById('burgerBtn');
        const mainNav = document.getElementById('mainNav');
        
        if (!burgerBtn || !mainNav) {
            console.warn('Burger menu elements not found');
            return;
        }
        
        const navLinks = document.querySelectorAll('.nav__link');
        
        // Открытие/закрытие меню
        burgerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            burgerBtn.classList.toggle('active');
            mainNav.classList.toggle('active');
            
            if (mainNav.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
        
        // Закрытие меню при клике на ссылку
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                burgerBtn.classList.remove('active');
                mainNav.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Закрытие меню при клике вне его области
        document.addEventListener('click', function(e) {
            if (mainNav.classList.contains('active') && 
                !mainNav.contains(e.target) && 
                !burgerBtn.contains(e.target) &&
                window.innerWidth <= 768) {
                burgerBtn.classList.remove('active');
                mainNav.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Закрытие меню при изменении размера окна (если перешли на десктоп)
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 768) {
                    burgerBtn.classList.remove('active');
                    mainNav.classList.remove('active');
                    document.body.style.overflow = '';
                }
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
            const btnText = submitBtn.querySelector('.btn-text-split');
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
        
        // Маска для телефона
        const phoneInput = document.getElementById('phoneSplit');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                let formattedValue = '';
                
                if (value.length > 0) {
                    formattedValue = '+375 (';
                    if (value.length > 2) {
                        formattedValue += value.substring(0, 2) + ') ';
                        if (value.length > 4) {
                            formattedValue += value.substring(2, 4) + '-';
                            if (value.length > 6) {
                                formattedValue += value.substring(4, 6) + '-';
                                if (value.length > 8) {
                                    formattedValue += value.substring(6, 8);
                                } else {
                                    formattedValue += value.substring(6);
                                }
                            } else {
                                formattedValue += value.substring(4);
                            }
                        } else {
                            formattedValue += value.substring(2);
                        }
                    } else {
                        formattedValue += value;
                    }
                }
                
                e.target.value = formattedValue;
            });
        }
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
        formHeader.insertAdjacentElement('afterend', errorDiv);
        
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