// ВАЛИДАЦИЯ ПОЛЕЙ С ПОДСВЕТКОЙ
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('nameSplit');
    const emailInput = document.getElementById('emailSplit');
    const phoneInput = document.getElementById('phoneSplit');
    const messageTextarea = document.getElementById('messageSplit');
    
    // Функция валидации имени
    function validateName() {
        const name = nameInput.value.trim();
        const nameHint = document.querySelector('.name-hint');
        
        if (name.length >= 2) {
            nameInput.classList.add('valid');
            nameInput.classList.remove('invalid');
            if (nameHint) {
                nameHint.textContent = '✓ Имя введено корректно';
                nameHint.classList.add('valid-hint');
            }
            return true;
        } else {
            nameInput.classList.remove('valid');
            if (name.length > 0) {
                nameInput.classList.add('invalid');
                if (nameHint) {
                    nameHint.textContent = 'Имя должно содержать минимум 2 символа';
                    nameHint.classList.remove('valid-hint');
                }
            } else {
                nameInput.classList.remove('invalid');
                if (nameHint) {
                    nameHint.textContent = 'Пример: Иванов Иван Иванович';
                    nameHint.classList.remove('valid-hint');
                }
            }
            return false;
        }
    }
    
    // Функция валидации email
    function validateEmail() {
        const email = emailInput.value.trim();
        const emailHint = document.querySelector('.email-hint');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (emailRegex.test(email)) {
            emailInput.classList.add('valid');
            emailInput.classList.remove('invalid');
            if (emailHint) {
                emailHint.textContent = '✓ Email введен корректно';
                emailHint.classList.add('valid-hint');
            }
            return true;
        } else {
            emailInput.classList.remove('valid');
            if (email.length > 0) {
                emailInput.classList.add('invalid');
                if (emailHint) {
                    emailHint.textContent = 'Введите корректный email адрес';
                    emailHint.classList.remove('valid-hint');
                }
            } else {
                emailInput.classList.remove('invalid');
                if (emailHint) {
                    emailHint.textContent = 'Пример: example@mail.ru';
                    emailHint.classList.remove('valid-hint');
                }
            }
            return false;
        }
    }
    
    // Функция валидации телефона
    function validatePhone() {
        const phone = phoneInput.value.trim();
        const phoneHint = document.querySelector('.phone-hint');
        const phoneDigits = phone.replace(/\D/g, '');
        
        // Простая проверка на количество цифр (от 9 до 15)
        if (phoneDigits.length >= 9 && phoneDigits.length <= 15) {
            phoneInput.classList.add('valid');
            phoneInput.classList.remove('invalid');
            if (phoneHint) {
                phoneHint.textContent = '✓ Номер введен корректно';
                phoneHint.classList.add('valid-hint');
            }
            return true;
        } else {
            phoneInput.classList.remove('valid');
            if (phone.length > 0) {
                phoneInput.classList.add('invalid');
                if (phoneHint) {
                    phoneHint.textContent = 'Номер должен содержать от 9 до 15 цифр';
                    phoneHint.classList.remove('valid-hint');
                }
            } else {
                phoneInput.classList.remove('invalid');
                if (phoneHint) {
                    phoneHint.textContent = 'Введите номер телефона';
                    phoneHint.classList.remove('valid-hint');
                }
            }
            
            return false;
        }
    }
    
    // Счетчик символов для textarea
    const counter = document.querySelector('.textarea-counter-split');
    const currentChars = counter?.querySelector('.current-chars-split');
    const maxChars = 1000;
    
    function updateCounter() {
        if (!messageTextarea || !counter || !currentChars) return;
        
        const length = messageTextarea.value.length;
        currentChars.textContent = length;
        
        // Сбрасываем все классы
        counter.classList.remove('warning', 'error', 'valid');
        
        if (length === 0) {
            counter.style.color = '#666';
        } else if (length > maxChars) {
            counter.classList.add('error');
        } else if (length > maxChars - 100) {
            counter.classList.add('warning');
        } else if (length >= 10) {
            counter.classList.add('valid');
        }
        
        // Автоподсветка текстового поля
        if (length >= 10 && length <= maxChars) {
            messageTextarea.classList.add('valid');
            messageTextarea.classList.remove('invalid');
        } else if (length > maxChars) {
            messageTextarea.classList.add('invalid');
            messageTextarea.classList.remove('valid');
        } else {
            messageTextarea.classList.remove('valid', 'invalid');
        }
    }
    
    // ОБРАБОТЧИКИ СОБЫТИЙ
    if (nameInput) {
        nameInput.addEventListener('input', validateName);
        nameInput.addEventListener('blur', validateName);
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
        emailInput.addEventListener('blur', validateEmail);
    }
    
    if (phoneInput) {
        // Упрощенная маска - разрешаем все символы
        phoneInput.addEventListener('input', function(e) {
            // Убираем только буквы и спецсимволы, оставляем цифры и основные знаки
            this.value = this.value.replace(/[^\d+()\-\s]/g, '');
            validatePhone();
        });
        
        phoneInput.addEventListener('blur', validatePhone);
        
        // При вставке
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            // Очищаем от мусора
            const cleaned = pasted.replace(/[^\d+()\-\s]/g, '');
            
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const current = this.value;
            
            this.value = current.substring(0, start) + cleaned + current.substring(end);
            validatePhone();
        });
    }
    
    if (messageTextarea && counter && currentChars) {
        messageTextarea.addEventListener('input', updateCounter);
        messageTextarea.addEventListener('input', function() {
            if (this.value.length > maxChars) {
                this.value = this.value.substring(0, maxChars);
            }
            updateCounter();
        });
        updateCounter();
    }
    
    // Инициализация валидации при загрузке
    validateName();
    validateEmail();
    validatePhone();
    
    // Обработка формы
    const contactForm = document.getElementById('contactFormSplit');
    const submitBtn = document.getElementById('submitBtn');
    const formLoading = document.getElementById('formLoading');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Проверяем согласие
            const agreeCheckbox = document.getElementById('agreeSplit');
            if (!agreeCheckbox.checked) {
                alert('Пожалуйста, согласитесь на обработку персональных данных');
                agreeCheckbox.focus();
                agreeCheckbox.style.outline = '2px solid #f44336';
                return;
            } else {
                agreeCheckbox.style.outline = 'none';
            }
            
            // Проверяем все поля
            const isNameValid = validateName();
            const isEmailValid = validateEmail();
            const isPhoneValid = validatePhone();
            
            if (!isNameValid || !isEmailValid || !isPhoneValid) {
                alert('Пожалуйста, исправьте ошибки в форме');
                if (!isNameValid) nameInput.focus();
                else if (!isEmailValid) emailInput.focus();
                else phoneInput.focus();
                return;
            }
            
            // Показываем индикатор загрузки
            submitBtn.style.display = 'none';
            formLoading.classList.add('active');
            
            // Отправляем форму
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                window.location.reload();
            })
            .catch(error => {
                alert('Ошибка отправки. Пожалуйста, попробуйте позже или позвоните нам.');
                submitBtn.style.display = 'block';
                formLoading.classList.remove('active');
            });
        });
    }
    
    // Плавный скролл к форме
    const orderButtons = document.querySelectorAll('a[href="#contactFormSplit"]');
    orderButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const formElement = document.getElementById('contactFormSplit');
            if (formElement) {
                formElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // ИНИЦИАЛИЗАЦИЯ КАРТЫ
    function initMap() {
        // Проверяем, загрузился ли API Яндекс.Карт
        if (typeof ymaps === 'undefined') {
            console.log('Yandex Maps API не загружен');
            showFallbackMap();
            return;
        }
        
        try {
            ymaps.ready(function() {
                console.log('Yandex Maps API готов к работе');
                
                const mapElement = document.getElementById('map');
                if (!mapElement) {
                    console.log('Элемент карты не найден');
                    return;
                }
                
                // Координаты Смоленска, Вяземская 2-я, д.4
                const coordinates = [54.782952, 32.026853];
                
                const map = new ymaps.Map('map', {
                    center: coordinates,
                    zoom: 17,
                    controls: ['zoomControl', 'fullscreenControl']
                });
                
                // Создаем метку
                const marker = new ymaps.Placemark(coordinates, {
                    balloonContentHeader: 'Dosatron',
                    balloonContentBody: 'г. Смоленск, 2-я Вяземская улица, д.4',
                    balloonContentFooter: 'Офис Р58'
                }, {
                    preset: 'islands#blueDotIcon',
                    iconColor: '#00aaff'
                });
                
                map.geoObjects.add(marker);
                
                // Автоматически открываем балун
                marker.balloon.open();
                
                console.log('Карта успешно инициализирована');
            });
        } catch (error) {
            console.log('Ошибка при создании карты:', error);
            showFallbackMap();
        }
    }

    // Фолбэк если карта не загрузилась
    function showFallbackMap() {
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.innerHTML = `
                <div style="width:100%;height:100%;background:linear-gradient(135deg, #1a237e, #311b92);display:flex;align-items:center;justify-content:center;flex-direction:column;padding:20px;text-align:center;color:white;border-radius:12px;">
                    <div style="font-size:60px;margin-bottom:20px;">📍</div>
                    <h3 style="font-size:24px;margin-bottom:10px;color:#00aaff;">Dosatron</h3>
                    <p style="font-size:18px;margin-bottom:5px;">г. Смоленск, 2-я Вяземская улица, д.4</p>
                    <p style="font-size:16px;margin-bottom:20px;">Офис Р58</p>
                    <p style="color:#aaa;font-size:14px;">
                        Карта временно недоступна<br>
                        <a href="https://yandex.ru/maps/?text=Смоленск,+2-я+Вяземская+улица,+д.4" 
                           target="_blank" 
                           style="color:#00aaff;text-decoration:underline;">
                            Посмотреть на Яндекс.Картах
                        </a>
                    </p>
                </div>
            `;
        }
    }
    
    // Инициализация карты
    console.log('DOM загружен, инициализируем карту...');
    
    // Даем время на загрузку API Яндекс.Карт
    setTimeout(function() {
        initMap();
    }, 1000);
    
    // Также запускаем через 5 секунд на всякий случай
    setTimeout(function() {
        if (!document.querySelector('#map ymaps')) {
            console.log('Карта не загрузилась за 5 секунд, проверяем...');
            initMap();
        }
    }, 5000);
});
 // 3. ВЫПАДАЮЩЕЕ МЕНЮ КАТАЛОГА НА МОБИЛКЕ
    
    const catalogLink = document.getElementById('catalogLink');
    const catalogDropdown = document.getElementById('catalogDropdown');
    
    if (catalogLink && catalogDropdown && window.innerWidth <= 992) {
        catalogLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            catalogDropdown.classList.toggle('active');
        });
        
        // Закрытие при клике вне меню
        document.addEventListener('click', function(e) {
            if (!catalogLink.contains(e.target) && !catalogDropdown.contains(e.target)) {
                catalogLink.classList.remove('active');
                catalogDropdown.classList.remove('active');
            }
        });
    }
    
    
    // 6. БУРГЕР МЕНЮ (если есть)
    const burgerBtn = document.getElementById('burgerBtn');
    const navOverlay = document.getElementById('navOverlay');
    const mainNav = document.getElementById('mainNav');
    
    if (burgerBtn && mainNav) {
        burgerBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
            if (navOverlay) navOverlay.classList.toggle('active');
            document.body.classList.toggle('menu-open');
            
            // Обновляем aria атрибуты
            const isExpanded = this.classList.contains('active');
            this.setAttribute('aria-expanded', isExpanded);
        });
        
        if (navOverlay) {
            navOverlay.addEventListener('click', function() {
                burgerBtn.classList.remove('active');
                mainNav.classList.remove('active');
                this.classList.remove('active');
                document.body.classList.remove('menu-open');
                burgerBtn.setAttribute('aria-expanded', 'false');
            });
        }
    }


// Bitrix24 loader
(function(w,d,u){
    var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
    var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
})(window,document,'https://cdn-ru.bitrix24.by/b15313854/crm/site_button/loader_6_ykawzi.js');
