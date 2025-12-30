<?php
$BITRIX_WEBHOOK = 'https://k7s.bitrix24.by/rest/25370/dhzvmrk2o9q56985/crm.lead.add.json';

// ============================================
// ОБРАБОТКА ФОРМЫ
// ============================================
$form_success = false;
$form_error = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    $form_data = compact('name', 'email', 'phone', 'message');
    
    if (empty($name) || empty($email) || empty($phone)) {
        $form_error = 'Пожалуйста, заполните все обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = 'Пожалуйста, введите корректный email адрес';
    } else {
        $leadData = [
            'fields' => [
                'TITLE' => 'Заявка с сайта Dosatron',
                'NAME' => $name,
                'PHONE' => [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']],
                'EMAIL' => [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']],
                'SOURCE_ID' => 'WEB',
                'SOURCE_DESCRIPTION' => 'Контактная форма сайта',
                'ASSIGNED_BY_ID' => 1,
                'STATUS_ID' => 'NEW',
                'COMMENTS' => "Имя: $name\nEmail: $email\nТелефон: $phone\nСообщение: $message\n\nДата: " . date('d.m.Y H:i:s'),
                'UF_CRM_SITE' => 'dosa',
                'UTM_SOURCE' => 'direct',
                'UTM_MEDIUM' => 'contact_form'
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $BITRIX_WEBHOOK,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($leadData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        file_put_contents('bitrix_log.txt', 
            date('Y-m-d H:i:s') . " | HTTP: $httpCode\n" .
            "Данные: " . print_r($leadData, true) . "\n" .
            "Ответ: " . print_r($result, true) . "\n\n",
            FILE_APPEND
        );
        
        if (isset($result['result'])) {
            $form_success = true;
            $form_data = [];
        } else {
            $form_error = 'Ошибка отправки. Пожалуйста, позвоните нам.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты | 7 company</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Стили для уведомлений */
        .form-notification {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: 500;
        }
        
        .form-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .form-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Стили для подсветки полей */
        .form-input-split.valid {
            border-color: #4CAF50 !important;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1) !important;
            background-color: rgba(76, 175, 80, 0.05) !important;
        }
        
        .form-input-split.invalid {
            border-color: #f44336 !important;
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1) !important;
        }
        
        .form-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .form-hint.valid-hint {
            color: #4CAF50;
            font-weight: 500;
        }
        
        /* Счетчик символов */
        .textarea-counter-split {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .textarea-counter-split.warning {
            color: #ff9800;
        }
        
        .textarea-counter-split.error {
            color: #f44336;
        }
        
        .textarea-counter-split.valid {
            color: #4CAF50;
        }
        
        /* Индикатор загрузки */
        .form-loading {
            display: none;
            text-align: center;
            padding: 10px;
            color: #666;
        }
        
        .form-loading.active {
            display: block;
        }
        
        /* Стили для карты */
        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #ddd;
            margin-top: 20px;
        }
        
        #map {
            width: 100%;
            height: 100%;
        }
        
        .map-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .map-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header__inner">
                <a href="index.php" class="logo">
                    <div class="logo__img">
                        <img src="logo.jpg" alt="7company" width="40" height="40">
                    </div>
                    <span class="logo__text">7 company</span>
                </a>

                <nav class="nav">
                    <ul class="nav__list">
                        <li class="nav__item">
                            <a href="index.php" class="nav__link">Главная</a>
                        </li>
                        <li class="nav__item">
                            <a href="catalog.php" class="nav__link">Каталог</a>
                        </li>
                        <li class="nav__item">
                            <a href="contacts.php" class="nav__link nav__link--active">Контакты</a>
                        </li>
                         <li>
                            <a href="compare.php" class="nav__link">Сравнение</a>
                        </li>
                        <li>
                            <a href="#contactFormSplit" class="btn btn-primary">Заказать</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <section class="page-hero contacts-hero">
            <div class="container">
                <h1 class="page-hero__title">Свяжитесь с нами</h1>
                <p class="page-hero__text">Мы всегда рады помочь вам с выбором продукции и ответить на вопросы</p>
            </div>
        </section>

        <section class="contacts">
            <div class="container">
                <h2 class="section-title">Свяжитесь с нами</h2>
                
                <?php if ($form_success): ?>
                <div class="form-notification form-success">
                    ✅ Спасибо! Ваша заявка отправлена. Мы свяжемся с вами в течение 30 минут.
                </div>
                <?php elseif ($form_error): ?>
                <div class="form-notification form-error">
                    ⚠️ <?php echo $form_error; ?>
                </div>
                <?php endif; ?>
                
                <div class="contacts-split">
                    <div class="contacts-left">
                        <div class="contact-card">
                            <div class="contact-header">
                                <h3>Контакты</h3>
                                <p>Мы всегда рады помочь вам</p>
                            </div>
                            
                            <div class="contact-list">
                                <div class="contact-list-item">
                                    <div class="contact-icon">📍</div>
                                    <div class="contact-details">
                                        <h4>Адрес</h4>
                                        <p>г. Минск, ул. Толбухина д.2</p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">📞</div>
                                    <div class="contact-details">
                                        <h4>Телефоны</h4>
                                        <p>
                                            <strong>Отдел продаж:</strong> +375 (29) 605-22-73<br>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">✉️</div>
                                    <div class="contact-details">
                                        <h4>Электронная почта</h4>
                                        <p>
                                            <strong>Общие вопросы:</strong> info@7company.by<br>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">🕒</div>
                                    <div class="contact-details">
                                        <h4>Часы работы</h4>
                                        <p>
                                            <strong>Пн-Пт:</strong> 9:00 - 18:00<br>
                                            <strong>Суббота-Воскресенье:</strong> выходной
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">🚚</div>
                                    <div class="contact-details">
                                        <h4>Доставка</h4>
                                        <p>
                                            <strong>По городу:</strong> 1-2 рабочих дня<br>
                                            <strong>По Беларуси:</strong> 3-5 рабочих дней
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="quick-contact">
                            <h3>Быстрая связь</h3>
                            <div class="quick-buttons">
                                <a href="tel:+375296052273" class="quick-btn phone-btn">
                                    <span class="quick-icon">📞</span>
                                    <span>Позвонить</span>
                                </a>
                                <a href="mailto:info@7company.by" class="quick-btn email-btn">
                                    <span class="quick-icon">✉️</span>
                                    <span>Написать</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contacts-right">
                        <div class="form-card">
                            <div class="form-header">
                                <h2>Отправить запрос</h2>
                                <p class="form-subtitle">Заполните форму и мы свяжемся с вами в течение 30 минут</p>
                            </div>
                            
                            <form class="contact-form-split" id="contactFormSplit" method="POST" action="">
                                <div class="form-row-split">
                                    <div class="form-group-split">
                                        <label for="nameSplit" class="form-label-split">
                                            ФИО *
                                            <span class="form-required">обязательно</span>
                                        </label>
                                        <input type="text" id="nameSplit" name="name" class="form-input-split" 
                                               placeholder="Иванов Иван Иванович" 
                                               value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                                               required>
                                        <div class="form-hint name-hint">Пример: Иванов Иван Иванович</div>
                                    </div>
                                    
                                    <div class="form-group-split">
                                        <label for="emailSplit" class="form-label-split">
                                            Электронная почта *
                                            <span class="form-required">обязательно</span>
                                        </label>
                                        <input type="email" id="emailSplit" name="email" class="form-input-split" 
                                               placeholder="example@mail.ru" 
                                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                               required>
                                        <div class="form-hint email-hint">Пример: example@mail.ru</div>
                                    </div>
                                </div>
                                
                                <div class="form-row-split">
                                    <div class="form-group-split">
                                        <label for="phoneSplit" class="form-label-split">
                                            Телефон *
                                            <span class="form-required">обязательно</span>
                                        </label>
                                        <input type="tel" id="phoneSplit" name="phone" class="form-input-split" 
                                               placeholder="+375 (29) 123-45-67" 
                                               value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                               required>
                                        <div class="form-hint phone-hint">Пример: +375 (29) 123-45-67</div>
                                    </div>
                                </div>
                                
                                <div class="form-group-split full-width">
                                    <label for="messageSplit" class="form-label-split">Сообщение</label>
                                    <textarea id="messageSplit" name="message" class="form-textarea-split" 
                                              placeholder="Опишите ваш вопрос или запрос..." 
                                              rows="5"><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                                    <div class="textarea-counter-split">
                                        <span class="current-chars-split">0</span>/<span class="max-chars-split">1000</span> символов
                                    </div>
                                </div>
                                
                                <div class="form-agreement-split">
                                    <div class="checkbox-wrapper-split">
                                        <input type="checkbox" id="agreeSplit" name="agree" class="form-checkbox-split" required>
                                        <label for="agreeSplit" class="checkbox-label-split">
                                            <span class="checkbox-custom-split"></span>
                                            <span class="checkbox-text-split">
                                                Я согласен на обработку персональных данных
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-submit-split">
                                    <div class="form-loading" id="formLoading">
                                        ⏳ Отправка запроса...
                                    </div>
                                    <button type="submit" class="btn btn-submit-split" id="submitBtn">
                                        <span class="btn-text-split">Отправить запрос</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="map">
            <div class="container">
                <h2 class="section-title">Мы на карте</h2>
                <div class="map-container">
                    <div id="map"></div>
                </div>
                <div style="text-align: center; margin-top: 15px; color: #666;">
                    <p>📍 г. Минск, ул. Толбухина д.2</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__col">
                    <a href="index.php" class="footer-logo">7company</a>
                    <p class="footer__text">Каталог медикаторов</p>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Контакты</h3>
                    <ul class="footer__list">
                        <li>📞 +375 (29) 605-22-73</li>
                        <li>✉️ info@7company.by</li>
                        <li>📍 г. Минск, ул. Толбухина д.2</li>
                    </ul>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Навигация</h3>
                    <ul class="footer__list">
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="catalog.php">Каталог</a></li> 
                        <li><a href="contacts.php">Контакты</a></li> 
                        <li><a href="compare.php">Сравнение</a></li>
                    </ul>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Часы работы</h3>
                    <ul class="footer__list">
                        <li>Пн-Пт: 9:00-18:00</li>
                        <li>Сб-Вс: Выходной</li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <p>&copy; 2025 7company. Все права защищены.</p>
            </div>
        </div>
    </footer>


    <script src="https://api-maps.yandex.ru/2.1/?apikey=ваш_ключ_яндекс_карт&lang=ru_RU" type="text/javascript"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ВАЛИДАЦИЯ ПОЛЕЙ С ПОДСВЕТКОЙ
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
                
                // Простая проверка на количество цифр
                if (phoneDigits.length >= 9) {
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
                            phoneHint.textContent = 'Номер должен содержать минимум 9 цифр';
                            phoneHint.classList.remove('valid-hint');
                        }
                    } else {
                        phoneInput.classList.remove('invalid');
                        if (phoneHint) {
                            phoneHint.textContent = 'Пример: +375 (29) 123-45-67';
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
                // Маска телефона (простая)
                phoneInput.addEventListener('input', function(e) {
                    // Разрешаем только: цифры, плюс, пробел, скобки, дефис
                    this.value = this.value.replace(/[^\d+()\s-]/g, '');
                    validatePhone();
                });
                
                phoneInput.addEventListener('blur', validatePhone);
                
                // При вставке
                phoneInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    const cleaned = pasted.replace(/[^\d+()\s-]/g, '');
                    
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
                try {
                    ymaps.ready(function() {
                        const map = new ymaps.Map('map', {
                            center: [53.902284, 27.561831], // Координаты Минска
                            zoom: 12,
                            controls: ['zoomControl', 'fullscreenControl']
                        });
                        
                        // Точка с адресом
                        const marker = new ymaps.Placemark([53.902284, 27.561831], {
                            balloonContent: '<strong>7 company</strong><br>г. Минск, ул. Толбухина д.2'
                        }, {
                            preset: 'islands#blueDotIcon'
                        });
                        
                        map.geoObjects.add(marker);
                        
                        // Открываем балун при загрузке
                        marker.balloon.open();
                        
                        // Добавляем поиск
                        const searchControl = new ymaps.control.SearchControl({
                            options: {
                                noPlacemark: true,
                                provider: 'yandex#search'
                            }
                        });
                        
                        map.controls.add(searchControl);
                    });
                } catch (error) {
                    console.log('Ошибка загрузки карты:', error);
                    // Если карта не загрузилась, показываем статическое изображение
                    document.getElementById('map').innerHTML = `
                        <div style="width:100%;height:100%;background:#f5f5f5;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:20px;text-align:center;">
                            <div style="font-size:48px;margin-bottom:20px;">📍</div>
                            <h3>7 company</h3>
                            <p>г. Минск, ул. Толбухина д.2</p>
                            <p style="margin-top:20px;color:#666;">Координаты: 53.902284, 27.561831</p>
                        </div>
                    `;
                }
            }
            
            // Запускаем карту
            initMap();
        });
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn-ru.bitrix24.by/b15313854/crm/site_button/loader_6_ykawzi.js');
    </script>
</body>
</html>