<?php
require_once 'includes/forslug.php';
require_once 'includes/reviews_manager.php'; 

$BITRIX_WEBHOOK = 'https://k7s.bitrix24.by/rest/25370/s5ocapktjw31qkaw/crm.lead.add.json';

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

$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

$mysqli = new mysqli($host, $user, $pass, $db_name);

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

$sql = "SELECT id, name, img, d_dosing, performance, slug FROM medicator LIMIT 6";
$result = $mysqli->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['img_found'] = findFile($row['img'] ?? '');
        $products[] = $row;
    }
}

function findFile($dbPath) {
    if (empty($dbPath) || $dbPath == '-' || $dbPath == 'NULL') {
        return null;
    }
    
    $fileName = basename($dbPath);
    
    $searchFolders = [
        '',                      
        'images/',           
        'img/',                
        'products/',            
        'uploads/',             
        'images/products/',    
        'img/products/',       
        'assets/images/',       
        'media/products/',      
    ];

    foreach ($searchFolders as $folder) {
        $fullPath = $folder . $fileName;
        $fullPath = str_replace('\\', '/', $fullPath);
        $fullPath = preg_replace('#/+#', '/', $fullPath);
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return $fullPath;
        }
    }
    
    return null;
}

function getContent($section) {
    require_once 'includes/content_parser.php';
    return getContentSection($section, '');
}

// Получаем отзывы для сайта
$reviewsManager = new ReviewsManager();
$reviews = $reviewsManager->getActiveReviews();

