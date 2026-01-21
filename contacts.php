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
$favicon=getContent('favicon');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $meta_desc; ?>" type="image/x-icon">
    <title><?php echo $page_title; ?></title> 
    <meta name="description" content="<?php echo $meta_desc; ?>">
    <meta name="keywords" content="<?php echo $meta_keys; ?>">
    <link rel="stylesheet" href="cs/style.css">
    <link rel="stylesheet" href="cs/contacts.css">
    <script src="j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script> 
    <script src="j/contacts.js" ></script>  


</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="page-contacts">
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
                                        <p><?php echo nl2br($contact_address);?></p>
                                    </div>
                                </div>
                                
                                <div class="contact-list-item">
                                    <div class="contact-icon">📞</div>
                                    <div class="contact-details">
                                        <h4>Телефоны</h4>
                                        <p>
                                            <strong>Отдел продаж:</strong><?php echo nl2br($contact_phone);?> <br>
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
                                            Электронная почта

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
                                                Я даю согласие на <a href= "/privacy" class = "nav__link"> обработку персональных данных</a>
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
  <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
    </div>
</body>
</html>