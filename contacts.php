<?php
$BITRIX_WEBHOOK = 'https://k7s.bitrix24.by/rest/25370/dhzvmrk2o9q56985/crm.lead.add.json';


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
<?php
function getContent($section) {
    // Подключаем парсер
    require_once 'includes/content_parser.php';
    return getContentSection($section, '');
}


$contact_phone = getContent('contact_phone');
$contact_email = getContent('contact_email');
$contact_address = getContent('contact_address');
$working_hours = getContent('working_hours');
$meta_desc = getContent('meta_description');
$meta_keys = getContent('meta_keywords');
$page_title = getContent('header_title');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_desc; ?>">
    <meta name="keywords" content="<?php echo $meta_keys; ?>">
    <link rel="stylesheet" href="cs/style.css">
    <script src="j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script>   
     <style>
  /* ОБЩИЕ СТИЛИ СТРАНИЦЫ КОНТАКТОВ */
        .page-contacts {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
        }

        /* СЕКЦИЯ КОНТАКТОВ */
        .contacts {
            margin: 40px 0;
        }

        .contacts-split {
            display: flex;
            gap: 40px;
            margin-top: 30px;
            align-items: flex-start;
        }

        @media (max-width: 992px) {
            .contacts-split {
                flex-direction: column;
            }
        }

        /* ЛЕВАЯ КОЛОНКА - КОНТАКТЫ */
        .contacts-left {
            flex: 1;
            min-width: 300px;
        }

        .contact-card {
            background: hsl(220 35% 8%);
            border: 1px solid hsl(200 30% 18%);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .contact-header h3 {
            color: hsl(195 100% 50%);
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .contact-header p {
            color: hsl(200 20% 70%);
            margin-bottom: 25px;
        }

        .contact-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .contact-list-item {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .contact-icon {
            font-size: 24px;
            color: hsl(195 100% 50%);
            min-width: 40px;
            text-align: center;
        }

        .contact-details h4 {
            color: white;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .contact-details p {
            color: hsl(200 20% 70%);
            line-height: 1.5;
        }

        .contact-details strong {
            color: white;
        }

        /* БЫСТРАЯ СВЯЗЬ */
        .quick-contact {
            background: hsl(220 35% 8%);
            border: 1px solid hsl(200 30% 18%);
            border-radius: 12px;
            padding: 25px;
        }

        .quick-contact h3 {
            color: hsl(195 100% 50%);
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .quick-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .quick-btn {
            flex: 1;
            min-width: 150px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: hsl(200 30% 12%);
            border: 1px solid hsl(200 30% 18%);
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .quick-btn:hover {
            background: hsl(195 100% 50%);
            border-color: hsl(195 100% 50%);
            color: hsl(220 40% 5%);
        }

        .quick-icon {
            font-size: 20px;
        }

        /* ПРАВАЯ КОЛОНКА - ФОРМА */
        .contacts-right {
            flex: 1.5;
            min-width: 300px;
        }

        .form-card {
            background: hsl(220 35% 8%);
            border: 1px solid hsl(200 30% 18%);
            border-radius: 12px;
            padding: 30px;
        }

        .form-header h2 {
            color: hsl(195 100% 50%);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .form-subtitle {
            color: hsl(200 20% 70%);
            margin-bottom: 25px;
        }

        /* СТИЛИ ФОРМЫ */
        .contact-form-split {
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
        }

        /* Группы полей формы */
        .form-row-split {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .form-row-split {
                flex-direction: column;
                gap: 0;
            }
        }

        .form-group-split {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group-split.full-width {
            width: 100%;
        }

        /* Лейблы */
        .form-label-split {
            font-size: 0.9rem;
            color: hsl(200 20% 70%);
            display: block;
        }

        /* Inputs и textarea */
        .form-input-split,
        .form-textarea-split {
            appearance: none;
            background: hsl(200 30% 12%);
            border: 1px solid hsl(200 30% 18%);
            border-radius: 8px;
            padding: 12px 14px;
            color: white;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .form-input-split:focus,
        .form-textarea-split:focus {
            outline: 2px solid hsl(195 100% 50%);
            outline-offset: 2px;
        }

        .form-textarea-split {
            resize: vertical;
            min-height: 120px;
        }

        /* Счетчик символов */
        .textarea-counter-split {
            font-size: 0.8rem;
            color: hsl(200 20% 60%);
            text-align: right;
        }

        /* Чекбокс */
        .form-agreement-split {
            margin: 25px 0;
        }

        .checkbox-wrapper-split {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .form-checkbox-split {
            margin-top: 3px;
            accent-color: hsl(195 100% 50%);
            width: 18px;
            height: 18px;
        }

        .checkbox-label-split {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            color: hsl(200 20% 70%);
            font-size: 0.9rem;
        }

        /* Кнопка отправки */
        .form-submit-split {
            margin-top: 30px;
        }

        .btn-submit-split {
            width: 100%;
            padding: 15px;
            background: hsl(195 100% 50%);
            color: hsl(220 40% 5%);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit-split:hover {
            box-shadow: 0 0 20px hsl(195 100% 50% / 0.4);
        }

        /* Стили для карты */
        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid hsl(200 30% 18%);
            margin-top: 20px;
        }
        
        #map {
            width: 100%;
            height: 100%;
        }
        
        @media (max-width: 768px) {
            .map-container {
                height: 300px;
            }
        }
    </style>

</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="page-contacts">
        <div class="container">



</div>
    <main class="main">
       

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
                                        <p><?php echo nl2br(htmlspecialchars($contact_address));?></p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">📞</div>
                                    <div class="contact-details">
                                        <h4>Телефоны</h4>
                                        <p>
                                            <strong>Отдел продаж:</strong><?php echo nl2br(htmlspecialchars($contact_phone));?> <br>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">✉️</div>
                                    <div class="contact-details">
                                        <h4>Электронная почта</h4>
                                        <p> <?php echo nl2br(htmlspecialchars($contact_email));?><br>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">🕒</div>
                                    <div class="contact-details">
                                        <h4>Часы работы</h4>
                                        <p>
                                          <?php echo nl2br(htmlspecialchars($working_hours));?>
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
                                <a href="tel:+375 33 680 07 07" class="quick-btn phone-btn">
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
                                    <button type="submit" class="btn-submit-split" id="submitBtn">
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
                <div style="text-align: center; margin-top: 15px; color: hsl(200 20% 70%);">
                    <p>📍 214012, г. Смоленск, ул. Вяземская 2-я, д.4, офис Р58</p>
                </div>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=ваш_ключ_яндекс_карт&lang=ru_RU" type="text/javascript"></script>
    
    <script>
        setTimeout(() => {
    const phoneInput = document.getElementById('phoneSplit');
    console.log('=== PHONE INPUT DEBUG ===');
    console.log('Input element:', phoneInput);
    
    if (phoneInput) {
        // Проверяем обработчики событий
        const events = ['input', 'keydown', 'keyup', 'change', 'blur'];
        events.forEach(eventType => {
            const handlers = getEventListeners ? getEventListeners(phoneInput)[eventType] : 'unknown';
            console.log(`${eventType} handlers:`, handlers ? handlers.length : 0);
        });
        
        // Мониторим изменения значения
        let lastValue = phoneInput.value;
        Object.defineProperty(phoneInput, 'value', {
            get() {
                return this._value || '';
            },
            set(newValue) {
                console.log('VALUE SET CALLED:', newValue, 'old:', this._value);
                console.trace('Stack trace:');
                this._value = newValue;
                // Обновляем атрибут
                this.setAttribute('value', newValue);
            }
        });
        
        // Начинаем следить
        phoneInput._value = lastValue;
        
        // Также следим через интервал
        setInterval(() => {
            const currentValue = phoneInput._value || phoneInput.value;
            if (currentValue !== lastValue) {
                console.log('INTERVAL DETECTED CHANGE:', lastValue, '->', currentValue);
                lastValue = currentValue;
            }
        }, 100);
    }
}, 1000);
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
            // Функция валидации телефона
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
                phoneInput.addEventListener('blur', validatePhone);
                
                // При вставке
           
            
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
                            center: [54.782952, 32.026853], // Координаты Минска
                            zoom: 12,
                            controls: ['zoomControl', 'fullscreenControl']
                        });
                        
                        // Точка с адресом
                        const marker = new ymaps.Placemark([54.782952, 32.026853], {
                            balloonContent: '<strong>Dosatron.online</strong><br>г.Смоленск,2-я Вяземская улица,д.4'
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

                    document.getElementById('map').innerHTML = `
                        <div style="width:100%;height:100%;background:#f5f5f5;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:20px;text-align:center;">
                            <div style="font-size:48px;margin-bottom:20px;">📍</div>
                            <h3>7 company</h3>
                            <p>г.Смоленск,2-я Вяземская улица,д.4</p>
                            <p style="margin-top:20px;color:#666;">Координаты: 54.782952, 32.026853</p>
                        </div>
                    `;
                }
            }
            initMap();
        });
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
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn-ru.bitrix24.by/b15313854/crm/site_button/loader_6_ykawzi.js');
    </script>
    </div>
</body>
</html>