$meta_desc = getContent('meta_description');
$meta_keys = getContent('meta_keywords');
$page_title = getContent('header_title');
$about_text = getContent('about_text');
$favicon = getContent('favicon');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon">
<link rel="shortcut icon" href="<?php echo $meta_desc; ?>" type="image/x-icon">
<title><?php echo $page_title; ?></title>
<meta name="description" content="<?php echo $meta_desc; ?>">
<meta name="keywords" content="<?php echo $meta_keys; ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/cs/style.css">
<link rel="stylesheet" href="/cs/index.css">
<script src="/j/script.js?v=<?php echo filemtime('/j/script.js'); ?>" defer></script> 
<script src="/j/index.js" ></script>
<script src="/j/contacts.js"></script>
<link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main class="main">
        <section class="hero">
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide hero-slide">
                        <div class="container">
                            <div class="hero-slide__content">
                                <h1 class="hero-slide__title">Профессиональные медикаторы</h1>
                                <p class="hero-slide__text">Точное дозирование для сельского хозяйства</p>
                                <a href="/catalog" class="btn btn-primary btn-hero">Смотреть каталог</a>
                            </div>
                        </div>
                        <div class="hero-slide__overlay"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="about">
            <div class="container">
                <h2 class="section-title">О компании</h2>
                <div class="about__content">
                   <?php echo nl2br($about_text); ?>                   
                        <div class="about__image">
                                <img src="uploads/inde.png" alt="7 company" loading="lazy">
                        </div>
                </div>
            </div>
        </section>

        <section class="products">
            <div class="container">
                <h2 class="section-title">Популярные товары</h2>
                
                <div class="products-grid-desktop">
                    <?php if (count($products) > 0): ?>
                        <?php $desktopProducts = array_slice($products, 0, 3); ?>
                        <?php foreach ($desktopProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-card__image">
                                <?php 
                                $imageUrl = getProductImage($product);
                                if ($imageUrl): ?>
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <span class="placeholder-icon">🏭</span>
                                        <p>Нет изображения</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-card__content">
                                <h3 class="product-card__title">
                                    <a href="<?php echo getProductUrl($product); ?>" class="product-link">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-card__desc">
                                    <?php if (!empty($product['d_dosing'])): ?>
                                        <span class="spec-item">📏 <?php echo htmlspecialchars($product['d_dosing']); ?></span><br>
                                    <?php endif; ?>
                                    <?php if (!empty($product['performance'])): ?>
                                        <span class="spec-item">⚡ <?php echo htmlspecialchars($product['performance']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <div class="product-card__price">Подробности по запросу</div>
                                <div class="product-card__actions">
                                    <button class="btn btn-secondary" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                        В сравнение
                                    </button>
                                    <a href="<?php echo getProductUrl($product); ?>" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="text-center" style="margin-top: 30px; text-align: center;">
                    <a href="/catalog" class="btn btn-large">Весь каталог →</a>
                </div>
            </div>
        </section>
        
        <section class="form-section" id="form">
            <div class="form-container">
                <div class="form-card-single">
                    <div class="form-header-single">
                        <h2>Оставьте заявку</h2>
                        <p class="form-subtitle-single">
                            Опишите ваш вопрос или оставьте контакты для связи. 
                            Мы перезвоним вам в течение 30 минут!
                        </p>
                    </div>
                    
                    <?php if ($form_success): ?>
                        <div class="form-notification-single form-success-single">
                            ✅ Спасибо! Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.
                        </div>
                    <?php elseif ($form_error): ?>
                        <div class="form-notification-single form-error-single">
                            ⚠️ <?php echo htmlspecialchars($form_error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form class="contact-form-single" method="POST" action="#form">
                        <div class="form-grid-single">
                            <div class="form-group-single">
                                <label for="nameSingle" class="form-label-single required">
                                    Имя
                                </label>
                                <input type="text" id="nameSingle" name="name" class="form-input-single" 
                                       placeholder="Иван Иванов" 
                                       value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                                       required>
                                <div class="form-hint-single">Пример: Иван Иванов</div>
                            </div>
                            
                            <div class="form-group-single">
                                <label for="emailSingle" class="form-label-single">
                                    Электронная почта
                                </label>
                                <input type="email" id="emailSingle" name="email" class="form-input-single" 
                                       placeholder="example@mail.ru" 
                                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                                <div class="form-hint-single">Пример: example@mail.ru</div>
                            </div>
                            
                            <div class="form-group-single full-width">
                                <label for="phoneSingle" class="form-label-single required">
                                    Телефон
                                </label>
                                <input type="tel" id="phoneSingle" name="phone" class="form-input-single" 
                                       placeholder="+375 (29) 123-45-67" 
                                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                       required>
                                <div class="form-hint-single">Пример: +375 (29) 123-45-67</div>
                            </div>
                            
                            <div class="form-group-single full-width">
                                <label for="messageSingle" class="form-label-single">
                                    Комментарий
                                </label>
                                <textarea id="messageSingle" name="message" class="form-textarea-single" 
                                          placeholder="Опишите ваш вопрос или задачу подробнее..." 
                                          rows="5"><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-agreement-single">
                            <div class="checkbox-wrapper-single">
                                <input type="checkbox" id="agreeSingle" name="agree" class="form-checkbox-single" required>
                                <label for="agreeSingle" class="checkbox-label-single">
                                    Я даю согласие на <a href="/privacy">обработку персональных данных</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-submit-single">
                            <button type="submit" class="btn-submit-single">
                                Отправить заявку →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        
        <section class="reviews">
            <div class="container">
                <h2 class="section-title">Отзывы наших клиентов</h2>
                <div class="reviews__slider">
                    <div class="swiper reviews-swiper">
                        <div class="swiper-wrapper">
                            <?php if (empty($reviews)): ?>
                                <div class="swiper-slide review-slide">
                                    <div class="review-card">
                                        <div class="review-card__body">
                                            <p style="text-align: center; padding: 40px;">Пока нет отзывов. Будьте первым!</p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                <div class="swiper-slide review-slide">
                                    <div class="review-card">
                                        <div class="review-card__header">
                                            <div class="review-card__avatar">
                                                <?php echo htmlspecialchars($review['avatar']); ?>
                                            </div>
                                            <div class="review-card__info">
                                                <h4 class="review-card__name">
                                                    <?php echo htmlspecialchars($review['name']); ?>
                                                </h4>
                                                <div class="review-card__rating">
                                                    <?php echo str_repeat('⭐', $review['rating']); ?>
                                                    <span class="review-card__date">
                                                        <?php echo date('d.m.Y', strtotime($review['date'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="review-card__body">
                                            <p><?php echo nl2br(htmlspecialchars($review['text'])); ?></p>
                                        </div>
                                        <?php if (!empty($review['company'])): ?>
                                        <div class="review-card__footer">
                                            <span class="review-card__farm">
                                                <?php echo htmlspecialchars($review['company']); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script>
        const reviewsSwiper = new Swiper('.reviews-swiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                }
            }
        });
        
        const heroSwiper = new Swiper('.hero-swiper', {
            slidesPerView: 1,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            }
        });
    </script>
</body>
</html>

<?php
$mysqli->close();
?>